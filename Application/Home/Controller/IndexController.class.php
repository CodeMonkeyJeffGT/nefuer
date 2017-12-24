<?php
namespace Home\Controller;
use Home\Common\ApiController;
class IndexController extends ApiController {

	protected $noCheckMethod = array('get');

    public function index()
    {
    	if($this->checkToken())
    	{
    		$this->loadPage(I('get.ope', '/'));
    	}
    	else
    	{
    		$this->wxLogin(I('get.ope', '/'));
    	}
    }

    public function unbinding()
    {
        if(empty($this->payload['openid']))
            die('<script>alert("您还未进行微信登录")</script>');
        $users = D('user');
        if(empty($this->payload['user']))
        {
            $user = D('user')->getUser($openid);
            if(FALSE === $user)
            {
                die('<script>alert("您还未绑定任何账号")</script>');
            }
            $this->payload['user'] = $user;
        }
        $users->unbinding($this->payload['user']['id']);
        $this->send_remind(array(array(
            'type' => 'unbinding',
            'account' => $this->payload['user']['account'],
            'openid' => $this->payload['openid']
        )));

        unset($this->payload['user']);
        $this->sendJwt();
        echo '<script>alert("解绑成功，请关闭该页面")</script>';
    }

    public function signOut()
    {
        unset($this->payload['user']);
        $this->sendJwt();
        echo '<script>alert("注销成功，请关闭该页面")</script>';
    }

    public function wxLogin($ope = '/')
    {
		$this->wxInit();
    	if( ! isset($_GET['code']))
    	{
    		echo '<meta name=”viewport” content=”width=device-width, initial-scale=1, maximum-scale=1″><style>*{margin:0} html,body{height:100%;width:100%;background-color:rgb(225, 255,255);text-align:center;font-size:3rem} div{position: absolute;top: 50%;transform:translateY(-50%);width:100%}</style><div>微信登陆中</div>';
    		$this->wx->userCode('http://nefuer.jblog.info/nefuer/index.php/Home/index/wxLogin', FALSE, $ope);
    	}
    	else
    	{
    		$ope = ('' !== $_GET['state']) ? $_GET['state'] : '/';
    		$openid = $this->wx->userOpenid($_GET['code']);
    		if(isset($openid['errcode']))
    			$this->error('errmsg');
    		$openid = $openid['openid'];
    		$this->payload['openid'] = $openid;

    		$users = D('user');
    		$user = D('user')->getUser($openid);
    		if(FALSE === $user)
    		{
    			$this->loginPage($ope);
    		}
    		$nefuer = \Nefu::getInstance($user['account'], $user['password']);
            $this->payload['user'] = array(
                'id'       => $user['id'],
                'account'  => $user['account'],
                'password' => $user['password'],
                'cookie'   => $nefuer->getCookie()
            );
    		if(FALSE === $nefuer)
    		{
    			$this->loginPage($ope);
    		}
    		else
    		{
                F('nefuer' . $account, $password);
    			$this->loadPage($ope);
    		}
    	}
    }

    public function loadPage($ope = '/')
    {
        $this->sendJwt();
    	if(strpos('://', $ope))
    	{
    		$link = strpos('?', $ope) ? '&' : '?';
    		header('location:' . $ope . $link . 'id=' . $this->payload['user']['id']);die;
    	}
    	switch($ope[0])
    	{
    		case '#' : 
    			header('location:/Public/index.html' . $ope);die;
    			break;
    		default :
    			header('location:' . $ope);die;
    	}
    }

    public function loginPage($ope = '/')
    {
        $this->sendJwt();
    	$this->assign('ope', $ope);
    	$this->display('login');
        die;
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
                case 'unbinding':
                    $template_id = 'ggoURgkU3MwSZf7IC6WXU7dC3BZEcNMRzboymrAOK2Y';
                    $url = 'http://nefuer.jblog.info/nefuer';
                    $data = array(
                        'first' =>  array(
                            'value' => '您已解除绑定教务系统账号',
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
                            'value' => '欢迎您的下次使用',
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