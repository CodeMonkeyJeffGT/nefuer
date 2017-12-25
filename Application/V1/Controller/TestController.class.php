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
        	$term[$i . '-2'] = $nefuer->userTest($i . '-' . ($i + 1) . '-2');
	        if(FALSE === $term[$i . '-2'])
	        {
	            $this->apiError('获取考试失败，请重新登录或查看教务系统是否可用');
	        }
        }
        $test = $nefuer->userTest();


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

}
