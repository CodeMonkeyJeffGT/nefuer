<?php
namespace App\Controller;
use FF\Core\Controller;

class IndexController extends Controller{

	public function index($param) {
		p($param);
		p(config());
		p(input('get.a'));
		p(input('.a'));
		p(input('header.REQUEST_URI'));
	}

}
