<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/29
 * Time: 18:45
 */
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088421604418532';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= 'MIICXgIBAAKBgQCisY+6qsXhh625rH0sTVYDfcgpbBMMQNI/0QQ09a2wve2up6uWAyydh7L86NF9A9f3UOek2KfFkaRc+igjl8eaR74GWvfB2uEMAhy5o+lLPBHGI2qz4AoHrCOx1pIw6nTTyMVlk/MKXNfaZ8H76aqSN1ZFegHf0ER/gqBPmJVAPwIDAQABAoGALQbreoQtBAAjCpI8inhU5951+VDZ7Lg7+EGG4olkKthF4eKx0HDMdkTKOsjMwcbAjMgtdCgqNrnaPYlWdpNZQF6qMAsXjvslzLVzXgjlYAuAQW4BOjK6L1m+LwdQ6wbap0VL5aE1xivBm3Fe4Qio0nW+G18PzVcDYVCmWI/T/sECQQDVfsD74IJ+HYqBGYFcyD2g6e/HpoJUB4IqoHAki/fxIeS0GyJHpFIV3NE3aqAkiYNWAot8cMprKaHNHuBogkVPAkEAwxWYrrpKpMMrTl7TudPy+YSGabrgHBSO4O4WvUm9ObdzwM+1WhdYXXQIVARVicSCxsjipo0nekQLzhGbIul6EQJBAIlEQx5vPleJ9NiGppcaJA3G+6U5WMhgP3/awd+totAGA78NRyAa9bAa1uWzh52WULxHTJnJB0yZau+wb4aiY58CQQCDG+eSPndeBiD7ubVX5X8dfJiNRF/L33Eq8DhuHLnEqWttAOtj0d68Z/gU5xjJzz0I9geFYcZPYJ4Cb0ixDPLRAkEAzRSBdsq91AqS9wKo4keFsnsJXhi/niBo4o1Ab+A/lEUWvYFzpo3hppgCKHYAWSSD6uyVy8u3azM6ibbvLIHfhQ==';

//支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCisY+6qsXhh625rH0sTVYDfcgpbBMMQNI/0QQ09a2wve2up6uWAyydh7L86NF9A9f3UOek2KfFkaRc+igjl8eaR74GWvfB2uEMAhy5o+lLPBHGI2qz4AoHrCOx1pIw6nTTyMVlk/MKXNfaZ8H76aqSN1ZFegHf0ER/gqBPmJVAPwIDAQAB';

// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = "http://商户网址/create_direct_pay_by_user-PHP-UTF-8/notify_url.php";

// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://商户网址/create_direct_pay_by_user-PHP-UTF-8/return_url.php";

//签名方式
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

// 支付类型 ，无需修改
$alipay_config['payment_type'] = "1";

// 产品类型，无需修改
$alipay_config['service'] = "create_direct_pay_by_user";

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//↓↓↓↓↓↓↓↓↓↓ 请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

// 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
$alipay_config['anti_phishing_key'] = "";

// 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
$alipay_config['exter_invoke_ip'] = "";

//↑↑↑↑↑↑↑↑↑↑请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑