<?php
return array(

    /* 数据库设置 */
    'DB_TYPE'               =>  'mysqli',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'nefuer_n',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'GT338570',          // 密码
    'DB_PORT'               =>  '3306',        // 端口

    /* SESSION设置 */
    'SESSION_AUTO_START'    =>  false,    // 是否自动开启Session

    'URL_PARAMS_BIND_TYPE'  =>  1, // URL变量绑定的类型 0 按变量名绑定 1 按变量顺序绑定
    // 'URL_ROUTER_ON'         =>  false,   // 是否开启URL路由
    // 'URL_ROUTE_RULES'       =>  array(), // 默认路由规则 针对模块
);