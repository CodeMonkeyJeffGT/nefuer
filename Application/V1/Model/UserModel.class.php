<?php
namespace V1\Model;
use Think\Model;

class UserModel extends Model {
    private $lastId = 0;
    public function getLastId()
    {
    	return $this->lastId;
    }

	public function getUser($openid)
	{
		$sql = '
			SELECT `account`, `password`
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

	public function checkUser($account)
	{
		$sql = '
			SELECT `id`, `password`, `openid`
			FROM `user`
			WHERE `account` = %d;
			LIMIT 1
		';
		$user = $this->query($sql, $account);
		if(count($user) === 0)
			return FALSE;
		else
			return $user[0];
	}

	public function newUser($account, $password, $info, $openid = '')
	{
		$wx_remind = array();
		if($openid !== '')
		{
			$wx_remind[] = array(
				'type' => 'binding',
				'openid' => $openid,
				'account' => $account
			);
			$sql = '
				SELECT `id`, `account`
				FROM `user`
				WHERE `openid` = "%s";
			';
			$users = $this->query($sql, $openid);
			if(count($users) === 1)
			{
				$wx_remind[] = array(
					'type'    => 'unbinding',
					'openid'  => $openid,
					'account' => $users[0]['account']
				);
				$sql = '
					UPDATE `user`
					SET `openid` = ""
					WHERE `id` = %d;
				';
				$this->execute($sql, $users[0]['id']);
			}
		}

		$grade = substr($account, 0, 4);
		
		$c_id = $this->query('
			SELECT `id`
			FROM `college`
			WHERE `name` = "%s"
			LIMIT 1;
		', $info['college']);
		$c_id = count($c_id) === 1 ? $c_id[0]['id'] : M('college')->add(array('name' => $info['college']));
		$m_id = $this->query('
			SELECT `id`
			FROM `major`
			WHERE `name` = "%s"
			LIMIT 1;
		', $info['major']);
		$m_id = count($m_id) === 1 ? $m_id[0]['id'] : M('major')->add(array('name' => $info['major']));

		$sex = ($info['sex'] === '男' ? 1 : ($info['sex'] === '女' ? 2 : 0));

		$sql = '
			INSERT INTO `user`(`account`, `password`, `name`, `grade`, `c_id`, `m_id`, `openid`, `sex`, `last_upd`, `birthday`)
			VALUES (%d, "%s", "%s", %d, %d, %d, "%s", %d, %d, %d);
		';
		$this->execute($sql, $account, $password, $info['name'], $grade, $c_id, $m_id, $openid, $sex, 0, $info['birthday']);
		$this->lastId = $this->getLastInsID();

		return $wx_remind;
	}

	public function updateUser($account, $password, $openid)
	{
		$wx_remind = array();
		$sql = '
			SELECT `id`, `account`, `password`, `openid`
			FROM `user`
			WHERE `openid` = "%s" OR `account` = %d;
		';
		$users = $this->query($sql, $openid, $account);
		if(count($users) === 1)	//只涉及到一条记录的情况
		{
			if((int)$account === (int)$users[0]['account'] && $openid === $users[0]['openid'])
			{	//账号与openid相同，只有密码变化，修改密码不发送模板消息
				if($password !== $users[0]['account'])
				{
					$sql = '
						UPDATE `user`
						SET `password` = "%s"
						WHERE `id` = %d;
					';
					$this->execute($sql,  $users[0]['id']);
				}
			}
			elseif((int)$account === (int)$users[0]['account'])
			{	//账号相同openid不同，则修改密码及openid并发送绑定信息
				if($users[0]['openid'] !== '')
				{	//openid不为空，为该openid发送解绑信息
					$wx_remind[] = array(
						'type'    => 'unbinding',
						'openid'  => $users[0]['openid'],
						'account' => $users[0]['account']
					);
				}
				$sql = '
					UPDATE `user`
					SET `password` = "%s", `openid` = "%s"
					WHERE `id` = %d;
				';
				$this->execute($sql, $password, $openid, $users[0]['id']);
				$wx_remind[] = array(
					'type'    => 'binding',
					'openid'  => $openid,
					'account' => $account
				);
			}
			else
			{	//openid相同账号不同，说明账号不存在，不存在这种情况	
			}
		}
		else 					//涉及到两个用户的情况
		{
			if($users[0]['openid'] === $openid)
			{
				$bind_acc   = $users[0];
				$unbind_acc = $users[1];
			}
			else
			{
				$bind_acc   = $users[1];
				$unbind_acc = $users[0];
			}
			$sql = '
				UPDATE `user`
				SET `password` = "%s", `openid` = "%s"
				WHERE `id` = %d;
				UPDATE `user`
				SET `openid` = ""
				WHERE `id` = %d;
			';
			$this->execute($sql, $password, $openid, $bind_acc['id'], $unbind_acc['id']);
			$wx_remind[] = array(
				'type'    => 'binding',
				'openid'  => $openid,
				'account' => $account
			);
			$wx_remind[] = array(
				'type'    => 'unbinding',
				'openid'  => $openid,
				'account' => $unbind_acc['account']
			);
			$wx_remind[] = array(
				'type'    => 'unbinding',
				'openid'  => $account,
				'account' => $bind_acc['openid']
			);
		}

		return $wx_remind;

	}

}