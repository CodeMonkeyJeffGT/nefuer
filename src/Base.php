<?php
namespace Nefu;

use Nefu\Build;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;

class Base
{
    protected $username;
    protected $password;
    protected $in = false;

    private static $config;     //配置文件
    private static $client;     //guzzle Client

    protected const LOGIN = 'login';
    protected const STU_INFO = 'stuInfo';
    protected const STU_SCORE_ALL = 'stuScoreAll';
    protected const STU_SCORE_ITEM = 'stuScoreItem';

    protected const OK = 0;

    protected const ERR_JWC_UNAVALIABLE = 100;

    protected const ERR_LOGIN_NO = 200;
    protected const ERR_LOGIN_PWD_WRONG = 201;

    protected static $ERR_MSG = array(
        self::OK => '请求成功',
        self::ERR_JWC_UNAVALIABLE => '教务系统服务器不可用',
        self::ERR_LOGIN_NO => '未登录',
        self::ERR_LOGIN_PWD_WRONG => '密码错误',
    );

    private $cookie;          //用户cookie

    /**
     * 构造器，实例化guzzle Client
     */
    public function __construct($timeout = 2.0)
    {
        if (empty(self::$config)) {
            try {
                self::$config = include 'config.php';
            } catch (\Exception $e) {
                throw new \Exception('配置文件不存在');
            }
        }

        if ( ! isset(self::$config['baseUri'])) {
            throw new \Exception('配置文件缺失参数：baseUrI');
        }
        //防止baseUri末尾不是 / 导致的url错误
        if (self::$config['baseUri'][strlen(self::$config['baseUri']) - 1] != '/') {
            self::$config['baseUri'] .= '/';
        }

        if (empty(self::$client)) {
            self::$client = new Client([
                'base_uri' => self::$config['baseUri'],
                'timeout'  => $timeout,
            ]);
        }

        $this->setCookie(array());
    }

    /**
     * 发送http请求
     * @param string $name 请求名称（对应config里的key）
     * @param array $body 请求参数
     * @param array $headers 请求头（一般是没有）
     * 
     * @return Response $response 请求结果
     */
    protected function request($name, $body = array(), $headers = array())
    {
        $headers = Build::build(
            self::$config[$name]['headers'],
            $headers
        );
        $body = Build::build(
            self::$config[$name]['params'],
            $body,
            'http'
        );
        $uri = self::$config[$name]['uri'];
        if ('GET' == self::$config[$name]['method']) {
            $uri .= '?' . $body;
        }
        $request = new Request(
            self::$config[$name]['method'],
            $uri,
            $headers
        );
        $configs = array(
            'cookies' => $this->cookie,
        );
        if ($name === self::LOGIN) {
            $configs['allow_redirects'] = false;
        }
        if ('POST' == self::$config[$name]['method']) {
            $configs['query'] = $body;
        }
        $response = self::$client->send(
            $request,
            $configs
        );
        return $response;
    }

    protected function setCookie($cookie)
    {
        $uri = str_replace('http:', '', str_replace('/', '', self::$config['baseUri']));
        $this->cookie = CookieJar::fromArray($cookie, $uri);
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getCookie()
    {
        $cookies = $this->cookie->toArray();
        foreach ($cookies as $key => $value) {
            $cookies[$value['Name']] = $value['Value'];
            unset($cookies[$key]);
        }
        return $cookies;
    }

    protected function return($code = self::OK)
    {
        return array(
            'code' => $code,
            'msg' => self::$ERR_MSG[$code],
        );
    }

}