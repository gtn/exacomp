<?php

namespace Super;

class Fs {
	static function joinPath($path, $file) {
		return rtrim(static::toUnixPath($path), '/').($file !== null ? '/'.ltrim($file, '/') : '');
	}

	public static function toUnixPath($path) {
		return str_replace('\\', '/', $path);
	}

	static function pathToUrl($path) {
		$path = static::toUnixPath($path);
		$doc_root = static::toUnixPath($_SERVER['DOCUMENT_ROOT']);
		$pos = strpos($path, $doc_root);
		if ($pos === false) {
			throw new \Exception("path not found $path");
		}

		return substr($path, strlen($doc_root) + $pos);
	}

	static function toFilesystemFilename($fileName) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return utf8_decode($fileName);
		}

		return $fileName;
	}

	static function fromFilesystemFilename($fileName) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return utf8_encode($fileName);
		}

		return $fileName;
	}

	static function deleteOlderThan($path, $lifetime) {
		$files = glob(static::joinPath($path, '*'));
		$olderThan = time() - $lifetime;

		foreach ($files as $file) {
			if (is_file($file) && (filemtime($file) < $olderThan)) {
				@unlink($file);
			}
		}
	}

	static function mkdirs($path) {
		if (is_dir($path)) {
			return true;
		}

		return mkdir($path, 0777, true);
	}

	static function getUploadedFile($key) {
		$access = function($value) use ($key) {
			$key = preg_split('![/\.]!', str_replace(']', '', str_replace('[', '/', trim($key, '][/. '))));
			$key = array_merge(array_slice($key, 0, 1), [$value], array_slice($key, 1));

			$now = $_FILES;
			while ($key && isset($now[$key[0]])) {
				$now = $now[array_shift($key)];
			}

			return $now;
		};

		$tmp_name = $access('tmp_name');
		if (is_string($tmp_name) && !empty($tmp_name)) {
			// when not submitting a new file, php gets an empty string

			$file = new UploadedFile;
			$file->filename = $access('name');
			$file->type = $access('type');
			$file->file = $tmp_name;
			$file->error = $access('error');
			$file->size = $access('size');
			$file->filename = $access('name');

			return $file;
		} else {
			return null;
		}
	}

	static function getLock($file, $wait = 30) {
		return new Lock($file, $wait);
	}
}

class UploadedFile {
	var $filename;
	var $type;
	var $file;
	var $error;
	var $size;
}

class Lock {
	private $fp;
	private $file;
	private $wait;

	function __construct($file, $wait = 30) {
		$this->file = $file;
		$this->wait = $wait;
	}

	function lock() {
		$this->fp = fopen($this->file, 'w');

		$start = microtime(true);
		while (!flock($this->fp, LOCK_EX | LOCK_NB)) {
			if (microtime(true) > $start + $this->wait) {
				throw new \Exception("couldn't get lock within {$this->wait} seconds");
			}

			usleep(rand(5 * 1000, 10 * 1000));
		}

		return true;
	}

	function unlock() {
		if ($this->fp) {
			flock($this->fp, LOCK_UN);
			fclose($this->fp);
			@unlink($this->file);
		}
	}

	function __destruct() {
		$this->unlock();
	}
}
