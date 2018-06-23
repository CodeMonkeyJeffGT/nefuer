<?php
namespace FF\Core;

class Config{
	
	private static $config;

	public function init() {
		$default = ifile(CONFIG . 'default.php');
		$user = ifile(CONFIG . 'config.php');
		$debug = ifile(CONFIG . 'debug.php');
		if (is_null($default)) {
			throw new \Exception('请确认基础配置文件是否存在', 1);
		}
		if (is_null($user)) {
			$user = array();
		}
		if (is_null($debug)) {
			$debug = array();
		}
		self::$config = array_merge($default, $user, $debug);

		if (defined('FF_DEBUG')) {
			self::$config['FF_DEBUG'] = FF_DEBUG;
		}

	}

	public function get($name = '.', $default = null) {
		if ($name === '.') {
			return self::$config;
		}
		if (isset(self::$config[$name])) {
			return self::$config[$name];
		} else {
			return $default;
		}
	}

	public function set($name, $value = null) {
		if (is_array($name)) {
			$config = array();
			foreach ($name as $key => $value) {
				$config[strtoupper($key)] = $value;
			}

			self::$config = array_merge(self::$config, $config);
			foreach ($config as $key => $value) {
				if (is_null($value)) {
					unset(self::$config[$key]);
				}
			}
		} else {
			if (is_null($value)) {
				unset(self::$config[strtoupper($name)]);
			} else {
				self::$config[strtoupper($name)] = $value;
			}
		}
	}

}
