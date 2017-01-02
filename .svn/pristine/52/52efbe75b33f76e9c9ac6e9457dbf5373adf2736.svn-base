<?php
/**
 * 权限验证
 * @param rule string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param uid  int           认证用户的id
 * @param string mode        执行check的模式
 * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function authCheck($rule,$uid,$mode='url', $relation='or'){
    //超级管理员跳过验证
    if(in_array($uid, C('ADMINISTRATOR'))){
        return true;
    }else{
        $auth=new \Think\Auth();
        //设置的是一个用户对应一个角色组,所以直接取值.如果是对应多个角色组的话,需另外处理
        return $auth->check($rule,$uid,1,$mode,$relation)?true:false;
    }
}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}


//转换大小
function ChangeSize($size){
    if($size<1024)
    {
        $str=$size." B";
    }
    elseif($size<1024*1024)
    {
        $str=round($size/1024,2)." KB";
    }
    elseif($size<1024*1024*1024)
    {
        $str=round($size/(1024*1024),2)." MB";
    }
    else
    {
        $str=round($size/(1024*1024*1024),2)." GB";
    }
    return $str;
}
/**
 * 截取字数 by chen
 * @param $string 字符串
 * @param $length 截取字数
 * @param $dot 后缀
 * @return 截取后字符串
 * 编码默认为UTF-8
 */
function info_sub ($string,$length='140',$dot='...'){
    $str_len=strlen($string);
    if($str_len<=$length){return $string;}
    $string = str_replace(array('&nbsp;','&amp;','&quot;','&lt;','&gt;','&#039;'), array(' ','&','"','<','>',"'"), $string);
    $n = $tn = $noc = 0;
    while($n < $str_len) {
        $t = ord($string[$n]);
        if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1; $n++; $noc++;
        } elseif(194 <= $t && $t <= 223) {
            $tn = 2; $n += 2; $noc += 2;
        } elseif(224 <= $t && $t < 239) {
            $tn = 3; $n += 3; $noc += 2;
        } elseif(240 <= $t && $t <= 247) {
            $tn = 4; $n += 4; $noc += 2;
        } elseif(248 <= $t && $t <= 251) {
            $tn = 5; $n += 5; $noc += 2;
        } elseif($t == 252 || $t == 253) {
            $tn = 6; $n += 6; $noc += 2;
        } else {$n++;}
        if($noc >= $length) {break;}
    }
    if($noc > $length) {$n -= $tn;}
    $str_cut = substr($string, 0, $n);
    $str_cut = str_replace(array('&','"','<','>',"'"), array('&amp;','&quot;','&lt;','&gt;','&#039;'), $str_cut);
    return $str_cut.$dot;
}

/**
 * 浮点数舍去指定位数小数点部分。全舍不入
 * @param $n float浮点值
 * @param $len 截取长度字数
 * @return string 截取后的值
 */
function sub_float($n,$len)
{
    stripos($n, '.') && $n= (float)substr($n,0,stripos($n, '.')+$len+1);
    return $n;
}

/**
 * 系统缓存缓存管理
 * @param mixed $name 缓存名称
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function cache($name, $value = '', $options = null) {
    static $cache = '';
    if (empty($cache)) {
        $cache = \Think\Cache::getInstance();
    }
    // 获取缓存
    if ('' === $value) {
        if (false !== strpos($name, '.')) {
            $vars = explode('.', $name);
            $data = $cache->get($vars[0]);
            return is_array($data) ? $data[$vars[1]] : $data;
        } else {
            return $cache->get($name);
        }
    } elseif (is_null($value)) {//删除缓存
        return $cache->remove($name);
    } else {//缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : NULL;
        } else {
            $expire = is_numeric($options) ? $options : NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 生成随机字符串
 * @param int       $length  要生成的随机字符串长度
 * @param string    $type    随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
 * @return string
 */
function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } elseif ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[mt_rand(0, $count)];
    }
    return $code;
}

/*
 * 产生随机字符
 * $length  int 生成字符传的长度
 * $numeric  int  , = 0 随机数是大小写字符+数字 , = 1 则为纯数字
*/
function randCodeM($length, $numeric = 0)
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
 * @return String
 */
function myEncode($string = '')
{
    if(empty($string)) return '';
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split(C('PASS_KEY')) as $key => $value)
        $key < $strCount && $strArr[$key] .= $value;
    return str_replace(array('+','/'), array('-','_'), join('', $strArr));
}

/**
 * 简单对称加密算法之解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @return String
 */
function myDecode($string = '')
{
    if(empty($string)) return '';
    $strArr = str_split(str_replace(array('-','_'),array('+','/'),  $string), 2);
    $strCount = count($strArr);
    foreach (str_split(C('PASS_KEY')) as $key => $value)
        $key <= $strCount && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
}

/**
 * 用户数据 DES加密
 * @param String $str 需要加密的字串
 * @param String $skey 加密EKY
 * @return String
 */
function myDes_encode($str, $key)
{
    $va = \Think\Crypt\Driver\Des::encrypt($str, $key.C('PASS_KEY'));
    $va = base64_encode($va);
    return str_replace(array('+','/'), array('-','_'), $va);
}

/**
 * 用户数据 DES解密
 * @param String $str 需要解密的字串
 * @param String $skey 解密KEY
 * @return String
 */
function myDes_decode($str, $key)
{
    $str = str_replace(array('-','_'), array('+','/'), $str);
    $str = base64_decode($str);
    $va = \Think\Crypt\Driver\Des::decrypt($str, $key.C('PASS_KEY'));
    return trim($va);
}
?>