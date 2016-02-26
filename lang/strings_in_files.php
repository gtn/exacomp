<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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

require __DIR__.'/../inc.php';

function get_plugin_name() {
	$pluginType = basename(dirname(dirname(__DIR__)));
	$pluginName = basename(dirname(__DIR__));
	if ($pluginType == 'blocks') {
		$pluginName = 'block_'.$pluginName;
	} else {
		throw new moodle_exception('unknown plugin type '.$pluginType);
	}

	return $pluginName;
}

function getTranslations($language='en') {
	$string = array();
	$stringNotUsed = array();

	if (file_exists($language.'/'.get_plugin_name().'.php')) {
		require ($language.'/'.get_plugin_name().'.php');
	} else {
		require ($language.'/'.get_plugin_name().'.orig.php');
	}

	return $string + $stringNotUsed;
}

$searchPath = __DIR__.'/../';
$Directory = new RecursiveDirectoryIterator($searchPath);
$Iterator = new RecursiveIteratorIterator($Directory);
// $Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$translations = getTranslations('en');
foreach ($translations as $key=>$string) {
	$translations[$key] = (object)[
		'key' => $key,
		'string' => $string,
		'is_used' => false,
	];
}

// $files = iterator_to_array($Iterator);
$all_matches_trans = [];
$all_matches_get_string = [];

foreach ($Iterator as $file) {
	$file = $file->getPathname();
	if (!preg_match('/^.+\.php$/i', $file) || strpos($file, 'tests')) continue;

	$file_id = basename(realpath($searchPath)).'/'.str_replace('\\', '/', substr($file, strlen($searchPath)));

	$lines = explode("\n", file_get_contents($file));
	foreach ($lines as $i=>$line) {
		if (preg_match('!\\\\trans\s*\(\s*(?<params>["\'].*["\'\]])\s*\)!U', $line, $matches)) {
			$matches = (object)$matches;
			$matches->params = eval('return array('.$matches->params.');');
			$all_matches_trans[$file_id.':'.($i+1)] = $matches;
		}

		// $brackets = '(\[[^\]+]\]|\([^\]+\))';
		if (preg_match('!(?<all>get_string\s*\(\s*(?<params>["\'][^"\']*["\'](\s*,\s*["\'][^"\']*["\'])?))!', $line, $matches)) {
			$matches = (object)$matches;
			$params = eval('return array('.$matches->params.');');

			if (!empty($params[1]) && $params[1] != get_plugin_name()) continue;
			$all_matches_get_string[] = $match = (object)[
				'file' => $file_id,
				'line' => $i+1,
				'params' => $params,
				'matches' => $matches,
			];

			if (!empty($translations[$match->params[0]])) {
				$match->translation_found = true;
				$translations[$match->params[0]]->is_used = true;
			} else {
				$match->translation_found = false;
			}
		}
	}
}


echo '<table>';
foreach ($all_matches_trans as $file => $match) {
	$has_identifier = block_exacomp\common\_t_check_identifier($match->params[0]);

	echo "<tr><td>$file</td><td>".join('</td><td>', $match->params)."</td>";

}
echo '</table>';

echo '<hr />';
echo '<table>';
foreach ($all_matches_get_string as $match) {
	// var_dump($match->matches);
	echo "<tr><td>{$match->file}:{$match->line}</td><td>";
	echo $match->translation_found ? 'found' : 'not found';
	echo "</td><td>";
	echo $match->matches->all;// join('</td><td>', [])."</td>";

}
echo '</table>';

echo '<hr />';
echo '<table>';
foreach ($translations as $trans) {
	echo "<tr><td>{$trans->key}</td><td>";
	echo $trans->is_used ? 'used' : 'not used';
	// echo "</td><td>";
	// echo $match->matches->all;// join('</td><td>', [])."</td>";

}
echo '</table>';

// var_dump($all_matches_trans);
exit;

echo 'Translations TODO: '.join(', ', $translations);
