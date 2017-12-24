<?php
namespace V1\Controller;
use V1\Common\ApiController;
class UserController extends ApiController {

	protected $noCheckMethod = array('post');

    public function login()
    {
    	if(empty($this->data['account']))
    		$this->apiError('请输入账号');
    	if(empty($this->data['password']))
    		$this->apiError('请输入密码');
    	$account = $this->data['account'];
    	$password = strtoupper(md5($this->data['password']));
    	$nefuer = \Nefu::getInstance($account, $password);
    	if(FALSE === $nefuer)
    	{
    		$this->apiError('账号或密码错误');
    	}

        F('nefuer' . $account, $password);
    	$users = D('user');
    	$user = $users->checkUser($account);
    	$wx_remind = array();
    	if(FALSE === $user)
    	{
    		$info = $nefuer->userinfo();
    		if(empty($info['college']))
    		{
    			$this->apiError('登录状态异常');
    		}
    		if(empty($this->payload['openid']))
    		{
	    		$wx_remind = $users->newUser($account, $password, $info);
    		}
	    	else
	    	{
	    		$wx_remind = $users->newUser($account, $password, $info, $this->payload['openid']);
	    	}
            $this->payload['user'] = array(
                'id'       => $users->getLastId(),
                'account'  => $account,
                'password' => $password,
                'cookie'   => $nefuer->getCookie()
            );
    	}
    	else
    	{
    		$this->payload['user'] = array(
                'id'       => $user['id'],
                'account'  => $account,
                'password' => $password,
                'cookie'   => $nefuer->getCookie()
            );
	    	if(empty($this->payload['openid']))
	    	{
	    		if($password !== $user['password'])
		    		$wx_remind = $users->updateUser($account, $password, $user['openid']);
	    	}
            elseif($password !== $user['password'] || $this->payload['openid'] !== $user['openid'])
            {
		    	$wx_remind = $users->updateUser($account, $password, $this->payload['openid']);
            }
    	}

    	$this->send_remind($wx_remind);

    	$this->apiSuccess();
    }

    private function send_remind($wx_remind)
    {
    	$this->wxInit();
        $template_id = '';
        $url = '';
        $data = $wx_remind;
    	for($i = 0, $iloop = count($wx_remind); $i < $iloop; $i++)
    	{
    		switch ($wx_remind[$i]['type']) {
    			case 'binding':
    				$template_id = 'Opw5l9pNNPJKu_Pm7tJVvdFcT02AKE1jKRqOyf5BQnk';
    				$url = 'http://nefuer.jblog.info/nefuer';
                    $data = array(
                        'first' => array(
                            'value' => '您已绑定知派',
                            'color' => ''
                        ),
                        'keyword1' => array(
                            'value' => $wx_remind[$i]['account'],
                            'color' => '#173177'
                        ),
                        'keyword2' =>  array(
                            'value' => date('Y-m-d H:i:s', time()),
                            'color' => '#173177'
                        ),
                        'remark' => array(
                            'value' => "您可在此微信使用以下功能：\n免密查成绩\n接收成绩通知（开发中）",
                            'color' => ''
                        )
                    );
    				break;

    			case 'unbinding':
    				$template_id = 'ggoURgkU3MwSZf7IC6WXU7dC3BZEcNMRzboymrAOK2Y';
    				$url = 'http://nefuer.jblog.info/nefuer';
                    $data = array(
                        'first' =>  array(
                            'value' => '该教务系统账号已在其他微信登录，您已被强制解除绑定',
                            'color' => ''
                        ),
                        'keyword1' => array(
                            'value' => $wx_remind[$i]['account'],
                            'color' => '#173177'
                        ),
                        'keyword2' =>  array(
                            'value' => date('Y-m-d H:i:s', time()),
                            'color' => '#173177'
                        ),
                        'remark' => array(
                            'value' => '如果这不是您本人的操作，请及时修改教务系统密码并重新绑定',
                            'color' => ''
                        )
                    );
    				break;
    			
    			default:
    				break;
    		}
    		$this->wx->msgTemp($wx_remind[$i]['openid'], $template_id, $data, $url);
    	}
    }

}