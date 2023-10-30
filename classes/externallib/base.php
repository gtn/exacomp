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

namespace block_exacomp\externallib;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../../inc.php';
require_once $CFG->libdir . '/externallib.php';

class base extends \external_api {
    public static function custom_htmltrim($string) {
        //$string = strip_tags($string);
        $string = nl2br($string);
        $remove = array("\n", "\r\n", "\r", "<p>", "</p>", "<h1>", "</h1>", "<br>", "<br />", "<br/>", "<sup>", "</sup>");
        $string = str_replace($remove, ' ', $string); // new lines to space
        $string = preg_replace('!\s+!', ' ', $string); // multiple spaces to single
        $string = fix_utf8($string);
        // here is possible &nbsp;, but also are possible umlauts...
        $string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $string = trim($string, chr(0xC2) . chr(0xA0));
        return $string;
    }
}
