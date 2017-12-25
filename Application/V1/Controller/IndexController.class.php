<?php
namespace V1\Controller;
use V1\Common\ApiController;
class IndexController extends ApiController {
    public function index(){
    	var_dump($this->checkToken());
    }
}