<?php
namespace V1\Controller;
use V1\Common\ApiController;
class ScoreController extends ApiController {

    public function index()
    {
        $nefuer = \Nefu::getInstance($this->payload['user']['account'], $this->payload['user']['password'], $this->payload['user']['cookie']);
        $score = $nefuer->userScore();
        $this->payload['user']['cookie'] = $nefuer->getCookie();
    	$this->apiSuccess($score);
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
    				$template_id = '';
    				$url = '';
                    $data = array(
                        "first" => array(
                            "value" => "",
                            "color" => ""
                        ),
                        "keyword1" => array(
                            "value" => '',
                            "color" => "#173177"
                        ),
                        "keyword2" =>  array(
                            "value" => date('Y-m-d H:i:s', time()),
                            "color" => "#173177"
                        ),
                        "remark" => array(
                            "value" => "",
                            "color" => ""
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