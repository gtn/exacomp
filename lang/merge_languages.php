<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

function getTranslations($language) {
	$string = array();
	$stringNotUsed = array();

	$file = current(glob($language.'/*.php'));

	if ($language == 'de') {
		$content = file_get_contents($file);

		// get copyright
		if (!preg_match('!(//.*\r?\n)+!', $content, $matches)) {
			throw new moodle_exception('copyright not found');
		}

		$copyright = $matches[0];
		$content = str_replace($copyright, '', $content);

		$content = preg_replace_callback('!^//\s*(.*)!m', function($m) {
			return '$string[\'=== '.trim($m[1], ' =').' ===\'] = null;';
		}, $content);
		echo $content;
		eval('?>'.$content);
	} else {
		require $file;
	}

	return $string; // + $stringNotUsed;
}


//$langPaths = glob('*');
//$langPaths = array_filter($langPaths, 'is_dir');
$langPaths = [];

$langPaths = array_combine($langPaths, $langPaths);
unset($langPaths['de']);
unset($langPaths['en']);
$langPaths = array('de' => 'de', 'en' => 'en') + $langPaths;

$totalLanguages = [];

foreach ($langPaths as $langPath) {
	$strings = getTranslations($langPath);

	foreach ($strings as $key => $value) {
		if (!isset($totalLanguages[$key])) {
			$totalLanguages[$key] = [
				null, null
			];
		}

		if (preg_match('!^===!', $key)) {
			$totalLanguages[$key] = $value;
		} else {
			$totalLanguages[$key][$langPath === 'de' ? 0 : ($langPath === 'en' ? 1 : $langPath)] = $value;
		}
	}
}

$output = var_export($totalLanguages, true);
$output = str_replace('),', '],', $output);
$output = preg_replace('!\)\s*$!', '];', $output);
$output = preg_replace('!\s*array\s*\(!', ' [', $output);
$output = preg_replace('!^([\t]*)  !m', '$1	', $output);
$output = preg_replace('!^([\t]*)  !m', '$1	', $output);
$output = preg_replace('!^([\t]*)  !m', '$1	', $output);
$output = preg_replace('!^\s*\'===!m', "\n\n\n".'$0', $output);
$output = str_replace('0 => ', '', $output);
$output = str_replace('1 => ', '', $output);
echo $output;

file_put_contents('total.php', "<?php\n\nreturn ".$output);

exit;
