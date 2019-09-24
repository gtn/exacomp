<?php

namespace Super;

class Cache {
	static function get($cache_id) {
		if ($cache_file = self::getCacheFile($cache_id)) {
			// cache exists
			$cache_data = unserialize(file_get_contents($cache_file));

			touch($cache_file);

			return $cache_data;
		}

		return null;
	}

	static function set($cache_id, $data) {
		$cache_file = self::getCacheFilePath($cache_id);

		$cache_data = serialize($data);

		file_put_contents($cache_file, $cache_data);
	}

	static function delete($cache_id) {
		$cache_file = self::getCacheFilePath($cache_id);

		@unlink($cache_file);
	}

	public static function getCacheId($cache_id) {
		if (is_array($cache_id) || is_object($cache_id)) {
			// etag can be an object (eg. database query)
			$cache_id = (array)$cache_id;

			self::getCacheIdIterator($cache_id);

			$string_id = '';

			if (is_array($cache_id)) {
				foreach ($cache_id as $key => $value) {
					if (is_scalar($value)) {
						if ($key === 0) {
							$key = 'area';
						} elseif (is_int($key)) {
							// no int keys allowed in tags
							continue;
						}

						$prepend = str_replace('-', '', $key).'-'.str_replace('-', '', $value).'-';
						if (strlen($string_id.$prepend) > 150) {
							// too long
							break;
						}
						$string_id .= $prepend;
					}
				}
			}

			$string_id .= 'cacheid-'.md5(filemtime(__FILE__).json_encode($cache_id));
		} elseif (is_string($cache_id) && (strlen($cache_id) > 10)) {
			$string_id = $cache_id;
		} else {
			die('TODO: check if md5, else generate md5');
			$string_id = '...';
		}

		return $string_id;
	}

	static function getFilesByTags($tags) {
		$tags = (array)$tags;
		if (isset($tags[0])) {
			$tags['area'] = $tags[0];
			unset($tags[0]);
		}

		$value = reset($tags);
		$key = key($tags);
		$tag = str_replace('-', '', $key).'-'.str_replace('-', '', $value);

		$foundFiles = [];
		$possibleFiles = glob(Fs::joinPath(self::getCachePath(), '*'.$tag.'*')) ?: [];

		foreach ($possibleFiles as $filepath) {
			$fileTags = static::parseTags($filepath);

			$found = false;
			foreach ($tags as $key => $value) {
				if (array_key_exists($key, $fileTags) && $fileTags[$key] == $value) {
					$found = true;
				} else {
					$found = false;
					break;
				}
			}

			if ($found) {
				$foundFiles[] = (object)[
					'file' => $filepath,
					'tags' => $tags,
				];
			}
		}

		return $foundFiles;
	}

	static function clearFilesByTags($tags) {
		$files = static::getFilesByTags($tags);

		foreach ($files as $file) {
			@unlink($file->file);
		}

		return true;
	}

	static function parseTags($filepath) {
		$filename = basename($filepath);

		$tmp_tags = explode('-', $filename);

		$tags = [];
		for ($i = 0; $i < count($tmp_tags); $i += 2) {
			$tags[$tmp_tags[$i]] = $tmp_tags[$i + 1];
		}

		return $tags;
	}

	private static function getCacheFilePath($cache_id) {
		return Fs::joinPath(static::getCachePath(), self::getCacheId($cache_id).'.cache');
	}

	static function getCachePath() {
		static $path;
		if ($path !== null) {
			return $path;
		}

		$path = Fs::joinPath(sys_get_temp_dir(), 'super-cache');
		if (!is_dir($path)) {
			mkdir($path);
		}

		return $path;
	}


	static function getCacheFile($cache_id) {
		$cache_file = self::getCacheFilePath($cache_id);

		if (file_exists($cache_file)) {
			return $cache_file;
		} else {
			return null;
		}
	}

	public static function clean($lifetime) {
		Fs::deleteOlderThan(static::getCachePath(), $lifetime);
	}

	/**
	 * don't use whole object, just use it's cache_id
	 */
	private static function getCacheIdIterator(&$cache_id, $level = 0) {
		foreach ($cache_id as $key => &$value) {
			if (is_array($value)) {
				self::getCacheIdIterator($value, $level + 1);
			} elseif (is_object($value) && !empty($value->cache_id)) {
				$cache_id[$key] = $value->cache_id;
				// TODO: go through subs? or is it string?
				/*
				if (is_array($cache_id[$key]) {
				}
				*/
			}
		}
	}

	static function time($cache_id) {
		if ($cache_file = self::getCacheFile($cache_id)) {
			return filemtime($cache_file);
		}
	}

	static function full($cache_id, $callback, array $params = [], array $options = []) {
		if (headers_sent()) {
			throw new Exception('Cache::whole not supported: headers already sent');
		}

		$cb = new CacheCallback($cache_id, function() use (&$cb, $callback) {
			ob_start();

			$ret = call_user_func_array($callback, func_get_args());

			header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
			header("Etag: \"".$cb->cacheId()."\"");

			ob_end_flush();

			return $ret;
		}, $params, $options);

		$data = $cb->get();

		if ($data &&
			((@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $data->time) ||
				(@trim($_SERVER['HTTP_IF_NONE_MATCH']) == $cb->cacheId()))
		) {
			header("HTTP/1.1 304 Not Modified");
			header(@$data->headers('Etag'));
			header(@$data->headers('Last-Modified'));

			exit;
		}

		return $cb->execute();
	}

	static function callback($cache_id, $callback, array $params = [], array $options = []) {
		$cb = new CacheCallback($cache_id, $callback, $params, $options);

		return $cb->execute();
	}

	static function createCallback($cache_id, $callback, array $params = [], array $options = []) {
		return new CacheCallback($cache_id, $callback, $params, $options);
	}

	static function staticCallback($cache_id, $callback, array $params = []) {
		return StaticCacheCallback::get($cache_id, $callback, $params);
	}

	static function object($cache_id, $class, array $params = [], array $options = []) {
		$cb = new CacheCallback($cache_id, function() use ($class, $params) {
			$r = new \ReflectionClass($class);

			return $r->newInstanceArgs($params);
		}, $params, $options);

		return $cb->execute();
	}
}

class CacheItem {
	protected $cache_id;

	function __construct($cache_id) {
		$this->cache_id = Cache::getCacheId($cache_id);
	}

	function get() {
		return Cache::get($this->cache_id);
	}

	function set($data) {
		return Cache::set($this->cache_id, $data);
	}

	function delete() {
		return Cache::delete($this->cache_id);
	}

	function cacheId() {
		return $this->cache_id;
	}
}

class CacheCallback extends CacheItem {
	protected $params;
	protected $callback;
	protected $ttl;
	protected $lock;
	protected $etag;

	protected $time;

	function __construct($cache_id, $callback, array $params = [], array $options = []) {
		if (!is_callable($callback)) {
			throw new Exception('no valid callback given');
		}

		$cache_id = (array)$cache_id;
		$cache_id[] = $params;

		$this->params = $params;
		$this->callback = $callback;

		foreach ($options as $key => $value) {
			if ($key == 'ttl') {
				$this->setTTl($value);
			} elseif ($key == 'etag') {
				$this->setEtag($value);
			} elseif ($key == 'lock') {
				$this->setLock($value);
			} else {
				throw new \Exception("option $key not found");
			}
		}

		parent::__construct($cache_id);
	}

	protected function _headersDiff($headersBefore) {
		$headersAfter = headers_list();
		foreach ($headersAfter as $key => $value) {
			if (in_array($value, $headersBefore)) {
				unset($headersAfter[$key]);
			}
		}

		$diff = array_values($headersAfter);
		$headers = [];
		foreach ($diff as $header) {
			$id = explode(':', $header)[0];
			if (!isset($headers[$id])) {
				$headers[$id] = $header;
			} else {
				$headers[] = $header;
			}
		}

		return $headers;
	}

	function getLock() {
		static $lock;

		if (!$lock) {
			$lockfile = $this->getLockFile();
			$lock = Fs::getLock($lockfile, is_int($this->lock) ? $this->lock : 30);
		}

		return $lock;
	}

	function getLockFile() {
		return Fs::joinPath(Cache::getCachePath(), Cache::getCacheId($this->cache_id).'.lock');

	}

	function get() {
		$this->time = null;

		$data = parent::get();

		if (!$data) {
			return;
		}

		$this->time = $data->time;

		if ($this->ttl && ($data->time > time() || $data->time < (time() - $this->ttl))) {
			// too old, or newer
			return;
		}

		if ($this->etag && ($this->etag !== $data->etag)) {
			return;
		}

		return $data;
	}

	function execute() {
		$data = $this->get();

		if (!$data && $this->lock) {
			$this->getLock()->lock();

			$data = $this->get();

			if ($data) {
				$this->getLock()->unlock();
			}
		}

		if ($data) {
			foreach ($data->headers as $header) {
				header($header);
			}

			echo $data->output;

			return $data->ret;
		}

		$headersBefore = headers_list();

		ob_start();
		$ret = call_user_func_array($this->callback, $this->params);
		$output = ob_get_flush();

		$headers = $this->_headersDiff($headersBefore);

		$this->time = time();

		$this->set((object)[
			'time' => $this->time,
			'etag' => $this->etag,
			'ret' => $ret,
			'output' => $output,
			'headers' => $headers,
		]);

		if ($this->lock) {
			$this->getLock()->unlock();
		}

		return $ret;
	}

	function setTTl($seconds) {
		$this->ttl = $seconds;
	}

	function getTTl() {
		return $this->ttl;
	}

	function setEtag($etag) {
		$this->etag = Cache::getCacheId($etag);
	}

	function setLock($lock) {
		$this->lock = $lock;
	}

	function getTime() {
		return $this->time;
	}
}

class StaticCacheCallback {
	static $cachedItems = [];

	static function get($cache_id, $callback, $params = []) {
		$id = json_encode([$cache_id, $params]);
		if (array_key_exists($id, static::$cachedItems)) {
			return static::$cachedItems[$id];
		} else {
			return static::$cachedItems[$id] = call_user_func_array($callback, $params);
		}
	}
}
