<?php 

class Nefu
{
	private $cookie;
	private $username;
	private $password;

	private function __construct($cookie, $username, $password)
	{
		$this->cookie   = $cookie;
		$this->username = $username;
		$this->password = $password;
	}

	public static function getInstance($username, $password, $cookie = '')
	{
		if( ! empty($cookie))
		{
			return new self($cookie, $username, $password);
		}
		$loginResult = self::login($username, $password);
		if(FALSE !== $loginResult)
		{
			$cookie = $loginResult;
			return new self($cookie, $username, $password);
		}
		else
			return FALSE;
	}

	public function userinfo()
	{
        $url = 'http://jwcnew.nefu.edu.cn/dblydx_jsxsd/ds/zwjs.do?Ves632DSdyV=NEW_XSD_DS';
        $userinfo = $this->curl_safe($url);
        if(FALSE === $userinfo)
        	return FALSE;
        else
        	return $this->html_escape($this->toInfo($userinfo));
	}

	public function userScore($type = 'any')
	{
		$all = array();
		$item = array();
        $url = "http://jwcnew.nefu.edu.cn/dblydx_jsxsd/kscj/cjcx_list"; 
        $data = array(
            'xsfs' => 'max',
            'isfx' => 0,
            'pageIndex' => 1
        );

		if(in_array($type, array('any', 'all')))
		{
	        do
	        {
	            $score = $this->curl_safe($url, $data);
	            if(FALSE === $score)
	            	return FALSE;
	            $all = $this->toScoreAll($score, $all);
	            $data['pageIndex']++;
	        }while($data['pageIndex'] <= $all['page_all']);
	        unset($all['page_all']);

			if('all' === $type)
				return $this->html_escape($all);
		}
		if(in_array($type, array('any', 'item')))
		{
	        unset($data['pageIndex']);
	        $data['xsfs'] = 'item';
	        $item = $this->curl_safe($url, $data);
	        if(FALSE === $item)
	        	return FALSE;
	        $item = $this->toScoreItem($item);

			if('item' === $type)
				return $this->html_escape($item);
		}
		return $this->html_escape(array(
			'all'  => $all,
			'item' => $item
		));
	}

	public function userTest($term = '2017-2018-1', $type = 'any')
	{
		$final = array();
		$item = array();
		$data = array(
			'xnxqid' => $term
		);
		if(in_array($type, array('any', 'final')))
		{
			$url = 'http://jwcnew.nefu.edu.cn/dblydx_jsxsd/xsks/xsksap_list';
			$final = $this->curl_safe($url, $data);
	        if(FALSE === $item)
	        	return FALSE;
	        $final = $this->toTestFinal($final);

			if('final' === $type)
				return $this->html_escape($final);
		}
		if(in_array($type, array('any', 'item')))
		{
			$url = 'http://jwcnew.nefu.edu.cn/dblydx_jsxsd/xsks/xsjdks_list';
			$item = $this->curl_safe($url, $data);
	        if(FALSE === $item)
	        	return FALSE;
	        $item = $this->toTestItem($item);

			if('item' === $type)
				return $this->html_escape($item);
		}
		return $this->html_escape(array(
			'final'  => $final,
			'item' => $item
		));
	}

	public function userLesson()
	{

	}

	public function userLessonTab($week = 'any')
	{

	}

	public function allLessonInfo($term = '2017-2018-1')
	{

	}

	public function lessonInfo($name)
	{

	}

	public function getCookie()
	{
		return $this->cookie;
	}

	private function toInfo($page)
	{
		preg_match_all('/<tr[^>]*?>((.|\n)*?)<\/tr>/i', $page, $page);
		$page = $page[1];
		array_shift($page);
		array_shift($page);
		array_shift($page);
		for ($i = 0; $i < 48; $i++)
		{ 
			if($i == 6)
				$i = 27;
			if($i == 33)
				$i = 46;
			preg_match_all('/<td[^>]*?>(.*?)<\/td>/i', $page[$i], $page[$i]);
			$page[$i] = $page[$i][1];
		}
		$changes = array();
		for($i = 27; $i < 32; $i++)
		{
			if($page[$i][0] === '&nbsp;')
			{
				break;
			}
			$changes[] = array(
				'type'      => str_replace('&nbsp;', '', $page[$i][0]),
				'reason'    => str_replace('&nbsp;', '', $page[$i][1]),
				'time'      => str_replace('&nbsp;', '', $page[$i][2]),
				'o_college' => str_replace('&nbsp;', '', $page[$i][3]),
				'o_major'   => str_replace('&nbsp;', '', $page[$i][4]),
				'o_grade'   => str_replace('&nbsp;', '', $page[$i][5])
			);
		}
		$result = array(
			'college'     => str_replace('院系：', '', $page[0][0]),
			'major'       => str_replace('专业：', '', $page[0][1]),
			'number'      => str_replace('学号：', '', $page[0][4]),
			'name'        => str_replace('&nbsp;', '', $page[1][1]),
			'sex'         => str_replace('&nbsp;', '', $page[1][3]),
			'name_pinyin' => str_replace('&nbsp;', '', $page[1][5]),
			'headimgurl'  => str_replace('" style="width:78px; height:110px;" />', '', str_replace('<img alt="照片" src="', '', $page[1][6])),
			'birthday'    => str_replace('&nbsp;', '', $page[2][1]),
			'mobile'      => str_replace('&nbsp;', '', $page[2][5]),
			'political'   => str_replace('&nbsp;', '', $page[3][3]),
			'orign'       => str_replace('&nbsp;', '', $page[4][1]),
			'nation'      => str_replace('&nbsp;', '', $page[5][3]),
			'changes'     => $changes,
			'in_time'     => str_replace('&nbsp;', '', $page[46][1]),
			'id_num'      => str_replace('&nbsp;', '', $page[47][3])
		);
		return $result;
	}

	private function toScoreAll($page, $all)
	{
		// return array($page, 'page_all' => 1);
		preg_match_all('/<span> 共(.){1,2}页/i', $page, $page_all);
		$page_all = $page_all[1][0];
		preg_match_all('/<tr[^>]*?>((.|\n)*?)<\/tr>/i', $page, $page);
		$page = $page[1];
		array_shift($page);
		array_shift($page);
		for($i = 0, $iloop = count($page); $i < $iloop; $i++)
		{
			preg_match_all('/<td[^>]*?>((.|\n)*?)<\/td>/i', $page[$i], $page[$i]);
			$all[] = array(
				'term'      => $page[$i][1][1],
				'code'      => $page[$i][1][2],
				'name'      => $page[$i][1][3],
				'score'     => $page[$i][1][4],
				'num'       => $page[$i][1][5],
				'time'      => $page[$i][1][6],
				'type'      => $page[$i][1][7],
				'attribute' => $page[$i][1][8],
				'isOpen'    => $page[$i][1][9]
			);
		}
		$all['page_all'] = $page_all;
		return $all;
	}

	private function toScoreItem($page)
	{
		preg_match_all('/<tr[^>]*?>((.|\n)*?)<\/tr>/i', $page, $page);
		$page = $page[1];
		array_shift($page);
		array_shift($page);
		for ($i = 0, $iloop = count($page); $i < $iloop; $i++)
		{ 
			preg_match_all('/<td[^>]*?>((.|\n)*?)<\/td>/i', $page[$i], $page[$i]);
			$page[$i] = array(
				'term'      => $page[$i][1][1],
				'code'      => $page[$i][1][2],
				'name'      => $page[$i][1][3],
				'equal_all' => $page[$i][1][4],
				's_1'       => $page[$i][1][5],
				's_2'       => $page[$i][1][6],
				's_3'       => $page[$i][1][7],
				's_4'       => $page[$i][1][8],
				's_5'       => $page[$i][1][9],
				's_common'  => $page[$i][1][10],
				's_middle'  => $page[$i][1][11],
				's_final'   => $page[$i][1][12],
				'num'       => $page[$i][1][13],
				'time'      => $page[$i][1][14],
				'type'      => $page[$i][1][15],
				'attribute' => $page[$i][1][16]
			);
		}
		return $page;
	}

	private function toTestFinal($page)
	{
		preg_match_all('/<tr[^>]*?>((.|\n)*?)<\/tr>/i', $page, $page);
		$page = $page[1];
		array_shift($page);
		array_shift($page);
		for ($i = 0, $iloop = count($page); $i < $iloop; $i++)
		{ 
			preg_match_all('/<td[^>]*?>((.|n)*?)<\/td>/i', $page[$i], $page[$i]);
			$page[$i] = array(
				'code'    => $page[$i][1][2],
				'name'    => $page[$i][1][3],
				'time'    => $page[$i][1][4],
				'place'   => $page[$i][1][5],
				'seat'    => $page[$i][1][6],
				'per_num' => $page[$i][1][7]
			);
		}
		return $page;
	}

	private function toTestItem($page)
	{
		preg_match_all('/<tr[^>]*?>((.|\n)*?)<\/tr>/i', $page, $page);
		$page = $page[1];
		array_shift($page);
		array_shift($page);
		for ($i = 0, $iloop = count($page); $i < $iloop; $i++)
		{ 
			preg_match_all('/<td[^>]*?>((.|n)*?)<\/td>/i', $page[$i], $page[$i]);
			$page[$i] = array(
				'name'   => $page[$i][1][1],
				'step'   => $page[$i][1][3],
				'week'   => $page[$i][1][4],
				'time_s' => $page[$i][1][5],
				'time_e' => $page[$i][1][6],
				'place'  => $page[$i][1][7],
				'seat'   => $page[$i][1][8]
			);
		}
		return $page;
	}

	private function curl_safe($url, $data = '')
	{
		$result = $this->curl($url, $this->cookie, $data);
		if(empty(htmlspecialchars($result)))
		{
			$try_login = $this->login($this->username, $this->password);
			if(empty($try_login))
			{
				return FALSE;
			}
			else
			{
				$this->cookie = $try_login;
				$result = $this->curl($url, $this->cookie, $data);
				if(empty(htmlspecialchars($result)))
					return FALSE;
				else
					return $result;
			}
		}
		else
			return $result;
	}

	private function html_escape($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $value) {
				$data[$key] = $this->html_escape($value);
			}
			return $data;
		}
		else
			return htmlspecialchars($data);
	}

	private static function login($username, $password)
	{
		$url = 'http://jwcnew.nefu.edu.cn/dblydx_jsxsd/xk/LoginToXk?method=skybinfw&USERNAME=' . $username . '&PASSWORD=' . $password;
		$cookie = '';
		$loginResult = self::curl($url, $cookie);
		if(empty(htmlspecialchars($loginResult)) || empty($cookie))
			return FALSE;
		else
			return $cookie;
	}

	private static function curl($url, &$cookie, $data = '')
	{
	    $ch = curl_init();
		$curl_opt = array(
			CURLOPT_URL            => $url,
			CURLOPT_HEADER         => 1,//设置不返回header
			CURLOPT_RETURNTRANSFER => 1,//设置不显示页面
			CURLOPT_TIMEOUT        => 10, //防止超时
			CURLOPT_COOKIE         => $cookie,
		);
		if( ! empty($data))
		{
	    	//post方式提交
		    $curl_opt[CURLOPT_POST] = 1;
		    //要提交的信息
	    	$data = http_build_query($data);
	    	$curl_opt[CURLOPT_POSTFIELDS] = $data;
		}
		curl_setopt_array($ch, $curl_opt);
	    $result = curl_exec($ch);
		preg_match_all('/Set-Cookie: (.*);/iU', $result, $str); //正则匹配
	    if( ! empty($str))
			$cookie = self::comb_cookie($cookie, $str[1]);
	    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	    $result = substr($result, $headerSize);
	    curl_close($ch);
	    return $result;
	}

	private static function comb_cookie($str, $arr)
	{
		$cookie = array();
		$str = explode('; ', $str);
		if( ! empty($str[0]))
		{
			for($i = 0, $len = count($str); $i < $len; $i++)
			{
				$row = explode('=', $str[$i]);
				$cookie[$row[0]] = $row[1];
			}
		}
		for($i = 0, $len = count($arr); $i < $len; $i++)
		{
			$row = explode('=', $arr[$i]);
			$cookie[$row[0]] = $row[1];
		}
		if(empty($cookie))
			return '';
		$str = '';
		foreach ($cookie as $key => $value)
		{
			$str .= $key . '=' . $value . '; ';
		}
		$str = substr($str, 0, strlen($str) - 2);
		return $str;
	}
}