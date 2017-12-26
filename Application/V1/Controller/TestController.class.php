<?php
namespace V1\Controller;
use V1\Common\ApiController;
class TestController extends ApiController {

    public function index()
    {
        $nefuer = \Nefu::getInstance($this->payload['user']['account'], $this->payload['user']['password'], $this->payload['user']['cookie']);

        $grade = substr($this->payload['user']['account'], 0, 4);
        $year = date('Y');
        if(date('m') < 9)
        	$year--;

        $term = array();
        for($i = $grade; $i <= $year; $i++)
        {
        	$term[$i . '-1'] = $nefuer->userTest($i . '-' . ($i + 1) . '-1');
	        if(FALSE === $term[$i . '-1'])
	        {
	            $this->apiError('获取考试失败，请重新登录或查看教务系统是否可用');
	        }
	        if(empty($term[$i . '-1']['item']) && empty($term[$i . '-1']['final']))
	        	unset($term[$i . '-1']);
        	$term[$i . '-2'] = $nefuer->userTest($i . '-' . ($i + 1) . '-2');
	        if(FALSE === $term[$i . '-2'])
	        {
	            $this->apiError('获取考试失败，请重新登录或查看教务系统是否可用');
	        }
	        if(empty($term[$i . '-2']['item']) && empty($term[$i . '-2']['final']))
	        	unset($term[$i . '-2']);
        }

        foreach ($term as $key => $value) {
        	$tmpTerm = array();
        	for($i = 0, $iloop = count($value['final']); $i < $iloop; $i++)
        	{
        		$tmpTerm[] = array(
        			'name' => $value['final'][$i]['name'],
        			'place' => $value['final'][$i]['place'],
        			'seat' => $value['final'][$i]['seat'],
        			'type' => 'final',
        			'timeS' => substr($value['final'][$i]['time'], 0, 16),
        			'timeE' => (substr($value['final'][$i]['time'], 0, 11) . substr($value['final']['time'], 11, 16))
        		);
        	}
        	for($i = 0, $iloop = count($value['item']); $i < $iloop; $i++)
        	{
        		$tmpTerm[] = array(
        			'name' => $value['item'][$i]['name'],
        			'place' => $value['item'][$i]['place'],
        			'seat' => $value['item'][$i]['seat'],
        			'type' => 'item',
        			'timeS' => $value['item'][$i]['time_s'],
        			'timeE' => $value['item'][$i]['time_e'],
        			'step' => $value['item'][$i]['step']
        		);
        	}
        	$term[$key] = $this->qSort($tmpTerm);
        }

        $this->payload['user']['cookie'] = $nefuer->getCookie();
    	$this->apiSuccess($term);
    }

    // private function send_remind($wx_remind)
    // {
    // 	$this->wxInit();
    //     $template_id = '';
    //     $url = '';
    //     $data = $wx_remind;
    // 	for($i = 0, $iloop = count($wx_remind); $i < $iloop; $i++)
    // 	{
    // 		switch ($wx_remind[$i]['type']) {
    // 			case 'binding':
    // 				$template_id = '';
    // 				$url = '';
    //                 $data = array(
    //                     "first" => array(
    //                         "value" => "",
    //                         "color" => ""
    //                     ),
    //                     "keyword1" => array(
    //                         "value" => '',
    //                         "color" => "#173177"
    //                     ),
    //                     "keyword2" =>  array(
    //                         "value" => date('Y-m-d H:i:s', time()),
    //                         "color" => "#173177"
    //                     ),
    //                     "remark" => array(
    //                         "value" => "",
    //                         "color" => ""
    //                     )
    //                 );
    // 				break;
    			
    // 			default:
    // 				break;
    // 		}
    // 		$this->wx->msgTemp($wx_remind[$i]['openid'], $template_id, $data, $url);
    // 	}
    // }

    private function qSort($arr)
    {
    	return $arr;
    }

}
