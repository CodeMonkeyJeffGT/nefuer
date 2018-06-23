<?php
namespace App\Controller;
use FF\Core\Controller;

class IndexController extends Controller{

	public function index($param) {
		$db = new \FF\Core\Model();
		p($db->query('DESC `user`'));
		$this->assign(array(
			'name' => 'FF',
		));
		$this->display('index');
	}

}
