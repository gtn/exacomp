<?php

defined('MOODLE_INTERNAL') || die();

call_user_func(function() {
    $servicesFile = __DIR__ . '/../db/services.php';

    $externallibs = [
        ['file' => 'externallib.php', 'className' => 'block_exacomp_external'],
        ['file' => 'externallib.setapp.php', 'className' => 'block_exacomp_external_setapp'],
    ];

    if (file_exists($servicesFile)) {
        if (!is_writable($servicesFile)) {
            // no change possible
            return;
        }

        $lastChangeTime = max(array_map(function($file) {
            return filemtime(__DIR__ . '/../' . $file['file']);
        }, $externallibs));

        if (filemtime($servicesFile) == $lastChangeTime) {
            // no change required
            return;
        }
    }

    $services = array(
        'exacompservices' => array(
            'requiredcapability' => '',
            'restrictedusers' => 0,
            'enabled' => 1,
            'shortname' => 'exacompservices',
            'functions' => [],
            'downloadfiles' => 1,
            'uploadfiles' => 1,
        ),
    );

    $functions = [];

    $doku = '';

    extract($GLOBALS);

    foreach ($externallibs as $externallib) {
        require_once __DIR__ . '/../' . $externallib['file'];

        $rc = new ReflectionClass($externallib['className']);
        $methods = $rc->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!preg_match('!@ws-type-(read|write)!', $method->getDocComment(), $matches)) {
                continue;
            }
            $description = preg_replace('!^[/\t \\*]+!m', '', $method->getDocComment());
            $description = trim(preg_replace('!@.*!sm', '', $description));

            $func = $method->getName();
            if (strpos($func, 'dakora_') === false && strpos($func, 'diggrplus_') === false && strpos($func, 'dakoraplus_') === false) {
                $func = 'block_exacomp_' . $func;
            }

            $services['exacompservices']['functions'][] = $func;

            $functions[$func] = [                             // web service function name
                'classname' => $externallib['className'],         // class containing the external function
                'methodname' => $method->getName(), // external function name, strip block_exacomp_ for function name
                'classpath' => 'blocks/exacomp/' . $externallib['file'], // file containing the class/external function
                'description' => $description,                   // human readable description of the web service function
                'type' => $matches[1],                   // database rights of the web service function (read, write)
            ];
        }
    }

    // save to services.php
    $content = "<?php\n\n";
    $content .= '$functions = ' . var_export($functions, true) . ";\n\n";
    $content .= '$services = ' . var_export($services, true) . ";\n\n";
    file_put_contents($servicesFile, $content);
    @touch($servicesFile, $lastChangeTime);
});

function block_exacomp_print_webservice_doku() {
    global $CFG;

    $servicesFile = __DIR__ . '/../db/services.php';

    require $servicesFile;

    $doku = '';

    // $functions is defined in services.php
    $lastClassname = '';
    foreach ($functions as $functionName => $function) {
        require_once $CFG->dirroot . '/' . $function['classpath'];

        if ($lastClassname != $function['classname']) {
            $doku .= '<hr/><h1>Class: ' . $function['classname'] . "</h1><hr/>\n";
            $lastClassname = $function['classname'];
        }

        $method = new ReflectionMethod($function['classname'], $function['methodname']);

        // doku
        $doku .= "<h2>{$functionName}</h2>\n";
        $doku .= "<div>{$function['description']}</div>\n";
        $doku .= "<div>type: {$function['type']}</div>\n";

        $recursor = function($o) use (&$recursor) {
            if ($o instanceof external_multiple_structure) {
                $ret = [];
                $ret[] = $recursor($o->content);
                if ($o->desc) {
                    $ret[] = '... ' . $o->desc . ' ...';
                } else {
                    $ret[] = '...';
                }

                return $ret;
            } elseif ($o instanceof external_single_structure) {
                $data = [];
                foreach ($o->keys as $paramName => $paramInfo) {
                    if ($paramInfo instanceof external_value) {
                        $data[$paramName] = $paramInfo->type .
                            ' ' . ($paramInfo->allownull ? 'null' : 'not null') .
                            ($paramInfo->desc ? ' (\'' . $paramInfo->desc . '\')' : '');
                    } elseif ($paramInfo instanceof external_multiple_structure || $paramInfo instanceof external_single_structure) {
                        $data[$paramName] = $recursor($paramInfo);
                    } else {
                        die('error #fsjkjlerw234');
                    }
                }

                return $data;
            } elseif ($o instanceof external_value) {
                $paramInfo = $o;

                return $paramInfo->type .
                    ' ' . ($paramInfo->allownull ? 'null' : 'not null') .
                    ' (' . $paramInfo->desc . ')';
            } else {
                die('wrong value of type: ' . var_export($o, true));
                $doku .= get_class($o);
            }
        };

        $paramMethod = new ReflectionMethod($function['classname'], $function['methodname'] . '_parameters');
        /* @var external_function_parameters $params */
        $params = $paramMethod->invoke(null)->keys;
        $doku .= "Params: <table>\n";
        foreach ($params as $paramName => $paramInfo) {
            $doku .= "<tr>\n";
            $doku .= '<td>' . $paramName . "</td>\n";
            $doku .= '<td>' . ($paramInfo->type ?? '') . "</td>\n";
            $doku .= '<td>' . (!isset($paramInfo->allownull) ? '' : ($paramInfo->allownull ? 'null' : 'not null')) . "</td>\n";
            $doku .= '<td>';
            if ($paramInfo->required == VALUE_REQUIRED) {
                $doku .= 'required';
            } elseif ($paramInfo->required == VALUE_OPTIONAL || $paramInfo->required == VALUE_DEFAULT) {
                $doku .= 'optional';
            } else {
                $doku .= 'unknown';
            }
            $doku .= "</td>\n";
            if ($paramInfo->required == VALUE_DEFAULT) {
                ob_start();
                var_dump($paramInfo->default);
                $default = ob_get_clean();
                $doku .= '<td>default: ' . $default . "</td>\n";
            } else {
                $doku .= '<td>' . "</td>\n";
            }
            $doku .= '<td>' . $paramInfo->desc . "</td>\n";

            if (!($paramInfo instanceof external_value)) {
                $data = $recursor($paramInfo);
                $doku .= "<td><pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre></td>\n";
            } else {
                $doku .= "<td></td>\n";
            }
        }
        $doku .= "</table>\n";

        $returnMethod = new ReflectionMethod($function['classname'], $function['methodname'] . '_returns');
        /* @var external_description $returns */
        $returns = $returnMethod->invoke(null);

        $data = $recursor($returns);

        $doku .= "Returns:<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>\n";
    }

    // save doku
    $doku = '<style>
		table {
			border-collapse: collapse;
		}
		td {
			border: 1px solid black;
			padding: 2px 5px;
		}
	</style>' . $doku;

    echo $doku;
}
