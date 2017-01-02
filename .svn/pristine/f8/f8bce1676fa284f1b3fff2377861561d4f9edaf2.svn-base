<?php
/**
 * 微信公共类
 * @author CYH
 */
namespace Common\Controller;
use Think\Controller;

class BaseController extends Controller
{

    /**
     * 发送短信
     * @param string $phone 电话号码
     * @param msg 消息内容
     * @return string 返回结果 0-成功 2014-7-8
     */
    protected function SendSms($phone = '', $msg = '')
    {
//        $cpid = C('SMS_CPID');
//        $cppwd = md5(C('SMS_CPPWD'));
//        //$httpstr = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account={$cpid}&password={$cppwd}&mobile={$phone}&content={$msg}";
////        @file_put_contents('sms.log',date('Y-m-d H:i:s',time()).'  '.$httpstr.PHP_EOL,FILE_APPEND);
////        @file_put_contents('phone.log',$phone.';',FILE_APPEND);
//        //避免恶意短信
//        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
//        $post_data = "account=".$cpid."&password=".$cppwd."&mobile=".$phone."&content=".rawurlencode($msg);
//        $gets = $this->PostSM($post_data, $target);
//        //$result = @file_get_contents($httpstr);
//        return $gets;
    }

    protected function PostSM($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    /*
     * 产生随机字符
     * $length  int 生成字符传的长度
     * $numeric  int  , = 0 随机数是大小写字符+数字 , = 1 则为纯数字
    */
    function random($length, $numeric = 0)
    {
        $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 简单对称加密算法之加密
     * @param String $string 需要加密的字串
     * @param String $skey 加密EKY
     * @author David
     * @return String
     */
    function encode($string = '')
    {
        if(empty($string)) return '';
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split(C('PASS_KEY')) as $key => $value)
            $key < $strCount && $strArr[$key] .= $value;
        return str_replace(explode(",", C('PASS_REPLACE_KEY')), explode(",", C('PASS_REPLACE_VALUE')), join('', $strArr));
    }

    /**
     * 简单对称加密算法之解密
     * @param String $string 需要解密的字串
     * @param String $skey 解密KEY
     * @author David
     * @return String
     */
    function decode($string = '')
    {
        if(empty($string)) return '';
        $strArr = str_split(str_replace(explode(",", C('PASS_REPLACE_VALUE')), explode(",", C('PASS_REPLACE_KEY')), $string), 2);
        $strCount = count($strArr);
        foreach (str_split(C('PASS_KEY')) as $key => $value)
            $key <= $strCount && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        return base64_decode(join('', $strArr));
    }

    /**
     * @param $lng1 第一个点的精度
     * @param $lat1 第一个点的纬度
     * @param $lng2 第二个点的精度
     * @param $lat2 第二个点的纬度
     * @return int 两个点之间的距离 米
     */
    protected function getdistance($lng1, $lat1, $lng2, $lat2)
    {
        $PI=3.1415926535898;
        $EARTH_RADIUS=6378.137;
        $radLat1 = $lat1 * ($PI / 180);
        $radLat2 = $lat2 * ($PI / 180);

        $a = $radLat1 - $radLat2;
        $b = ($lng1 * ($PI / 180)) - ($lng2 * ($PI / 180));

        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return $s;

        //百度坐标
//        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
//        $radLat2 = deg2rad($lat2);
//        $radLng1 = deg2rad($lng1);
//        $radLng2 = deg2rad($lng2);
//        $a = $radLat1 - $radLat2;
//        $b = $radLng1 - $radLng2;
//        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) *6378137;
//        return $s;
    }

    //全站初始化
    public function _initialize()
    {
        //根据数据库动态配置
        $C = M('config');
        $list = $C->field(true)->select();
        foreach ($list as $key => $value) {
            C($value['config_field'],$value['config_value']);
        }
    }
}

?>