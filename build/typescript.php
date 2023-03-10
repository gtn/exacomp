<?php

require __DIR__ . '/../inc.php';

global $CFG;

$servicesFiles = [
    __DIR__ . '/../db/services.php',
    $CFG->dirroot . '/mod/quiz/db/services.php',
    $CFG->dirroot . '/lib/db/services.php',
    $CFG->dirroot . '/message/output/popup/db/services.php',
];

function moodle_type_to_typescript_type($isParameters, $type) {
    if ($type == 'int' || $type == 'float') {
        $tsType = $isParameters ? 'param_number' : 'number';
    } elseif ($type == 'bool') {
        $tsType = $isParameters ? 'param_boolean' : 'boolean';
    } else {
        $tsType = $isParameters ? 'param_string' : 'string';
    }

    return $tsType;
}

$doku = '';
$dokuInterfaces = '';

$definedEnumsByDefenition = [];

foreach ($servicesFiles as $servicesFile) {
    require $servicesFile;

    // $functions is defined in services.php

    /* @var array $functions */

    foreach ($functions as $functionName => $function) {
        // echo $function['classpath']; exit;
        if (!empty($function['classpath'])) {
            require_once $CFG->dirroot . '/' . $function['classpath'];
        }

        $method = new ReflectionMethod($function['classname'], $function['methodname']);

        $recursor = function ($isParameters, $namePrefix, $o) use (&$recursor, &$definedEnumsByDefenition) {
            if ($o instanceof external_multiple_structure) {
                $dokuInterface = $recursor($isParameters, $namePrefix . '_item', $o->content);
                $dokuInterface .= "export type {$namePrefix} = {$namePrefix}_item[]\n\n";

                return $dokuInterface;
            } elseif ($o instanceof external_single_structure) {
                $dokuInterface = "export interface {$namePrefix} {\n";

                foreach ($o->keys as $paramName => $paramInfo) {
                    if ($paramInfo instanceof external_value) {
                        if (preg_match('!ENUM\(([^)]+)\)!', $paramInfo->desc, $matches)) {
                            $tsType = moodle_type_to_typescript_type($isParameters, $paramInfo->type);
                            if ($tsType != 'string') {
                                if ($isParameters) {
                                    die('enum IN PARAMETERS not allowed in: ' . $namePrefix);
                                } else {
                                    die('enum not allowed in: ' . $namePrefix);
                                }
                            }

                            $parts = explode(',', $matches[1]);

                            $enumFields = join("", array_map(function ($part) {
                                return "  " . ucfirst(trim($part)) . " = '" . trim($part) . "',\n";
                            }, $parts));
                            $enumName = 'enum_' . join("_", array_map(function ($part) {
                                    return trim($part);
                                }, $parts));

                            // enums with same defenition have the same type
                            $tsType = $namePrefix . '_' . $paramName;
                            $dokuInterface = "export { $enumName as $tsType }\n\n" .
                                $dokuInterface;

                            if (!$definedEnumsByDefenition[$enumFields]) {
                                $dokuInterface = "export enum {$enumName} {\n" . $enumFields . "}\n\n" .
                                    $dokuInterface;

                                // save enum defenition for later
                                $definedEnumsByDefenition[$enumFields] = $tsType;
                            }

                            $tsType = $enumName;
                        } else {
                            $tsType = moodle_type_to_typescript_type($isParameters, $paramInfo->type);
                        }
                    } elseif ($paramInfo instanceof external_single_structure) {
                        $dokuInterface = $recursor($isParameters, $namePrefix . '_' . $paramName, $paramInfo) . $dokuInterface;
                        $tsType = $namePrefix . '_' . $paramName;
                    } elseif ($paramInfo instanceof external_multiple_structure) {
                        $dokuInterface = $recursor($isParameters, $namePrefix . '_' . $paramName, $paramInfo->content) . $dokuInterface;
                        $tsType = $namePrefix . '_' . $paramName . '[]';
                    } else {
                        die('error #fsjkjlerw234');
                    }

                    if ($paramInfo->required == VALUE_DEFAULT) {
                        $tsType .= ' | null';
                    }

                    if ($paramInfo->required == VALUE_DEFAULT) {
                        // hack default param is time()
                        if (strpos($paramName, 'time') !== false && @$paramInfo->type == PARAM_INT && abs(time() - $paramInfo->default) < 10) {
                            // default is the current time() value
                            $default = 'time()';
                        } else {
                            ob_start();
                            var_dump($paramInfo->default);
                            $default = preg_replace("![\r\n\s]+!", ' ', trim(ob_get_clean()));
                        }
                    } else {
                        $default = '';
                    }

                    $dokuInterface .= "  {$paramName}" . ($isParameters
                            ? ($paramInfo->required == VALUE_REQUIRED ? '' : '?')
                            : ($paramInfo->required == VALUE_OPTIONAL ? '?' : '')
                        ) . ": {$tsType};" .
                        ($default ? " // default: $default" : "") .
                        ($paramInfo->desc ? " // " . preg_replace("![\r\n\s]+!", ' ', $paramInfo->desc) : "") .
                        "\n";
                }

                $dokuInterface .= "}\n\n";

                return $dokuInterface;
            } elseif ($o instanceof external_value) {
                $tsType = moodle_type_to_typescript_type($isParameters, $o->type);

                return "type {$namePrefix} = {$tsType};\n\n";
            } elseif ($o === null) {
                return "type {$namePrefix} = null;\n\n";
            } else {
                echo('wrong value of type: ' . $namePrefix . ' ' . var_export($o, true));
                // $doku .= get_class($o);
            }
        };

        $paramMethod = new ReflectionMethod($function['classname'], $function['methodname'] . '_parameters');
        /* @var external_function_parameters $params */
        $params = $paramMethod->invoke(null);
        $dokuInterfaces .= $recursor(true, "{$functionName}_parameters", $params);

        $returnMethod = new ReflectionMethod($function['classname'], $function['methodname'] . '_returns');
        /* @var external_description $returns */
        $returns = $returnMethod->invoke(null);

        if ($returns instanceof external_multiple_structure) {
            $returns_type = "{$functionName}_returns_item[]";
            $dokuInterfaces .= $recursor(false, "{$functionName}_returns_item", $returns->content);
        } else {
            $returns_type = "{$functionName}_returns";
            $dokuInterfaces .= $recursor(false, "{$functionName}_returns", $returns);
        }

        $doku .= "\n  /**\n   * " . preg_replace("![\r\n\s]+!", ' ', $function['description']) . "\n   */\n" .
            "  public {$functionName} = async (params" .
            // optional, weil keine parameter notwendig
            (count($params->keys) == 0 ? '?' : '') .
            ": {$functionName}_parameters): Promise<{$returns_type}> =>\n" .
            "    this.callWebservice('{$functionName}', params);\n";
    }
}

$doku = "export type param_string = string | number | boolean | null;\n" .
    "export type param_boolean = string | number | boolean | null;\n" .
    "export type param_number = string | number | null;\n" .
    "\n" .
    $dokuInterfaces .
    "\n\n" .
    "export default abstract class MoodleWebserviceDefinitions {\n" .
    "  abstract callWebservice<T = any>(wsfunction: string, payload: any): Promise<T>;\n" .
    $doku .
    "}";

echo '<pre>' . htmlspecialchars($doku);
