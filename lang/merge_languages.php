<?php

function getTranslations($language) {
	$string = array();
	$stringNotUsed = array();

	$file = current(glob($language.'/*.php'));

	if ($language == 'en') {
		$content = file_get_contents($file);

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
$langPaths = array('en' => 'en', 'de' => 'de') + $langPaths;

$totalLanguages = [];

foreach ($langPaths as $langPath) {
	$strings = getTranslations($langPath);

	foreach ($strings as $key => $value) {
		if (!isset($totalLanguages[$key])) {
			$totalLanguages[$key] = [
				'de' => '',
				'en' => '',
			];
		}

		if (preg_match('!^===!', $key)) {
			$totalLanguages[$key] = $value;
		} else {
			$totalLanguages[$key][$langPath] = $value;
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
echo $output;

file_put_contents('total.php', "<?php\n\nreturn ".$output);

exit;
