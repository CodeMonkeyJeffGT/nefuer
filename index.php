<?php

require __DIR__ . '/vendor/autoload.php';

use Nefu\Nefuer;

$nefuer = new Nefuer();
echo '<pre>';
$rst = $nefuer->login(
    2015214310,
    strtoupper(md5('GT338570'))
);
// $rst = $nefuer->info();
// $rst = $nefuer->scoreItem();
// $rst = $nefuer->scoreAll();
var_dump($rst);