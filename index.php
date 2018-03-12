<?php
use FF\FF;

defined('ROOT') or define('ROOT', __DIR__ . '/');
defined('APP') or define('APP', ROOT . 'App/');
defined('VIEW') or define('VIEW', APP . 'View/');
defined('CONFIG') or define('CONFIG', ROOT . 'Config/');
defined('FF') or define('FF', ROOT . 'FF/');
defined('LOG') or define('LOG', ROOT . 'log/');

include_once(FF . 'FF.php');
include_once(FF . 'Core/functions.php');

FF::run();

