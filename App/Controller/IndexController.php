<?php
namespace App\Controller;
use FF\Core\Controller;

class IndexController extends Controller{

	public function index($param) {
		$this->assign(array(
			'name' => 'FF',
		));
		$this->display('index');
	}

}
