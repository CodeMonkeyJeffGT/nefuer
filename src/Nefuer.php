<?php
namespace Nefu;

use Nefu\Base;

/**
 * 学生类
 */
class Nefuer extends Base
{
    /**
     * 登录并返回结果
     */
    public function login($username = null, $password = null, $cookie = null)
    {
        is_null($username) ? null : $this->username = $username;
        is_null($password) ? null : $this->password = $password;
        if ( ! is_null($cookie)) {
            $this->setCookie($cookie);
            $this->in = true;
            return true;
        } else {
            $response = $this->request(
                static::LOGIN,
                array(
                    'USERNAME' => $this->username,
                    'PASSWORD' => $this->password,
                )
            );
            $code = $response->getStatusCode();
            switch ($code) {
                case 302:
                    $this->in = true;
                    return $this->return();
                case 200: 
                    return $this->return(self::ERR_LOGIN_PWD_WRONG);
                default:
                    return $this->return(self::ERR_JWC_UNAVALIABLE);                
            }
        }
    }

    /**
     * 
     */
    public function info()
    {
        $content = $this->stuRequest(self::STU_INFO);
        if (isset($content['code'])) {
            return $content;
        }
        return $this->toInfo($content);
    }

    /**
     * 
     */
    public function scoreAll()
    {
        $content = $this->stuRequest(self::STU_SCORE_ALL);
        if (isset($content['code'])) {
            return $content;
        }
        return $this->toAll($content);
    }

    /**
     * 
     */
    public function scoreItem()
    {
        $content = $this->stuRequest(self::STU_SCORE_ITEM);
        if (isset($content['code'])) {
            return $content;
        }
        return $this->toItem($content);
    }

    private function toInfo($page)
    {
        $preg = "/<tr[^>]*?>([\s\S]*?)<\/tr/i";
        preg_match_all($preg, $page, $arr);
        $arr = $arr[1];
        $array = array(
            $arr[3],
            $arr[4],
            $arr[8],
            $arr[13],
            $arr[30],
            $arr[31],
            $arr[32],
            $arr[33],
            $arr[34],
            $arr[50],
        );
        
        foreach ($array as $key => $val) {
            $preg = "/<td[^>]*?>([\s\S]*?)<\/td/i";
            preg_match_all($preg, $val, $arr);
            $array[$key] = $arr[1];
        }
        
        $info['college'] = str_replace('院系：', '', $array[0][0]);
        $info['major'] = str_replace('专业：', '', $array[0][1]);
        $info['name'] = str_replace(' ', '', $array[1][1]);
        $info['sex'] = str_replace(' ', '', $array[1][3]);
        $info['nation'] = str_replace(' ', '', $array[2][3]);
        $info['ID'] = $array[9][3];

        return $info;
    }

    private function toAll($page)
    {
        $preg = "/<tr[^>]*?>([\s\S]*?)<\/tr/i";
        preg_match_all($preg, $page, $arr);
        $arr = $arr[1];

        $preg = "/<td[^>]*?>([\s\S]*?)<\/t[dh]/i";

        //学分情况
        $nums = array();
        for ($i = 3; $i < 9; $i++) {
            preg_match_all($preg, $arr[$i], $tmp);
            $tmp = $tmp[1];
            $nums[] = array(
                'all' => $tmp[1],
                'done' => $tmp[2],
                'need' => $tmp[3],
                'doing' => $tmp[4],
                'fail' => $tmp[5],
            );
        }

        //通选情况
        $pubNums = array(
            'all' => array(),
            'A' => array(),
            'B' => array(),
            'C' => array(),
            'D' => array(),
            'E' => array(),
            'F' => array(),
            'G' => array(),
            'H' => array(),
        );
        for ($i = 10; $i < 12; $i++) {
            preg_match_all($preg, $arr[$i], $tmp);
            $tmp = $tmp[1];
            $key = 'all';
            if ($i == 11) {
                $key = 'done';
            }

            $pubNums['all'][$key] = $tmp[1];
            $pubNums['A'][$key] = $tmp[2];
            $pubNums['B'][$key] = $tmp[3];
            $pubNums['C'][$key] = $tmp[4];
            $pubNums['D'][$key] = $tmp[5];
            $pubNums['E'][$key] = $tmp[6];
            $pubNums['F'][$key] = $tmp[7];
            $pubNums['G'][$key] = $tmp[8];
            $pubNums['H'][$key] = $tmp[9];
        }
        $pubNums['all']['all'] = $nums[2]['all'];
        
        //成绩
        $scores = array();
        for ($i = 15; $i < count($arr); $i++) {
            preg_match_all($preg, $arr[$i], $tmp);
            $tmp = $tmp[1];

            if (count($tmp) < 6 || $tmp[0] == '&nbsp;') {
                continue;
            }

            for ($j = 0; $j < 6; $j++) {
                $pregcb = "/<\/*?font[^>]*?>/i";
                $tmp[$j] = preg_replace($pregcb, '', $tmp[$j]);
            }

            $score = array(
                'term' => $tmp[0],
                'code' => $tmp[1],
                'name' => $tmp[2],
                'num' => $tmp[3],
                'type' => $tmp[4],
                'score' => $tmp[5],
            );
            switch ($score['type']) {
                case '实践教学':
                    $score['type'] = '实践教学';
                    break;
                case '通识教育选修课':
                    $score['type'] = '通识教育选修课';
                    break;
                case '专业选修课':
                    $score['type'] = '专业选修课';
                    break;
                default:
                    $score['type'] = '必修课';
                    break;
            }
            switch ($score['score']) {
                case '待修读':
                    $score['status'] = 'no';
                    $score['score'] = 0;
                    break;
                case '修读中':
                    $score['status'] = 'doing';
                    $score['score'] = 0;
                    break;
                default:
                    $score['status'] = 'done';
                    break;
            }
            $scores[] = $score;
        }

        return array(
            'nums' => $nums,
            'pubNums' => $pubNums,
            'score' => $scores,
        );
    }

    private function toItem($page)
    {
        $preg = "/<tr[^>]*?>([\s\S]*?)<\/tr/i";
        preg_match_all($preg, $page, $arr);
        $arr = $arr[1];

        $scores = array();
        for ($i = 2, $loop = count($arr); $i < $loop; $i++) {
            $preg = "/<td[^>]*?>([\s\S]*?)<\/td/i";
            preg_match_all($preg, $arr[$i], $score);
            $score = $score[1];
            $scoreTmp = array(
                'name' => $score[3],
                'code' => $score[2],
                'term' => $score[1],
                'item' => array(),
                'normal' => $score[10],
                'mid' => $score[11],
                'fin' => $score[12],
                'num' => $score[13],
                'time' => $score[14],
            );
            for ($j = 5; $j < 10; $j++) {
                if ($score[$j] != '') {
                    $scoreTmp['item'][] = $score[$j];
                } else {
                    break;
                }
            }
            $scores[] = $scoreTmp;
        }

        return $scores;
    }

    private function stuRequest($name)
    {
        if ( ! $this->in) {
            return $this->return(self::ERR_LOGIN_NO);
        }
        
        $response = $this->request(
            $name
        );

        $code = $response->getStatusCode();
        if ($code != 200) {
            return $this->return(self::ERR_JWC_UNAVALIABLE); 
        }

        $content = $response->getBody()->getContents();

        if (htmlspecialchars($content) == '') {
            $rlg = $this->reLogin();
            if ($rlg['code'] != 0) {
                return $rlg;
            }
            return $this->stuRequest($name);
        }

        return $content;
    }

    private function reLogin()
    {
        $this->in = false;
        $rst = $this->login();
        return $rst;
    }

}