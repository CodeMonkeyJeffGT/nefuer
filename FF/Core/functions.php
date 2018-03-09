<?php
namespace FF\Core;

function ele(array $array, string $element, $default = null) {
	if (isset($array[$element])) {
		return $array[$element];
	} elseif (isset($array->$element)) {
		return $array->$element;
	} else {
		return $default;
	}
}

function input(string $name, $default = null) {
	$input = array();
	$name = explode('.', $name);
	switch (strtolower($name[0])) {
		case 'get':
			$input = $_GET;
			break;

		case 'post':
			$input = $_POST;
			break;

		case 'json':
			$input = json_decode(file_get_contents('php://input'), true);
			break;

		case 'php':
			$input = parse_str(file_get_contents('php://input'), true);
			break;

		case 'header':
			$input = $_SERVER;

		case 'session':
			$input = $_SESSION;
			break;

		case 'cookie':
			$input = $_COOKIE;
			break;
		
		if (isset($name[1])) {
			return ele($input, $name[1], $default);
		} else {
			return $input;
		}
	}
}

function session(string $name = '.', $value = null, $config = array()) {
	if ($name === '.') {
		return $_SESSION;
	} else {
		$name = explode('.', $name);
		if(is_null($value)) {
			if(count($name) == 1) {
				return $_SESSION[$name[0]];
			} else {
				return $_SESSION[$name[0]][$name[1]];
			}
		} else {
			if(count($name) == 1) {
				$_SESSION[$name[0]] = $value;
			} else {
				$_SESSION[$name[0]][$name[1]] = $value;
			}
		}
	}
}

function cookie(string $name, $value = null, $config = array()) {
	if ($name === '.') {
		return $_COOKIE;
	} else {
		$name = explode('.', $name);
		if(is_null($value)) {
			if(count($name) == 1) {
				return $_COOKIE[$name[0]];
			} else {
				return $_COOKIE[$name[0]][$name[1]];
			}
		} else {
		}
	}
}
