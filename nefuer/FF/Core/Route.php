<?php 
namespace FF\Core;
use FF\Core\Config;

class Route{

	public function explain() {
		$pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$pathinfo = explode('/', trim($pathinfo, '/'));
		$result = array();
		$result = array(
			ucfirst(ele($pathinfo, 0, Config::get('DEFAULT_CONTROLLER', 'index'), false)),
			ele($pathinfo, 1, Config::get('DEFAULT_aACTION', 'index'), false),
			array(),
		);
		if (count($pathinfo) > 2) {
			$result[2] = array_slice($pathinfo, 2);
		}
		return $result;
	}

}
