<?php
namespace FF\Core;
use \FF\Core\Config;

class Controller{

	protected $ff_config = array();
	protected $paramMethod = null;

	public function __construct() {
		config($this->ff_config, null, false);
	}

	public function init($param) {
		Config::set($this->ff_config);
		if (is_null($this->paramMethod)) {
			$this->paramMethod = Config::get('PARAM_METHOD');
		}
		return $this->paramDecode($param);
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
