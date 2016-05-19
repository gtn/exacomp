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

$searchPath = dirname(__DIR__);
$Directory = new RecursiveDirectoryIterator($searchPath);
$Iterator = new RecursiveIteratorIterator($Directory);

$overwriteAll = true;

foreach ($Iterator as $file) {
	$file = $file->getPathname();
	if (!preg_match('/^.+\.(php|js)$/i', $file, $matches)) continue;

	$filetype = strtolower($matches[1]);

	echo $filetype.': '.$file."<br />\n";

	$content = file_get_contents($file);

	$content = preg_replace('!^<\?php\s+!', '', $content);
	$content = preg_replace('!\?>\s*$!', '', $content);

	$copyrightFound = false;

	while (true) {
		$content = trim($content);

		if (false) {
			if (preg_match('!^/\*.*exabis.*\*/!isU', $content, $matches)) {
				$copyrightFound = true;
				echo $matches[0]."\n";
				$content = preg_replace('!^/\*.*\*/!sU', '', $content);
				continue;
			}
			if (preg_match('!^(//.*\n)*//.*exabis.*\n(//.*\n)*!i', $content, $matches)) {
				$copyrightFound = true;
				echo $matches[0]."\n";
				$content = preg_replace('!^(//.*\n)*//.*exabis.*\n(//.*\n)*!i', '', $content);
				continue;
			}
		} else {
			$copyrightFound = true;
			if (preg_match('!^/\*.*\*/!sU', $content, $matches)) {
				echo $matches[0]."\n";
				$content = preg_replace('!^/\*.*\*/!sU', '', $content);
				continue;
			}
			if (preg_match('!^//.*\n!', $content, $matches)) {
				echo $matches[0];
				$content = preg_replace('!^//.*\n!', '', $content);
				continue;
			}
		}
		break;
	}

	if (!$copyrightFound) continue;

	// copyright
	$copyright =
trim("
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
");

	$content = "$copyright\n\n".$content."\n";

	if ($filetype == 'php') {
		$content = "<?php\n".$content;
	}

	file_put_contents($file, $content);
	/*
	echo "-----------------------------------\n";
	echo $content;
	exit;
	// $i++;
	// if ($i> 4) exit;
	/* */
}
