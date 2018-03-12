<?php
namespace FF\Core;
use \FF\Core\Config;

class Model extends \PDO {
	public function __construct() {
		$dsn = 'mysql:host=' . Config::get('DB_HOST') . ';dbname=' . Config::get('DB_DBNAME');
		$username = Config::get('DB_USERNAME');
		$passwd = Config::get('DB_PASSWORD');
		try{
			parent::__construct($dsn, $username, $passwd);
		} catch(\PDOException $e) {
			p($e->getMessage());
		}
	}

	public function query($sql, $param = array()) {
		$st = $this->prepare($sql);
		if ( ! is_array($param)) {
			$st->bindParam(1, $param);
		} else {
			for ($i = 0, $iLoop = count($param); $i < $iLoop; $i++) {
				$st->bindParam($i + 1, $param[$i]);
			}
		}
		$st->execute();
		$result = $st->fetchAll();
		for ($i = 0, $iLoop = count($result); $i < $iLoop; $i++) {
			for ($j = 0, $jLoop = count($result[$i]) / 2; $j < $jLoop; $j++) {
				unset($result[$i][$j]);
			}
		}
		return $result;
	}
}
