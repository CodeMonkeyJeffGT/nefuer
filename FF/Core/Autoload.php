<?php
namespace FF\Core;
use FF\Core\Config;

class Autoload{

	public static function init() {
		$autoload = Config::get('AUTOLOAD');
		foreach ($autoload as $value) {
			$value = ucfirst($value);
			if (file_exists(FF . 'Core/Autoload/' . $value . '.php')) {
				$class_full = '\\FF\\Core\\Autoload\\' . $value;
				$class_full::init();
			} else {
				throw new \Exception($value . '的自动加载方法不存在', 1);
			}
		}
	}

}
