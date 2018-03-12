<?php
namespace FF;
use FF\Core\Config;
use FF\Core\Route;

class FF{

	public static function run() {
		//注册自动加载
		spl_autoload_register('\FF\FF::auto_load');

		//引入配置
		Config::init();

		//调试模式
		if (Config::get('DEBUG')) {
			ini_set('display_error', 'On');
		} else {
			ini_set('display_error', 'Off');
		}

		//自动加载
		if (count(Config::get('AUTOLOAD')) > 0) {
			\FF\Core\Autoload::init();
		}

		list($controller, $action, $param) = Route::explain();
		$controller = '\\App\\Controller\\' . $controller . 'Controller';
		$controller = new $controller;
		$param = $controller->init($param);
		$controller->$action($param);
	}

	public static function auto_load($className) {
		ifile(ROOT . str_replace('\\', '/', $className) . '.php', true);
	}

}
