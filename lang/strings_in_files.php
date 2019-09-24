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

$searchPath = dirname(__DIR__);
$Directory = new RecursiveDirectoryIterator($searchPath);
$Iterator = new RecursiveIteratorIterator($Directory);
// $Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$translations = require __DIR__.'/total.php';
foreach ($translations as $key => $strings) {
	if (!$strings) {
		unset($translations[$key]);
	} else {
		$translations[$key] = (object)[
			'key' => $key,
			'strings' => $strings,
			'is_used' => false,
		];
	}
}

// $files = iterator_to_array($Iterator);
$all_matches_trans = [];
$all_matches_get_string = [];

foreach ($Iterator as $file) {
	$file = str_replace('\\', '/', $file->getPathname());
	if (!preg_match('/^.+\.php$/i', $file) || strpos($file, '/tests/') || strpos($file, '/lang/')) continue;

	$file_id = basename(realpath($searchPath)).'/'.str_replace('\\', '/', substr($file, strlen($searchPath)));

	$content = file_get_contents($file);
	// echo $file.'<br />';
	$tokens = token_get_all($content);

	foreach ($tokens as $token) {
		if (is_array($token)) {
			$token = (object)[
				'type' => $token[0],
				'content' => $token[1],
				'line' => $token[2],
			];
			if ($token->type == T_CONSTANT_ENCAPSED_STRING) {
				$token->string = substr($token->content, 1, -1);

				if (isset($translations[$token->string])) {
					$translations[$token->string]->token_found = true;
				}
			}
		} else {
			// var_dump($token);
		}
	}

	continue;
	preg_match_all('"[^"]+"|\'[^\']+\'', $content, $matches);
	var_dump($matches);
	exit;

	$lines = explode("\n", $content);
	/*
	foreach ($lines as $i=>$line) {
		if (preg_match('!\\\\trans\s*\(\s*(?<params>["\'].*["\'\]])\s*\)!U', $line, $matches)) {
			$matches = (object)$matches;
			$matches->params = eval('return array('.$matches->params.');');
			$all_matches_trans[$file_id.':'.($i+1)] = $matches;
		}

		// $brackets = '(\[[^\]+]\]|\([^\]+\))';
		if (preg_match('!(?<all>block_exacomp_get_string\s*\(\s*(?<params>["\'][^"\']*["\'](\s*,\s*["\'][^"\']*["\'])?))!', $line, $matches)) {
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
	*/
}

echo 'result:';
echo '<table>';
foreach ($translations as $trans) {
	if (@$trans->token_found) {
		echo "<tr><td>{$trans->key}</td><td>";
		continue;
	}
	echo "<tr><td style='color: red;'>{$trans->key}</td><td>";
	echo join('<br />', $trans->strings);
	// echo @$trans->token_found ? 'used' : 'not used';
	// echo "</td><td>";
	// echo $match->matches->all;// join('</td><td>', [])."</td>";

}
echo '</table>';

exit;


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
