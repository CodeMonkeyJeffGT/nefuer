<?php 
namespace FF\Core;

class Route{

	public function explain() {
		$pathinfo = $_SERVER['PATH_INFO'];
		$pathinfo = explode('/', $pathinfo);
		return $pathinfo;
	}

}