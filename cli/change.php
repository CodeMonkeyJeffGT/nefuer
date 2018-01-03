<?php 

session_start();
if(empty($_SESSION['acc']))
	header('location:/?ope=' . urlencode('/nefuer/cli/index.php'));
$db = mysqli_connect('localhost:3306', 'root', 'GT338570');
mysqli_select_db($db, 'nefuer');
$sql = '
	SELECT `user`.`last_upd`
	FROM `user`
	WHERE `acc` = ' . $_SESSION['acc'] . '
	LIMIT 1;
';
$result = mysqli_query($db, $sql);
$status = mysqli_fetch_row($result)[0] != -1 ? '关闭' : '开启';
$change = $status == '关闭' ? '开启' : '关闭';

if($result[0] == -1)
{
	$sql = 'SELECT count(*) FROM `score_record` WHERE `acc` = ' . $_SESSION['acc'];
	$result = mysqli_query($db, $sql);
	$sql = 'UPDATE `user` SET `last_upd` = ' . mysqli_fetch_row($result)[0] . ' WHERE `acc` = ' . $_SESSION['acc'];
}
else
{
	$sql = 'UPDATE `user` SET `last_upd` = -1 WHERE `acc` = ' . $_SESSION['acc'];
}
$result = mysqli_query($db, $sql);
echo json_encode(array('status' => $status, 'change' => $change));

mysqli_close($db); ?>