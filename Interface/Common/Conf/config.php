<?php
return array(

    //数据库设置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => 'rdsm614a6mt50f295feoo.mysql.rds.aliyuncs.com', // 服务器地址
    'DB_NAME'   => 'haoyuezhibo', // 数据库名
    'DB_USER'   => 'hyzbnew', // 用户名
    'DB_PWD'    => 'dnsPNg735295WK', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'wht_', // 数据库表前缀

    /* SESSION设置 */
    'SESSION_AUTO_START' => false, // 是否自动开启Session
    'SESSION_OPTIONS'    => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'       => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'     => 'tp', // session 前缀

    'DEFAULT_MODULE'     => 'Api', // 默认模块
    'DEFAULT_CONTROLLER' =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'      =>  'index', // 默认操作名称

    /*URL_MODEL*/
    'URL_MODEL'          => 1,
    'URL_HTML_SUFFIX'   => '',
    'URL_PATHINFO_DEPR' => '/',
    'URL_ROUTER_ON'      => true,

    /*加密方式*/
    'DATA_CRYPT_TYPE'  => 'DES',

    /*接口域名*/
    'API_SITE_PREFIX'  => '',

    /*redis 站点名称*/
    "SITENAME" =>"wht1",

    /*默认头像文件路径*/
    'HAND_IMG_PATH'   =>  '/Public/pic_hand_img.png',

    /*加密KEY*/
    'PASS_KEY'    => 'IAMYOURFATHER',

    //配置七牛直播云密钥
    define('ACCESS_KEY', 'GD67Wp4OZAETRXUh_LGIz5IR6Ckt6eJ_mXSPF7my'),
    define('SECRET_KEY', '-MyEsadQUZScacORWljXPWmNNsQdURefxl4bPS5l'),
    //配置直播空间名称：必需提前存在，也就是在直播云官网上提前创建好直播空间
    define('HUB', '9dcj1')
);