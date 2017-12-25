<?php
namespace V1\Controller;
use V1\Common\ApiController;
class ScoreController extends ApiController {

    public function index()
    {
        $nefuer = \Nefu::getInstance($this->payload['user']['account'], $this->payload['user']['password'], $this->payload['user']['cookie']);
        $score = $nefuer->userScore();
        if(FALSE === $score)
        {
            $this->apiError('获取成绩失败，请重新登录或查看教务系统是否可用');
        }

        $term = array(
            'score' => array(),
            'count' => array(),
            'avg'   => array()
        );

        $scoreAllAdd = array();
        for($i = 0, $iloop = count($score['item']); $i < $iloop; $i++)
        {
            $index = -1;
            for($j = 0,$jloop = count($score['all']); $j < $jloop; $j++)
            {
                if($score['item'][$i]['code'] === $score['all'][$j]['code'] && $score['item'][$i]['term'] === $score['all'][$j]['term'])
                {
                    $index = $j;
                    break;
                }
            }
            if($index === -1)
            {
                $scoreAllAdd[] = array(
                    'term'      => $score['item'][$i]['term'],
                    'name'      => $score['item'][$i]['name'],
                    'score'     => '（阶段）',
                    'type'      => $score['item'][$i]['type'],
                    'num'       => $score['item'][$i]['num'],
                    'attribute' => $score['item'][$i]['attribute'],
                    'children'  => array()
                );
                if('' != $score['item'][$i]['s_1'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '阶段1',
                        'score' => $score['item'][$i]['s_1']
                    );
                if('' != $score['item'][$i]['s_2'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '阶段2',
                        'score' => $score['item'][$i]['s_2']
                    );
                if('' != $score['item'][$i]['s_3'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '阶段3',
                        'score' => $score['item'][$i]['s_3']
                    );
                if('' != $score['item'][$i]['s_4'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '阶段4',
                        'score' => $score['item'][$i]['s_4']
                    );
                if('' != $score['item'][$i]['s_5'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '阶段5',
                        'score' => $score['item'][$i]['s_5']
                    );
                if('' != $score['item'][$i]['s_common'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '平时成绩',
                        'score' => $score['item'][$i]['s_common']
                    );
                if('' != $score['item'][$i]['s_middle'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '期中',
                        'score' => $score['item'][$i]['s_middle']
                    );
                if('' != $score['item'][$i]['s_final'])
                    $scoreAllAdd[count($scoreAllAdd) - 1]['children'][] = array(
                        'name' => '期末',
                        'score' => $score['item'][$i]['s_final']
                    );
            }
            else
            {
                if( ! isset($score['all'][$index]['children']))
                    $score['all'][$index]['children'] = array();

                if('' != $score['item'][$i]['s_1'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '阶段1',
                        'score' => $score['item'][$i]['s_1']
                    );
                if('' != $score['item'][$i]['s_2'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '阶段2',
                        'score' => $score['item'][$i]['s_2']
                    );
                if('' != $score['item'][$i]['s_3'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '阶段3',
                        'score' => $score['item'][$i]['s_3']
                    );
                if('' != $score['item'][$i]['s_4'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '阶段4',
                        'score' => $score['item'][$i]['s_4']
                    );
                if('' != $score['item'][$i]['s_5'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '阶段5',
                        'score' => $score['item'][$i]['s_5']
                    );
                if('' != $score['item'][$i]['s_common'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '平时成绩',
                        'score' => $score['item'][$i]['s_common']
                    );
                if('' != $score['item'][$i]['s_middle'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '期中',
                        'score' => $score['item'][$i]['s_middle']
                    );
                if('' != $score['item'][$i]['s_final'])
                    $score['all'][$index]['children'][] = array(
                        'name' => '期末',
                        'score' => $score['item'][$i]['s_final']
                    );
            }
        }
        $score = array_merge($score['all'], array_reverse($scoreAllAdd));

        for($i = count($score) - 1; $i >= 0; $i--)
        {
            $score[$i]['term'] = substr($score[$i]['term'], 0, 4) . '-' . $score[$i]['term'][10];
            if( ! isset($term['score'][$score[$i]['term']]))
            {
                $term['score'][$score[$i]['term']] = array();
                $term['count'][$score[$i]['term']] = array(
                    'allNum'    => 0,
                    'allScore'  => 0,
                    'baseNum'   => 0,
                    'baseScore' => 0
                );
            }

            if(is_numeric($score[$i]['score']))
            {
                if('公选' !== $score[$i]['attribute'])
                {
                    $term['count'][$score[$i]['term']]['baseNum'] += $score[$i]['num'];
                    $term['count'][$score[$i]['term']]['baseScore'] += $score[$i]['score'] * $score[$i]['num'];
                }
                $term['count'][$score[$i]['term']]['allNum'] += $score[$i]['num'];
                $term['count'][$score[$i]['term']]['allScore'] += $score[$i]['score'] * $score[$i]['num'];
            }

	    $term['score'][$score[$i]['term']][] = $score[$i];
	    /**array(
                'name'     => $score[$i]['name'],
                'score'    => $score[$i]['score'],
                'num'      => $score[$i]['num'],
                'children' => $score[$i]['children'],
	    );*/
        }

        foreach ($term['count'] as $key => $value)
        {
            $tmpTerm = substr($key, 0, 4);
            $tmpKey = $key[5];
            if( ! isset($term['avg'][$tmpTerm]))
                $term['avg'][$tmpTerm] = array();
            $term['avg'][$tmpTerm][$tmpKey] = array(
                'all'  => $term['count'][$key]['allNum'] === 0 ? 0 : number_format($term['count'][$key]['allScore'] / $term['count'][$key]['allNum'], 2),
                'base'  => $term['count'][$key]['baseNum'] === 0 ? 0 : number_format($term['count'][$key]['baseScore'] / $term['count'][$key]['baseNum'], 2)
            );
            if($term['avg'][$tmpTerm][$tmpKey]['all'] === 0 && $term['avg'][$tmpTerm][$tmpKey]['base'] === 0)
                unset($term['avg'][$tmpTerm][$tmpKey]);
        }

        foreach ($term['avg'] as $key => $value)
        {
            if(isset($term['avg'][$key][1]) && isset($term['avg'][$key][2]))
            {
                $term['avg'][$key]['all'] = array(
                    'all' => number_format(($term['count'][$key . '-1']['allScore'] + $term['count'][$key . '-2']['allScore']) / ($term['count'][$key . '-1']['allNum'] + $term['count'][$key . '-2']['allNum']), 2),
                    'base' => number_format(($term['count'][$key . '-1']['baseScore'] + $term['count'][$key . '-2']['baseScore']) / ($term['count'][$key . '-1']['baseNum'] + $term['count'][$key . '-2']['baseNum']), 2)
                );
            }
            if(empty($term['avg'][$key]))
                unset($term['avg'][$key]);
        }

        unset($term['count']);

        $this->payload['user']['cookie'] = $nefuer->getCookie();
    	$this->apiSuccess($term);
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
