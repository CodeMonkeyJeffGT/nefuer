<?php
namespace FF\Core;
use \FF\Core\Config;

class Controller{

	protected $ff_config = array();
	protected $paramMethod = null;
	private $assignData = array();

	public function __construct() {
		Config($this->ff_config, null, false);
	}

	public function init($param) {
		Config::set($this->ff_config);
		if (is_null($this->paramMethod)) {
			$this->paramMethod = Config::get('PARAM_METHOD');
		}
		return $this->paramDecode($param);
	}

	protected function assign($data) {
		$this->assignData = array_merge($this->assignData, $data);
	}

	protected function display($page) {
		extract($this->assignData);
		$page = VIEW . $page . '.php';
		if ( ! is_file($page)) {
			throw new \Exception($page . '页面不存在');die;
		}
		include($page);
	}

	private function paramDecode($param) {
		switch ($this->paramMethod) {
			case 'json':
				return json_decode(ele($param, 0, '[]'));
				break;

			case 'rest':
				return ele($param, 0);
				break;
			
			case 'no': 
				return $param;

			case 'normal':

			default:
				$newParam = array();
				for($i = 0, $iLoop = count($param); $i < $iLoop - 1; $i+= 2) {
					$newParam[$param[$i]] = $param[$i + 1];
				}
				return $newParam;
				break;
		}
	}

}
