<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_exacomp\common {

    use block_exacomp\db;
    use block_exacomp\developer;
    use block_exacomp\moodle_exception;

    defined('MOODLE_INTERNAL') || die();

    /**
     * @deprecated use global $DB; for update_record() with a where condition and insert_or_update_record() use \block_exacomp\db
     */
    abstract class exadb extends \moodle_database {
        /**
         * @param string $table
         * @param array|object $data
         * @param array|null $where
         * @return null|bool|object
         */
        public function update_record($table, $data, $where = null) {
        }

        public function insert_or_update_record($table, $data, $where = null) {
        }
    }

    /**
     * Class exadb_forwarder
     * exadb_extender extends this call,
     * which allows exadb_extender to call parent::function(), which gets forwarded to $DB->function()
     *
     * @deprecated use global $DB;
     */
    class exadb_forwarder {
        function __call($func, $args) {
            global $DB;

            return call_user_func_array([$DB, $func], $args);
        }
    }

    /**
     * @deprecated use global $DB; for update_record() with a where condition and insert_or_update_record() use \block_exacomp\db
     */
    class exadb_extender extends exadb_forwarder {

        /**
         * @param string $table
         * @param array|object $data
         * @param array|null $where
         * @return null|bool|object
         */
        public function update_record($table, $data, $where = null) {
            return db::update_record($table, $data, $where);
        }

        /**
         * @param $table
         * @param $data
         * @param null $where
         * @return object
         * @throws moodle_exception
         */
        public function insert_or_update_record($table, $data, $where = null) {
            return db::insert_or_update_record($table, $data, $where);
        }
    }

    /**
     * @property string $wwwroot moodle url
     * @property string $dirroot moodle path
     * @property string $libdir lib path
     *
     * @deprecated use global $CFG;
     */
    class _globals_dummy_CFG {
    }

    /**
     * @deprecated use the Moodle globals in the function instead, eg. global $DB, $USER;
     */
    class globals {
        /**
         * @var exadb
         * @deprecated use global $DB; for update_record() with a where condition and insert_or_update_record() use \block_exacomp\db
         */
        public static $DB;

        /**
         * @var \moodle_page
         * @deprecated use global $PAGE;
         */
        public static $PAGE;

        /**
         * @var \core_renderer
         * @deprecated use global $OUTPUT;
         */
        public static $OUTPUT;

        /**
         * @var \stdClass
         * @deprecated use global $COURSE;
         */
        public static $COURSE;

        /**
         * @var \stdClass
         * @deprecated use global $USER;
         */
        public static $USER;

        /**
         * @var \stdClass
         * @deprecated use global $SITE;
         */
        public static $SITE;

        /**
         * @var _globals_dummy_CFG
         * @deprecated use global $CFG;
         */
        public static $CFG;

        public static function init() {
            global $PAGE, $OUTPUT, $COURSE, $USER, $CFG, $SITE;
            globals::$DB = new exadb_extender();
            globals::$PAGE =& $PAGE;
            globals::$OUTPUT =& $OUTPUT;
            globals::$COURSE =& $COURSE;
            globals::$USER =& $USER;
            globals::$CFG =& $CFG;
            globals::$SITE =& $SITE;
        }
    }

    globals::init();

    /**
     * @deprecated do not use
     */
    function _plugin_name() {
        return preg_replace('!\\\\.*$!', '', __NAMESPACE__); // the \\\\ syntax matches a \ (backslash)!
    }

    call_user_func(function() {
        if (!globals::$CFG->debugdeveloper) {
            return;
        }

        $lang = current_language();
        $langDir = dirname(__DIR__) . '/lang';
        $totalFile = $langDir . '/total.php';
        $langFile = $langDir . '/' . $lang . '/' . _plugin_name() . '.php';

        if (file_exists($totalFile) && file_exists($langFile) && ($time = filemtime($totalFile)) != filemtime($langFile) && is_writable($langFile)) {
            // regenerate

            // test require, check if file has a parse error etc.
            require $totalFile;

            // get copyright
            $content = file_get_contents($totalFile);
            if (!preg_match('!(//.*\r?\n)+!', $content, $matches)) {
                throw new moodle_exception('copyright not found');
            }

            $copyright = $matches[0];
            $content = str_replace($copyright, '', $content);

            $content = preg_replace_callback('!^(?<comment>\s*//\s*.*)!m', function($matches) {
                return var_export(preg_replace('!^[ \t]+!m', '', $matches['comment']), true) . ',';
            }, $content);

            $totalLanguages = eval('?>' . $content);

            $byLang = [];

            foreach ($totalLanguages as $key => $langs) {
                if (is_int($key)) {
                    $byLang['de'][] = $langs;
                    $byLang['en'][] = $langs;
                    continue;
                }
                if (!$langs) {
                    $byLang['de'][$key] = null;
                    $byLang['en'][$key] = null;
                    continue;
                }
                foreach ($langs as $lang => $value) {
                    if ($lang === 0) {
                        $lang = 'de';
                    } else if ($lang === 1) {
                        $lang = 'en';
                    }
                    if ($value === null && preg_match('!^' . $lang . ':(.*)$!', $key, $matches)) {
                        $byLang[$lang][$key] = $matches[1];
                    } else {
                        $byLang[$lang][$key] = $value;
                    }
                }
            }

            foreach ($byLang as $lang => $strings) {
                $output = '<?php' . "\n{$copyright}\n";

                foreach ($strings as $key => $value) {
                    if (is_int($key)) {
                        $output .= $value . "\n";
                    } else if (strpos($key, '===') === 0) {
                        // group
                        $output .= "\n\n// " . trim($key, ' =') . "\n";
                    } else if ($value === null) {
                    } else {
                        $output .= '$string[' . var_export($key, true) . '] = ' . var_export($value, true) . ";\n";
                    }
                }

                file_put_contents($langDir . '/' . $lang . '/' . _plugin_name() . '.php', $output);
                @touch($langDir . '/' . $lang . '/' . _plugin_name() . '.php', $time);
            }
        }

        // include other developer scripts
        developer::developer_actions();
    });
}

namespace block_exacomp {
    /**
     * @deprecated use the Moodle globals in the function instead, eg. global $DB, $USER;
     */
    class globals extends common\globals {
    }
}
