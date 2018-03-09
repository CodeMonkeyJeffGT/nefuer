<?php
namespace FF;

class FF{

	public static function run(string $configFile = '') {
		spl_autoload_register('\FF\FF::auto_load');
		include ROOT . 'FF/Core/functions.php';



		var_dump(\FF\Core\Route::explain());
	}

	public static function auto_load($className) {
		include_once(ROOT . str_replace('\\', '/', $className) . '.php');
	}

}
