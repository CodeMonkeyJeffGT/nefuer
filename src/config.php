<?php

return array(

    'baseUri' => 'http://jwcnew.nefu.edu.cn/',

    'login' => array(
        'uri' => 'dblydx_jsxsd/xk/LoginToXk',
        'method' => 'GET',
        'headers' => array(

        ),
        'params' => array(
            array(
                'name' => 'method',
                'type' => 'string',
                'default' => 'skybinfw',
                'doc' => '请求方式',
            ),
            array(
                'name' => 'USERNAME',
                'type' => 'int',
                'doc' => '学号',
            ),
            array(
                'name' => 'PASSWORD',
                'type' => 'string',
                'doc' => '密码',
            ),
        ),
        'doc' => '登录接口',
    ),
    'stuInfo' => array(
        'uri' => 'dblydx_jsxsd/ds/zwjs.do',
        'method' => 'POST',
        'headers' => array(

        ),
        'params' => array(

        ),
        'doc' => '学生信息',
    ),
    'stuScoreAll' => array(
        'uri' => 'dblydx_jsxsd/xsxj/doQueryXxwcqkKcsx.do',
        'method' => 'POST',
        'headers' => array(

        ),
        'params' => array(

        ),
        'doc' => '最终成绩',
    ),
    'stuScoreItem' => array(
        'uri' => 'dblydx_jsxsd/kscj/cjcx_list',
        'method' => 'POST',
        'headers' => array(

        ),
        'params' => array(
            array(
                'name' => 'xsfs',
                'type' => 'string',
                'default' => 'item',
                'doc' => '显示方式，默认为项目成绩（阶段）',
            ),
            array(
                'name' => 'isfx',
                'type' => 'int',
                'default' => 0,
                'doc' => '是否辅修',
            ),
        ),
        'doc' => '阶段考试成绩',
    ),
    
);