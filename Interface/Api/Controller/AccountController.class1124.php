<?php
/**
 * 帐号公开接口API
 * 1.登录、
 * 2.注册
 * 3.忘记密码
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;


class AccountController extends ApiController
{
    public function _initialize()
    {
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 2016-08-15 用户登录 sjy
     * Api/Account/login/username/122/userpwd/1
     * */
    public function login()
    {
        /* echo "dsfc";
         exit();*/
        $rm = I("post.");
        $user = $rm['username'];
        $pwd = $rm['userpwd'];
		//链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        if (!empty($user) && !empty($pwd)) {
            $where['phone'] = $user;
            $where['isdelete'] = 0;
            $owner = M('userinfos');
            $resn = $owner->where($where)->field("userid,userpwd")->find();
            if (!empty($resn) && $resn["userpwd"] == $pwd) {
                $userid = $resn["userid"];
                $nick = '用户' . randCodeM(4);
                $time = uniqid();
                $token = md5($nick . $time . $user);
                //保存到redis里
                $sitename = C('SITENAME');
                //\Predis\Autoloader::register();
                //$redis = new \Predis\Client();
                $redis->hmset($sitename . '-token-' . $token, array('uid' => $userid, 'type' => 2));//添加到redis缓存中 本站用户是2
                $redis->expire($sitename . '-token-' . $token, 604800); //设置过期时间 86400  1440分钟  168小时7条

                $accesstoken = $sitename . '-token-' . $token;
                //获取过期时间
                $expiresdate = $redis->ttl($sitename . '-token-' . $token);

                //保存到accesstoken sql数据库
                $where2['atoken'] = $sitename.'-token-'.$token;
                $where2['atype'] = 2;
                $where2['userid'] = $userid;
                $where2['keyexpire']=168;
                $where2['expiretime']=date("Y-m-d h:i:s");
                M('accesstoken')->data($where2)->add();

                //获取用户个人信息
                $uwhere['phone'] = $user;
                $us = M('userinfos')->where($uwhere)->field('username,email,nickname,headimage,phone,sharecode,gradeid,expervalue,ninemoney,balance,signature,qq,weixin,sina,roomid,usertype,gender,job,emcid,emcpwd')->find();
                $result = array(
                    'token' => $accesstoken,
                    'type' => 2,
                    'expires' => $expiresdate,
                    'username' => "{$us['username']}",
                    'email' => "{$us['email']}",
                    'nickname' =>"{$us['nickname']}",
                    'headimage' =>"{$us['headimage']}" ,
                    'phone' => $us['phone'],
                    'sharecode' => $us['sharecode'],
                    'gradeid' => $us['gradeid'],
                    'expervalue' => $us['expervalue'],
                    'ninemoney' => $us['ninemoney'],
                    'balance' => $us['balance'],
                    'signature' => "{$us['signature']}",
                    'qq' =>"{$us['qq']}" ,
                    'sina' => "{$us['sina']}",
                    'weixin' =>"{$us['weixin']}" ,
                    'roomcode' =>"{$us['roomcode']}" ,
                    'usertype' => $us['usertype'],
                    'gender' => $us['gender'],
                    'roomid' => $us['roomid'],
                    'job' =>"{$us['job']}" ,
                    'emcid' => $us['emcid'],
                    'emcpwd' => $us['emcpwd']
                );
                if (!empty($accesstoken)) {
                    $this->myApiPrint('success', 200, $result);
                } else {
                    $this->myApiPrint('don\'t find ', 404);
                }
            } else {
                $this->myApiPrint('error', -0201);
            }
        }
    }
    
    /*
     * 生成用户名
     */
    private  function generate_username( $length = 11 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_ []';
        $username = '';
        for ( $i = 0; $i < $length; $i++ ){
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $username .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $username .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $model = D("userinfos");
        $wh = array();
        $wh["username"] = $username;
        if($model->where($wh)->count() > 0){ // 如果有就重新生成
            $this->generate_username($length);
        }else{
            return $username;
        }
        
    }

    /*2016-08-16 用户注册 sjy
     * Api/Account/register/username/{username}/email/{email}/userpwd/{userpwd}/nickname/{nickname}/fromsource/{fromsource}/sourcecode/{sourcecode}/headimage/{headimage}/phone/{phone}/qq/{qq}/weixin/{weixin}/sina/{sina}
     * */
    public function register()
    {
        $rm = I("post.");
        //判断是否为空
        if (!empty($rm["username"]) && !empty($rm["userpwd"])) {
            
            $userinfos_model = D('userinfos');
            $where = array();
            $where["username"] = $rm["username"];//获取用户名
            $where["phone"] = $rm["username"];
            $where["nickname"] = $rm["nickname"];
            $where["_logic"] = "OR";
            $count = $userinfos_model->where($where)->count();
            
            
            if ($count > 0) {
                $this->myApiPrint("Account already exists", 300);
                exit;
            }
            
            //添加帐号
            $where["emcid"]=randCode(3).uniqid();
            $where["emcpwd"]=md5('123');
            $where["fromsource"] = $rm["fromsource"];//获取用户来源 1:web 2:android 3:ios
            $where["phone"] = $rm["username"];//获取用户手机号
            $where["sharecode"] = uniqid();//获取邀请码
            $where["nickname"] = $rm["username"];//获取用户昵称
            $where["gradeid"] =1;//获取用户等级，默认1级为新手
            $where["headimage"]="http://jdcjapp.oss-cn-hangzhou.aliyuncs.com/headerimage/headimage1.png";
            $where["userpwd"] = $rm["userpwd"];
            $userid = $userinfos_model->data($where)->add();
            if (!$userid) {
                $this->myApiPrint('register error', 300);
                exit;
            }
            
            //注册信息到环信上
            vendor('emchat.Easemob');//调用环信的class类进行注册用户
            $options['client_id']='YXA6Nt1u0HosEeaqHH3gPQ64JQ';
            $options['client_secret']='YXA6zcifjTMaxc6_nIme-vJyanwhX6o';
            $options['org_name']='haoontech888';
            $options['app_name']='jiuducaijing';
            $h=new \Easemob($options);
            //重新调去token()方法
            $aa1 = $h->getToken();
            $rresult = "Authorization:Bearer ".$aa1['access_token'];
            $arrResult = $h->createUser($where["emcid"],$where["emcpwd"],$rresult);
            //注册或删除失败时，uuid为空。根据uuid是否有值 判断操作是否成功
            $result = $arrResult['entities'][0]['uuid']?$arrResult['entities'][0]['uuid']:"";
            if(empty($result)){
                //注册失败，重新再注册，再失败则修改userinfos表的信息
                $aa = $h->createUser($where["emcid"],$where["emcpwd"],$rresult);
                $result3 = $aa['entities'][0]['uuid']?$aa['entities'][0]['uuid']:"";
                if(empty($result3)){
                    //先修改用户信息，之后进行循环后台没有注册环信的用户
                    $userinfos_model->where(array("userid"=>$userid))->save(array("emcid"=>'',"emcpwd"=>''));
                }
            }
            $nick = '用户' . randCodeM(4);
            $time = uniqid();
            $token = md5($nick . $time . $userid);
            //保存到redis里
            $sitename = C('SITENAME');
            //链接redis,每次需要的时候重新链接 做初始化
            $redis = $this->connectredis();
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
            $redis->hmset($sitename . '-token-' . $token, array('uid' => $userid, 'type' => 2));//添加到redis缓存中 本站用户是2
            $redis->expire($sitename . '-token-' . $token, 604800); //设置过期时间 86400  1440分钟  168小时7条
            $accesstoken = $sitename . '-token-' . $token;
            //获取过期时间
            $expiresdate = $redis->ttl($sitename . '-token-' . $token);

            //保存到accesstoken sql数据库
            $where1['atoken'] = $sitename.'-token-'.$token;
            $where1['atype'] = 2;
            $where1['userid'] = $userid;
            $where1['keyexpire']=168;
            $where1['expiretime']=date("Y-m-d h:i:s");
            M('accesstoken')->data($where1)->add();

            $uwhere["userid"] = $userid;
            
           
            
            $us=M('userinfos')
                    ->where($uwhere)
                    ->field('username,email,nickname,headimage,phone,sharecode,gradeid,expervalue,ninemoney,balance,signature,qq,weixin,sina,roomid,usertype,gender,job,emcid,emcpwd')
                    ->find();
            
            $roomdata["roomid"]=$us["roomid"];
            $roomcode=M('rooms')->where($roomdata)->field('roomcode')->find();
            
            $result = array(
                'token' => $accesstoken,
                'type' => 2,
                'expires' => $expiresdate ? $expiresdate : '',
                'username' => $us['username'],
                'email' => $us['email'] ? $us['email'] : '',
                'nickname' => $us['nickname'] ? $us['nickname'] : '',
                'headimage' => $us['headimage'] ? $us['headimage'] : '',
                'phone' => $us['phone'],
                'sharecode' => $us['sharecode'] ? $us['sharecode'] : '',
                'gradeid' => $us['gradeid'] ? $us['gradeid'] : '',
                'expervalue' => $us['expervalue'] ? $us['expervalue'] : 0,
                'ninemoney' => $us['ninemoney'] ? $us['ninemoney'] : 0,
                'balance' => $us['balance'] ? $us['balance'] : 0,
                'signature' => $us['signature'] ? $us['signature'] : '',
                'qq' => $us['qq'] ? $us['qq'] : '',
                'sina' => $us['sina'] ? $us['sina'] : '',
                'weixin' => $us['weixin'] ? $us['weixin'] : '',
                'roomcode' => $roomcode['roomcode'] ? $roomcode['roomcode'] : '',
                'usertype' => $us['usertype'] ? $us['usertype'] : 2,
                'gender' => $us['gender'] ? $us['gender'] : 3,
                'roomid' => $us['roomid'] ? $us['roomid'] : 0,
                'job' => $us['job'] ? $us['job'] : '',
                'emcid' => $us['emcid'] ? $us['emcid'] : '',
                'emcpwd' => $us['emcpwd'] ? $us['emcpwd'] : ''
            );
            
            
            $this->myApiPrint("success", 200, $result);
        } else {
            $this->myApiPrint("account or password is empty", 300);
        }
    }

    /*
    * 第三方登录 2016-09-05 sjy
    * Api/Account/otherlogin/openid/{openid}/headimage/{第三方头像}/nickname/｛第三方昵称｝/loginflag/｛第三方来源｝/sex/{性别}
    *
    * */
    public function otherlogin()
    {
        $rs = I('get.');//获取用户的
        $openid = $rs["openid"];
        $headimage = $rs["headimage"];//获取第三方头像
        $nick = $rs["nickname"];//获取用户第三方昵称
        $loginflag = $rs["loginflag"];//获取登录旗帜
        $sex = $rs["gender"];//获取用户性别


        //判断穿传过来的信息是否为空
        if (empty($openid)) {
            $this->myApiPrint('openid not exist', 300);
        }
        if (empty($headimage)) {
            $this->myApiPrint('headimage not exist', 300);
        }
        if (empty($nick)) {
            $this->myApiPrint('nickname not exist', 300);
        }
        if (empty($loginflag)) {
            $this->myApiPrint('loginflag not exist', 300);
        }
        if (empty($loginflag)) {
            $sex = 1;
        }


        $time = uniqid();
        $token = md5($nick . $time . $openid);//第三方登录的accesstoken

        $sitename = C('SITENAME');//获取配置信息
        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
        $redis->hmset($sitename . '-token-' . $token, array('uid' => $openid, 'type' => 3));//添加到redis缓存中 本站用户是2
        $redis->expire($sitename . '-token-' . $token, 259200); //设置过期时间 3天

        $accesstoken = $sitename . '-token-' . $token;//获取第三方token

        $where["appid"] = $openid;
        $appaccount = M('appaccount');

        //查询第三方用户表中是否有用户的openid
        $resn = $appaccount
            ->where($where)
            // ->field('userid')
            ->find();

        $data["emcid"]=randCode(3).uniqid();
        $data["emcpwd"]=md5('123');
        $data["appid"] = $openid;
        $data["apptype"] = $loginflag;
        $data["headimg"] = $headimage;
        $data["nickname"] = $nick;
        $data["sex"] = $sex;

        $expiresdate = $redis->ttl($sitename . '-token-' . $token);//获取过期时间
        if ($resn) {
            if (!empty($resn["userid"])) {
                $where1['userid'] = $resn["userid"];
                $us = M('userinfos')->alias('u')
                    ->join("left join wht_rooms as r on r.roomid = u.roomid")->where($where1)->field('u.username,u.email,u.nickname,u.headimage,u.phone,u.sharecode,u.gradeid,u.expervalue,u.ninemoney,u.balance,u.signature,u.qq,u.weixin,u.sina,r.roomcode,u.usertype,u.gender,u.roomid,u.job')
                    ->find();

                $result = array(
                    'token' => $accesstoken,
                    'type' => 2,
                    'expires' => $expiresdate,
                    'username' => $us['username'],
                    'email' => $us['email'],
                    'nickname' => $us['nickname'],
                    'headimage' => $us['headimage'],
                    'phone' => $us['phone'],
                    'sharecode' => $us['sharecode'],
                    'gradeid' => $us['gradeid'],
                    'expervalue' => $us['expervalue'],
                    'ninemoney' => $us['ninemoney'],
                    'balance' => $us['balance'],
                    'signature' => $us['signature'],
                    'qq' => $us['qq'],
                    'sina' => $us['sina'],
                    'weixin' => $us['weixin'],
                    'roomcode' => $us['roomcode'],
                    'usertype' => $us['usertype'],
                    'gender' => $us['gender'],
                    'roomid' => $us['roomid'],
                    'job' => $us['job']
                );
                $this->myApiPrint("select success", 200, $result);
            } else {

                $updateother = $appaccount
                    ->where($where)
                    ->save($data);

                $result = array(
                    'token' => $accesstoken,
                    'type' => 3,
                    'appid' => $openid,
                    'expires' => $expiresdate,
                    'apptype' => $loginflag,
                    'headimage' => $headimage,
                    'nickname' => $nick,
                    'gender' => $sex
                );
                $this->myApiPrint(" update success", 200, $result);
            }

        } else {
            //第一次三方登录
            //在第三方用户登录表中记录数据

            $insertother = $appaccount->data($data)->add();

            if ($insertother) {
                //注册信息到环信上
                vendor('emchat.Easemob');//调用环信的class类进行注册用户
                $options['client_id']='YXA6Nt1u0HosEeaqHH3gPQ64JQ';
                $options['client_secret']='YXA6zcifjTMaxc6_nIme-vJyanwhX6o';
                $options['org_name']='haoontech888';
                $options['app_name']='jiuducaijing';
                $h=new \Easemob($options);
                if(session("access_token")){
                    //token不为空。判读是当前时间戳和session里的进行对比否过期
                    $dd = session("expirestime");
                    $ee = time();
                    if($ee>$dd){
                        //过期了，需要重新调去token
                        //把过期时间保存到session里
                        //把当前的时间和过期秒数累计存到session里
                        $aa = $h->getToken();
                        if($aa){
                            $stime = time()+$aa['expires_in'];
                            session(array("expirestime"=>$stime,'expire'=>604800));
                            session(array('expire-in'=>$aa['expires_in'],'expire'=>604800));
                            session(array('access_token'=>$aa['access_token'],'expire'=>604800));
                            session(array('application'=>$aa['application'],'expire'=>604800));
                        }
                        $rresult = "Authorization:Bearer ".$aa['access_token'];
                    }else{
                        $rresult = "Authorization:Bearer ".session("access_token");
                    }
                }else{
                    //重新调去token()方法
                    $aa1 = $h->getToken();
                    if($aa1){
                        $stime = time()+$aa1['expires_in'];
                        session(array("expirestime"=>$stime,'expire'=>604800));
                        session(array('expire-in'=>$aa1['expires_in'],'expire'=>604800));
                        session(array('access_token'=>$aa1['access_token'],'expire'=>604800));
                        session(array('application'=>$aa1['application'],'expire'=>604800));
                    }
                    $rresult = "Authorization:Bearer ".$aa1['access_token'];
                }
                $arrResult = $h->createUser($where["emcid"],$where["emcpwd"],$rresult);//
                //注册或删除失败时，uuid为空。根据uuid是否有值 判断操作是否成功
                $result = $arrResult['entities'][0]['uuid']?$arrResult['entities'][0]['uuid']:"";
                if(empty($result)){
                    //注册失败，重新再注册，再失败则修改userinfos表的信息
                    $aa = $h->createUser($where["emcid"],$where["emcpwd"],$rresult);
                    $result3 = $aa['entities'][0]['uuid']?$aa['entities'][0]['uuid']:"";
                    if(empty($result3)){
                        //先修改用户信息，之后进行循环后台没有注册环信的用户
                        $appaccount->where(array("appid"=>$where["appid"]))->save(array("emcid"=>'',"emcpwd"=>''));
                    }
                }
                $result = array(
                    'token' => $accesstoken,
                    'type' => 3,
                    'appid' => $openid,
                    'expires' => $expiresdate,
                    'apptype' => $loginflag,
                    'headimage' => $headimage,
                    'nickname' => $nick,
                    'gender' => $sex
                );

                $this->myApiPrint(" insert success", 200, $result);
            } else {
                $this->myApiPrint('insert fail', 300);
            }


        }

    }


    /*
     * 2016-08-16 忘记密码 根据手机号和新密码去修改用户密码 sjy
     * Api/Account/callpwd/accesstoken/{accesstoken}/phone/{phone}/userpwd/{userpwd}后面跟的值
    */
    public function callpwd()
    {

	//链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('post.accesstoken');//获取用户的token
        $phone = I('post.phone');
        $userpwd = I('post.userpwd');
//        var_dump($atoken);
//        var_dump($phone);
//        var_dump($userpwd);
        //判断token是否存在是否过期 存在就修改密码
        $owner = M('userinfos');


        if ($this->checktoken($atoken)) {
                if (!empty($phone)) {
                    //判断手机号是否存在
                    $count = $owner->where(array("username" => $phone))->field('userid')->find();
                    //                var_dump($count);
                    if ($count <= 0) {
                        $this->myApiPrint('account is not already', 300);
                    }
                    $where["phone"] = $phone;
                } else {
                    $this->myApiPrint('phone is empty', 300);
                }
                if (empty($userpwd)) {
                    $this->myApiPrint('password is empty', 300);
                } else {
                    if (strlen($userpwd) == 32) {
                        $cc = $owner->where(array("username" => $phone))->field('userid,userpwd')->find();
                        if($cc['userpwd']==$userpwd){
                            $this->myApiPrint('Password, and now the original password', 500);
                        }
                        $data["userpwd"] = $userpwd;
                    } else {
                        $this->myApiPrint('Passwords not MD5 encryption', 300);
                    }
                }
                //进行保存
                $resn = $owner
                    ->where($where)
                    ->save($data);
//                    ->setField();
                if (!$resn) {
                    $this->myApiPrint('error', 500);
                } else {
                    $this->myApiPrint('success', 200, $resn);
                }
// else {
//                $this->myApiPrint('accesstoken don\'t find', 404);
//            }
        }else{
			$this->myApiPrint('accesstoken don\'t find', 404);
		}


    }
	
	/*
     * 2016-08-15 app设备资料 ios 
     * http://test.api.9dushuju.com/Api/Account/iosequipment
     * */
    public function androidequipment()
    {
        $rm = I("post.");
        $uuid = $rm['uuid'];
        $smartphones = $rm['smartphones'];
		//链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        $token=$rm['accesstoken'];
        $userflag=$this->checktoken($token);
        if($userflag){
			if (!empty($uuid) && !empty($smartphones)){
				$where['uuid'] = $uuid;
				$where['smartphones'] = $smartphones;
				$where['isdelete'] = 0;
				$count = M('equipmentfile')->data($where)->add();
				if ($count>0) {
					$this->myApiPrint('success', 200);
				} else {
					$this->myApiPrint('error', 404);
				}
			}
		}else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
    }
	
	/*
     * 2016-08-15 app设备资料 android 
     * http://test.api.9dushuju.com/Api/Account/androidequipment
     * */
    public function androidequipment()
    {
        $rm = I("post.");
        $uuid = $rm['uuid'];
        $smartphones = $rm['smartphones'];
		//链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        $token=$rm['accesstoken'];
        $userflag=$this->checktoken($token);
        if($userflag){
			if (!empty($uuid) && !empty($smartphones)){
				$where['uuid'] = $uuid;
				$where['smartphones'] = $smartphones;
				$where['isdelete'] = 0;
				$count = M('equipmentfile')->data($where)->add();
				if ($count>0) {
					$this->myApiPrint('success', 200);
				} else {
					$this->myApiPrint('error', 404);
				}
			}
		}else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
    }

}