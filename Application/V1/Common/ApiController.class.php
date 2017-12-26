<?php
namespace V1\Common;
use Think\Controller\RestController;

/**
 * Api父类控制器
 * @author  谷田 2017.12.20
 */
class ApiController extends RestController {

	//注释					  W可修改  R可使用  C配置内容
	//----------------------------------------------------
	/* 数据及方法 */
	//指定的操作id						 R
	protected $id;
	//参数数组							 R
	protected $data;
	//payload							WR
	protected $payload;

	//允许的方法							W C
	protected $allowedMethod 	= array('get', 'post', 'put', 'delete');
	//----------------------------------------------------
	/* Token */
	//无需验证登录的方法(get/post等) 	    W C
	protected $noCheckMethod    = array();
	//jwt uuid， 指定缓存对应数据
	private $jwtUuid;
	//jwt头部，自动生成					WRC
	private $jwtHeader;
	//jwt发送方式，可多选					  C
	private $jwtSendWay		    = array('cookie'/*, 'header', 'response'*/);
	//全局jwt密匙    				      C
	private $jwtSecret 			= '*XeOImlfH*)J*HhEFiVTF^#nvNBpgZ0HH9)KF3nNfb$';
	//jwt有效期
	private $jwtExpire 			= 7200;
	//----------------------------------------------------
	/* 微信 */
	//实例化的微信类						 R
	protected $wx;

	//全局微信appid						  C
	private $wxAppid  			= 'wx00c21901537bc5a6';
	//全局微信secret						  C
	private $wxSecret 			= '821ab731206d8993146f3d151d6217b5';
	//----------------------------------------------------
	/* 其他 */
	//自动加载类库 						W C
	protected $autoVendor 		= array('Nefu', 'Wx');

	public function __construct()
	{
		parent::__construct();
        date_default_timezone_set('PRC');
		$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : ''; 
        header('Access-Control-Allow-Origin:' . $origin);
        header('Access-Control-Allow-Headers:token, Origin, X-Requested-With, Content-Type, Accept');
        header('Access-Control-Allow-Methods:PUT,POST,GET,DELETE,OPTIONS');
        header('X-Powered-By: 3.2.1');

		if( ! in_array($this->_method, $this->allowedMethod))
			$this->apiError('不支持' . $this->_method . '方法的请求');

		//验证token并填充payload、jwtHeader
		if(( ! $this->checkToken()) && ( ! in_array($this->_method, $this->noCheckMethod)))
			$this->goLogin();

		//获取参数、加载类库、实例化微信
		$this->getDatas();
		$this->autoVendor();

	}

	/**
	 * 输出程序结果
	 * @param  array $data 返回数据
	 */
	protected function apiSuccess(array $data = array())
	{
		$data['requestData'] = $this->data;
		$jwt = $this->sendJwt();
		if(in_array('response', $this->jwtSendWay))
			$data['jwt'] = $jwt;
		$this->response(array(
			'code' => 0,
			'data' => $data
		), 'json');
	}

	/**
	 * 输出错误信息
	 * @param  string $message 错误信息
	 */
	protected function apiError(string $message)
	{
		$data = array('requestData' => $this->data);
		$jwt = $this->sendJwt();
		if(in_array('response', $this->jwtSendWay))
			$data['jwt'] = $jwt;
		$this->response(array(
			'code'    => 1,
			'message' => $message,
			'data' 	  => $data
		), 'json');
	}

	/**
	 * 输出登录提示
	 */
	protected function goLogin()
	{
		if( ! empty($this->payload['user']))
			unset($this->payload['user']);
		$this->sendJwt();
		$this->response(array(
			'code'    => 2,
			'message' => '请登录'
		), 'json');
	}

	/**
	 * 验证是否登录
	 * @return bool 是否登录
	 */
	protected function checkToken()
	{
		if( ! is_null($this->payload))
			return ( ! empty($this->payload['user']));
        $token = I('server.HTTP_TOKEN', I('cookie.token', I('get.token', '')));
        if('' === $token)
        {
        	$this->payload = array();
        	$this->jwtHeader = array();
        	return FALSE;
        }

        $token_arr = explode('.', $token);
        if(count($token_arr) < 3)
        {
        	$this->payload = array();
        	$this->jwtHeader = array();
        	return FALSE;
        }
        list($this->jwtHeader, $this->payload, $signature) = $token_arr;
    	if(hash_hmac('sha256', $this->jwtHeader . '.' . $this->payload, $this->jwtSecret) !== $signature)
    	{
    		$this->payload = array();
    		$this->jwtHeader = array();
    		return FALSE;
    	}

    	$this->jwtUuid = $this->payload;
    	$this->jwtHeader = json_decode(base64_decode($this->jwtHeader), TRUE);
        $this->payload = S('nefuer.' . $this->jwtUuid);

    	if($this->payload['expire'] < time())
    	{
            $this->payload = array();
            $this->jwtHeader = array();
    		return FALSE;
    	}

    	if( ! isset($this->payload['user']))
    	{
    		return FALSE;
    	}

    	return TRUE;
	}

	/**
	 * 生成token并发送(或返回)
	 * @param  boolean $clearJwt 是否发送空jwt
	 * @return string            token
	 */
	protected function sendJwt()
	{
		$token = '';
		if( ! empty($this->payload))
		{
			if(empty($this->jwtHeader))
				$this->jwtHeader = array(
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                );
            $this->payload['expire'] = time() + $this->jwtExpire;
            if(empty($this->jwtUuid))
            {
            	$this->jwtUuid = md5(uniqid(mt_rand(), true));
            }
            S('nefuer.' . $this->jwtUuid, $this->payload, 7200);
            $this->jwtHeader         = base64_encode(json_encode($this->jwtHeader));
            $this->payload 			 = $this->jwtUuid;
            $prev                    = $this->jwtHeader . '.' . $this->payload;
            $signature = hash_hmac('sha256', $prev, $this->jwtSecret);
            $token = $prev . '.' . $signature;
		}

		if(in_array('header', $this->jwtSendWay))
			header('token:' . $token);
		if(in_array('cookie', $this->jwtSendWay))
			cookie('token', $token);
		return $token;
	}

	/**
	 * 实例化微信类
	 */
	protected function wxInit()
	{
		$access_token = $this->wxAccess_token();
		if(is_null($this->wx))
			$this->wx = new \Wx($this->wxAppid, $this->wxSecret, $access_token);
	}

	/**
	 * 获取微信access_token
	 * @return string access_token
	 */
	protected function wxAccess_token()
	{
		if( ! empty(S('access_token')))
		{
			return S('access_token');
		}
		$this->wx = new \Wx($this->wxAppid, $this->wxSecret);
		$access_token = $this->wx->getAccess_token();
		S('access_token', $access_token, 3600);
		return $access_token;
	}

	/**
	 * 获取参数
	 */
	private function getDatas()
	{
        $this->id   = (int)I('get.id', 0);
        unset($_GET['id']);
        switch ($this->_method)
        {
            case 'get':
                $this->data = I('get.');
                break;
            
            default:
                //$this->data = json_decode(@file_get_contents('php://input'), true);
		$this->data = I('post.');
		break;
        }
	}

	/**
	 * 自动加载第三方类库
	 */
	private function autoVendor()
	{
		for($i = 0, $iloop = count($this->autoVendor); $i < $iloop; $i++)
		{
			Vendor($this->autoVendor[$i]);
		}
	}

	public function __destruct()
	{

		parent::__destruct();
	}

}
