<?php
namespace Home\Model;
use Think\Model;

class UserModel extends Model {

	public function getUser($openid)
	{
		$sql = '
			SELECT `id`, `account`, `password`
			FROM `user`
			WHERE `openid` = "%s";
			LIMIT 1
		';
		$user = $this->query($sql, $openid);
		if(count($user) === 0)
			return FALSE;
		else
			return $user[0];
	}

	public function unbinding($id)
	{
		$sql = '
			UPDATE `user`
			SET `openid` = ""
			WHERE `id` = %d;
		';
		$this->execute($sql, $id);
	}

}