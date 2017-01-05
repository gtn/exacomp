<?php

namespace Super;

class Utilities {
	static function groupArray(array $arr, $key) {
		$result = [];

		foreach ($arr as $itemKey => $item) {
			$tmp = (array)$item;
			$groupKey = @$tmp[$key];

			if (!isset($result[$groupKey])) {
				$result[$groupKey] = [];
			}

			$result[$groupKey][$itemKey] = $item;
		}

		return $result;
	}

	static function findArray(array $arr, $keyOrFunction, $searchedValue = null) {
		if (is_string($keyOrFunction)) {
			$function = function($e) use ($keyOrFunction, $searchedValue) {
				if (is_object($e)) {
					return $e->{$keyOrFunction} == $searchedValue;
				} elseif (is_array($e)) {
					return $e[$keyOrFunction] == $searchedValue;
				} else {
					throw new \RuntimeException('no allowed array item found #5290290fds3');
				}
			};
		} elseif (is_callable($keyOrFunction)) {
			$function = $keyOrFunction;
		} else {
			throw new \RuntimeException('no allowed parameter found #5290290fds3');
		}

		return reset(array_filter($arr, $function)) ?: null;
	}

	static function stringToCsv($string, $delimiter, $has_header) {
		$string = trim($string, "\r\n");
		$string = rtrim($string);
		$csv = preg_split("!\r?\n!", $string);

		foreach ($csv as &$item) {
			$item = str_getcsv($item, $delimiter);
		}
		unset($item);

		if ($has_header) {
			$header = array_shift($csv);

			foreach ($csv as &$item) {
				$newItem = [];
				foreach ($item as $i => $part) {
					$newItem[$header[$i]] = $part;
				}
				$item = $newItem;
			}
			unset($item);
		}

		return $csv;
	}

	static function hiddenVarDump($var) {
		echo "<!--\n";
		var_dump($var);
		echo "-->";
	}
}
