<?php
/*
 * copyright exabis
 */

require __DIR__.'/../inc.php';

$searchPath = dirname(__DIR__);
$Directory = new RecursiveDirectoryIterator($searchPath);
$Iterator = new RecursiveIteratorIterator($Directory);

foreach ($Iterator as $file) {
	$file = $file->getPathname();
	if (!preg_match('/^.+\.(php|js)$/i', $file, $matches)) continue;

	$filetype = strtolower($matches[1]);

	echo $filetype.': '.$file."<br />\n";

	$content = file_get_contents($file);

	$content = preg_replace('!^<\?php\s+!', '', $content);
	$content = preg_replace('!\?>\s*$!', '', $content);

	while (true) {
		$content = trim($content);

		if (true) {
			if (preg_match('!^/\*.*exabis.*\*/!sU', $content, $matches)) {
				echo $matches[0]."\n";
				$content = preg_replace('!^/\*.*\*/!sU', '', $content);
				continue;
			}
		} else {
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

	// copyright
	$copyright =
"/*
 * copyright exabis
 */";

	$content = "$copyright\n\n".$content."\n";

	if ($filetype == 'php') {
		$content = "<?php\n".$content;
	}

	file_put_contents($file, $content);
	// echo $content;
	// exit;
	// $i++;
	// if ($i> 4) exit;
}
