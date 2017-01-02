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
    'SESSION_AUTO_START' => true, // 是否自动开启Session
    'SESSION_OPTIONS'    => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'       => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'     => '', // session 前缀

    'DEFAULT_MODULE'     => 'Home', // 默认模块
    'DEFAULT_CONTROLLER' =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'      =>  'index', // 默认操作名称

    //支付宝参数配置
    'ALI_CONFIG' =>array(
        //接口名称
        "service"       =>"create_direct_pay_by_user",
        //卖家签约账号ID
        "partner"       => "2088421604418532",
        //卖家账号ID
        "seller_id"  => "2088421604418532",
        //支付方式，必需为1
        "payment_type"	=>"1",
        //异步通知返回路径
        "notify_url"	=> "http://api.9dushuju.com/index.php/Home/AliPay/notify_url",
        //同步通知返回路径
        "return_url"	=> "http://api.9dushuju.com/index.php/Home/AliPay/return_url",
        //防钓鱼时间戳
        "anti_phishing_key"=>time(),
        //用户ip
        "exter_invoke_ip"=>"",
        //编码类型
        "_input_charset"	=> "UTF-8",
        //签名类型
        "sign_type"=>"RSA",
        //支付宝私钥
        "private_key"=>"MIICXgIBAAKBgQCisY+6qsXhh625rH0sTVYDfcgpbBMMQNI/0QQ09a2wve2up6uWAyydh7L86NF9A9f3UOek2KfFkaRc+igjl8eaR74GWvfB2uEMAhy5o+lLPBHGI2qz4AoHrCOx1pIw6nTTyMVlk/MKXNfaZ8H76aqSN1ZFegHf/gqBPmJVAPwIDAQABAoGALQbreoQtBAAjCpI8inhU5951+VDZ7Lg7+EGG4olkKthF4eKx0HDMdkTKOsjMwcbAjMgtdCgqNrnaPYlWdpNZQF6qMAsXjvslzLVzXgjlYAuAQW4BOjK6L1m+LwdQ6wbap0VL5aE1xivBm3Fe4Qio0nW+G18PzVcDYVCmWI/T/sECQQDVfsD74IJ+HYqBGYFcyD2g6e/HpoJUB4IqoHAki/fxIeS0GyJHpFIV3NE3aqAkiYNWAot8cMprKaHNHuBogkVPAkEAwxWYrrpKpMMrTl7TudPy+YSGabrgHBSO4O4WvUm9ObdzwM+1WhdYXXQIVARVicSCxsjipo0nekQLzhGbIul6EQJBAIlEQx5vPleJ9NiGppcaJA3G+6U5WMhgP3/awd+totAGA78NRyAa9bAa1uWzh52WULxHTJnJB0yZau+wb4aiY58CQQCDG+eSPndeBiD7ubVX5X8dfJiNRF/L33Eq8DhuHLnEqWttAOtj0d68Z/gU5xjJzz0I9geFYcZPYJ4Cb0ixDPLRAkEAzRSBdsq91AqS9wKo4keFsnsJXhi/niBo4o1Ab+A/lEUWvYFzpo3hppgCKHYAWSSD6uyVy8u3azM6ibbvLIHfhQ==",
        //支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
        'alipay_public_key'=> 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB'
    ),

);