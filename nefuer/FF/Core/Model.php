<?php
namespace FF\Core;
use \FF\Core\Config;

class Model extends \PDO {
	public function __construct() {
		$dsn = 'mysql:host=' . Config::get('DB_HOST') . ';dbname=' . Config::get('DB_DBNAME');
		$username = Config::get('DB_USERNAME');
		$passwd = Config::get('DB_PASSWORD');
		try{
			parent::__construct($dsn, $username, $passwd, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "set names " . Config::get('DB_CHARSET')));
		} catch(\PDOException $e) {
			p($e->getMessage());
		}
	}

	public function query($sql, $param = array()) {
		$st = $this->prepare($sql);
		if ( ! is_array($param)) {
			$st->bindValue(1, $param);
		} else {
			for ($i = 0, $iLoop = count($param); $i < $iLoop; $i++) {
				$st->bindValue($i + 1, $param[$i]);
			}
		}
		$st->execute();
		$error = $st->errorInfo();
		if ($error['0'] !== '00000') {
			throw new \Exception($error[2], 1);
		}
		$result = $st->fetchAll();
		for ($i = 0, $iLoop = count($result); $i < $iLoop; $i++) {
			for ($j = 0, $jLoop = count($result[$i]) / 2; $j < $jLoop; $j++) {
				unset($result[$i][$j]);
			}
		}
		return $result;
	}
}
