<?php

/**
 * 个人帐号公开接口API
 * 1.忘记密码
 * 2.修改密码
 * 3.修改头像
 * 4.增加经验接口（成长体系登录和充值情况下）
 * 5.修改基本信息
 * 6.根据用户usertoken显示基本信息
 */

namespace Api\Controller;

use Common\Controller\ApiController;

class UserController extends ApiController {

    public function _initialize() {
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 登录请求获取token 保存在REDIS里
     * 包括游客和本站用户
     * 测试接口：http://localhost:8080/jiuducaijingwebapi/Api/User/accesstoken/username/123/userpwd/134
     * */

    public function accesstoken() {
        $user = I('get.username');
        $pwd = I('get.userpwd');
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        if (!empty($user) && !empty($pwd)) {
            $where['userpwd'] = $pwd;
            $where['username'] = $user;
            $where['isdelete'] = 0;
            $owner = M('userinfos');
            $resn = $owner->where($where)
                    ->field("userid")
                    //->fetchsql('true')
                    ->find();
            if (!empty($resn)) {
                $userid = $resn["userid"];
//            var_dump($userid);
                //调用声称toke的方法
                $nick = '用户' . randCodeM(4);
                $time = uniqid();
                $token = md5($nick . $time . $userid);
//            var_dump($token);
                //保存到redis里
                $sitename = C('SITENAME');
                //\Predis\Autoloader::register();
                //$redis = new \Predis\Client();
                $redis->hmset($sitename . '-token-' . $token, array('uid' => $userid, 'type' => 2)); //添加到redis缓存中 本站用户是2
                $redis->expire($sitename . '-token-' . $token, 604800); //设置过期时间 86400  1440分钟  168小时7条
                $accesstoken = $sitename . '-token-' . $token;

                //获取过期时间
                $expiresdate = $redis->ttl($sitename . '-token-' . $token);
                //获取用户个人信息
                $where1['userid'] = $userid;
                $us = M('userinfos')->alias('u')
                                ->join("left join wht_rooms as r on r.roomid = u.roomid")->where($where1)->field('u.username,u.email,u.nickname,u.headimage,u.phone,u.sharecode,u.gradeid,u.expervalue,u.ninemoney,u.balance,u.signature,u.qq,u.weixin,u.sina,r.roomcode,u.usertype,u.gender,u.roomid,u.job')->find();
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
                if (!empty($accesstoken)) {
                    $this->myApiPrint('success', 200, $result);
                } else {
                    $this->myApiPrint('don\'t find ', 404);
                }
            } else {
                $this->myApiPrint('user does not exist', -0201);
            }
        } else {
            //游客登录
            $token = $this->nicktoken();
            //保存到redis里
            $sitename = C('SITENAME');
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
            $redis->hmset($sitename . '-token-' . $token, array('uid' => 0, 'type' => 1)); //添加到redis缓存中游客是1
            $redis->expire($sitename . '-token-' . $token, 28800); //设置过期时间 8个小时
            //获取过期时间
            $expiresdate = $redis->ttl($sitename . '-token-' . $token);
            $accesstoken = $sitename . '-token-' . $token;
            $result = array(
                'token' => $accesstoken,
                'type' => 1,
                'expires' => $expiresdate
            );
            if (!empty($accesstoken)) {
                $this->myApiPrint('success', 200, $result);
            } else {
                $this->myApiPrint('don\'t find ', 404);
            }
        }
    }

    /*
     * 2016-08-15 根据用户token获取用户的个人信息 sjy
     * Api/User/showUserInfo/accesstoken/{accesstoken}
     */

    public function showUserInfo() {
        $atoken = I('post.accesstoken'); //获取用户的token
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        if ($this->checktoken($atoken)) {
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
            $re = $redis->hgetall($atoken); //获取redis中的token
            $where["userid"] = $re["uid"];
            if ($where["userid"] != 0) {
                $owner = M('userinfos');
                $resn = $owner
                        ->where($where)
                        ->field("createtime,username,email,nickname,balance,headimage,phone,qq,weixin,sina,gradeid,expervalue,ninemoney,roomid,accredid")
                        ->select();
                if (!$resn) {
                    $this->myApiPrint('don\'t find ', 404);
                } else {
                    $msg = 'success';
                    $this->myApiPrint($msg, 200, $resn);
                }
            } else {
                $this->myApiPrint('error', 404);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     * 2016-08-15 根据用户token修改用户的个人信息 sjy
     * Api/User/updateuserInfo/accesstoken/wht1-token-450041672289483b6a7d556d45987f51/nickname/1234/signature/1/job/1/gender/1
     * mdy:lwx 2016-08-24
     */

//    public function updateuserInfo(){
//        \Predis\Autoloader::register();
//        $redis = new \Predis\Client();
//        $rm=I("get.");
//        $atoken = $rm["accesstoken"];//获取用户的token
//        $owner = M('userinfos');
//        if($this->checktoken($atoken)) {
//            $re=$redis ->get($atoken);//获取redis中的token
////            var_dump($re);
//            $data["userid"] =$re["uid"];
//            if($data["userid"]>0){
//                //判断昵称是否为空和是否已存在
//                if(!empty($rm["nickname"])) {
//                    //不为空判断是否为空
//                    $count = $owner->where(array("nickname"=>$rm["nickname"]))->count();
//                    if($count>0){
//                        $this->myApiPrint('nickname already exists', 300);
//                    }
//                    $where["nickname"] = $rm["nickname"];
//                }
//                //判断个性签名是否为空
//                if(!empty($rm["signature"])){
//                    $where["signature"] = $rm["signature"];
//                }
//                //判断职位是否为空
//                if(!empty($rm["job"])){
//                    $where["job"] = $rm["job"];
//                }
//                //判断性别
//                if(!empty($rm["gender"])){
//                    if($rm["gender"]>4){
//                        $this->myApiPrint('gender only has 1,2,3', 300);
////                        exit();
//                    }
//                    $where["gender"] = $rm["gender"];
//                }
//                $resn = $owner
//                    ->where($data)
//                    ->save($where);
////                var_dump($rm);
////                var_dump($data);
////                var_dump($where);
////                var_dump($resn);
////                exit();
//                if (!$resn) {
//                    $this->myApiPrint('submit error', 300);
//                } else {
//                    $where1['userid'] = $re["uid"];//从token中获取，
//                    $type= $re["type"];
//                    //$where1['isdelete'] = 0;
//                    $us = M('userinfos')
//                        ->alias('u')
//                        ->join("left join wht_rooms as r on r.roomid = u.roomid")
//                        ->where($where1)
//                        ->field('u.username,u.email,u.nickname,u.headimage,u.phone,u.sharecode,u.gradeid,u.expervalue,u.ninemoney,u.balance,u.signature,u.qq,u.weixin,u.sina,r.roomcode,u.usertype,u.gender,u.roomid,u.job')
//                        ->find();
//                    $result = array(
//                        'token'=>$atoken,
//                        'type'=>$type,
//                        'username'=>$us['username'],
//                        'email'=>$us['email'],
//                        'nickname'=>$us['nickname'],
//                        'headimage'=>$us['headimage'],
//                        'phone'=>$us['phone'],
//                        'phone'=>$us['phone'],
//                        'phone'=>$us['phone'],
//                        'sharecode'=>$us['sharecode'],
//                        'gradeid'=>$us['gradeid'],
//                        'expervalue'=>$us['expervalue'],
//                        'ninemoney'=>$us['ninemoney'],
//                        'balance'=>$us['balance'],
//                        'signature'=>$us['signature'],
//                        'qq'=>$us['qq'],
//                        'sina'=>$us['sina'],
//                        'weixin'=>$us['weixin'],
//                        'roomcode'=>$us['roomcode'],
//                        'usertype'=>$us['usertype'],
//                        'gender'=>$us['gender'],
//                        'roomid,'=>$us['roomid'],
//                        'job'=>$us['job']
//                    );
//                    $msg = 'success';
//                    $this->myApiPrint($msg, 200, $result);
//                }
//            }else{
//                $this->myApiPrint(' error', 300);
//            }
//        }else{
//            $this->myApiPrint('accesstoken error', 300);
//        }
//    }


    public function updateuserInfo() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $rm = I("get.");
        $atoken = $rm["accesstoken"]; //获取用户的token
        $owner = M('userinfos');
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //获取redis中的token
//            var_dump($re);
            $data["userid"] = $re["uid"];
            $userid = $re["uid"];
            if ($data["userid"] > 0) {
                //判断昵称是否为空和是否已存在
                //$nickname=$owner->where('userid='.$userid)->field("nickname")->select();

                $uid = $owner->where(array("nickname" => $rm["nickname"]))->field("userid")->find(); //查询当前昵称是否有人在用
                if ($uid != null) {
                    if ($uid["userid"] != $userid) {
                        $this->myApiPrint('nickname already exists', 300);
                    }
                }

                $where["nickname"] = $rm["nickname"];
                $where["signature"] = $rm["signature"];
                $where["job"] = $rm["job"];

                if ($rm["gender"] >= 4) {
                    $this->myApiPrint('gender only has 1,2,3', 300);
                }

                $where["gender"] = $rm["gender"];





//
                $resn = $owner
                        ->where($data)
                        ->save($where);
//
//
                if (!$resn) {
                    $this->myApiPrint('submit error', 300);
                } else {
                    $where1['userid'] = $re["uid"]; //从token中获取，
                    $type = $re["type"];

                    $us = M('userinfos')
                            ->alias('u')
                            ->join("left join wht_rooms as r on r.roomid = u.roomid")
                            ->where($where1)
                            ->field('u.username,u.email,u.nickname,u.headimage,u.phone,u.sharecode,u.gradeid,u.expervalue,u.ninemoney,u.balance,u.signature,u.qq,u.weixin,u.sina,r.roomcode,u.usertype,u.gender,u.roomid,u.job,u.emcid,u.emcpwd')
                            ->find();
                    $result = array(
                        'token' => $atoken,
                        'type' => $type,
                        'username' => $us['username'],
                        'email' => $us['email'],
                        'nickname' => $us['nickname'],
                        'headimage' => $us['headimage'],
                        'phone' => $us['phone'],
                        'phone' => $us['phone'],
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
                        'job' => $us['job'],
                        'emcid' => $us['emcid'],
                        'emcpwd' => $us['emcpwd']
                    );
                    $msg = 'success';
                    $this->myApiPrint($msg, 200, $result);
                }
            } else {
                $this->myApiPrint(' error', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     * 增加经验接口
     * Api/User/addempiricalvalue/accesstoken/{accesstoken}
     * */

    public function addempiricalvalue() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('get.accesstoken');
        //先修改用户值判断用户
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            if ($uid > 0) {
                //说明是本站用户不是游客
                $us = M('userinfos');
                $ev = M('empiricalvalue');
                $userinfo['expervalue'] = I('post.expervalue');
//                 $info = $us->data($userinfo)->add();
                //登录成功以后添加经验值
                //先修改用户经验值
                $exex = $us->where(array("userid" => $uid))->field("expervalue")->find();
                $expervalue["expervalue"] = intval($exex) + 100;
                $info = $us->where(array("userid" => $uid))->save($expervalue);
                if ($info > 0) {
                    //添加经验值记录
                    $exp['userid'] = $uid;
                    $exp['etype'] = I('post.etype');
                    $exp['ecount'] = I('post.ecount');
                    $info2 = $us->data($exp)->add();
                    if ($info2 > 0) {
                        $this->myApiPrint('success', 200);
                    } else {
                        $this->myApiPrint('submit error', 300);
                    }
                } else {
                    $this->myApiPrint('submit error', 300);
                }
            } else {
                $this->myApiPrint('error:Tourists cannot add experience', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //2016-08-16 根据用户token,旧密码，新密码修改密码 sjy
    //Api/User/updatepwd/accesstoken/{accesstoken}/olduserpwd/phone/newuserpwd/{newuserpwd}
    public function updatepwd() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $newuserpwd = I('post.newuserpwd');
        $atoken = I('post.accesstoken'); //获取用户的token
        $userpwd = I('post.olduserpwd'); //获取旧密码

        $owner = M('userinfos');
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //获取redis中的token

            $where["userid"] = $re["uid"]; //获取用户id

            if ($where["userid"] != 0) {
                //判断用户是否存在
                //  $where["userpwd"]=$userpwd;//获取用户旧密码

                $count = $owner
                        ->where($where)
                        ->field('userpwd')
                        ->select();


                if ($count[0]["userpwd"] != $userpwd) {
                    $this->myApiPrint('pwd error', 300);
                }
                // $data["userpwd"] = $newuserpwd;
                if (empty($newuserpwd)) {
                    $this->myApiPrint('password is empty', 300);
                } else {
//                    var_dump(strlen($newuserpwd));
                    if (strlen($newuserpwd) == 32) {
                        $data["userpwd"] = $newuserpwd;
                    } else {
                        $this->myApiPrint('Passwords not MD5 encryption', 300);
                    }
                }

                $h["userid"] = $re["uid"];
                //修改的时候
                $resn = $owner
                        ->where($h)
                        ->save($data);
//                var_dump($resn);
//                var_dump($where);
//                var_dump($data);
//                exit();

                if (!$resn) {
                    $this->myApiPrint('submit error', 500);
                } else {
                    $msg = 'success';
                    $this->myApiPrint($msg, 200, $resn);
                }
            } else {
                $this->myApiPrint('is tourist error', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //Api/User/changeImage/accesstoken/wht1-token-62b03a6abe829f6ea3f36824efc31cae/
    //修改头像
//    public function changeImage(){
//        $token=$_POST["accesstoken"];
//        $user=$this->checktoken($token);
//
//        // print_r($user);die;
//        if($user){
//            $type=$_POST["filename"];
//
//            $up = new \Org\Util\Uploads();
//            $img_url = $up->uploadFile($type);
//            if ($img_url) {
//                // echo $img_url;
//                $this->myApiPrint('success','200');
//            } else {
//                $this->myApiPrint('error','300');
//            }
//
//            $result=M("userinfos")->where("userid=$userid")->setField("headimage",$img_url);
//            if($result){
//                $this->myApiPrint("success","200",$img_url);
//            }else{
//                $this->myApiPrint("error2","300");
//            }
//
//            // $target_path  = "./Upload/";//接收文件目录
//            // $target_path = $target_path . basename( $_FILES['uploadedfile']['name']);
//            // if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
//            //     echo "The file ".  basename( $_FILES['uploadedfile']['name']). " has been uploaded";
//            // }  else{
//            //     echo "There was an error uploading the file, please try again!" . $_FILES['uploadedfile']['error'];
//            // }
//        }else{
//            //  var_dump("4");
//            $this->myApiPrint("error3","300");
//        }
//    }

    /*
     * 签到接口
     * 2016-09-03
     * Api/User/addsign/accesstoken/{accesstoken}
     * */
    public function addsign() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('get.accesstoken'); //获取token
        //判断token
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            if ($uid > 0) {
                //说明是本站用户不是游客
                $us = M('userinfos');
                $ev = M('empiricalvalue');
                $sl = M('signlog');
                $lt = $us
                        ->where(array('userid' => $uid))
                        ->field('lastsigndate,signcount,issign,expervalue,gradeid')
                        ->select();


                $timeee = '%' . date("Y-m-d") . '%'; //获取今天
                $ee['createtime'] = array('like', $timeee);
                $ee['uid'] = $uid;
                $counts = $sl->where($ee)->field('sid')->count();
//                echo $sl->where($ee)->field('sid')->fetchSql()->count();
//                exit();

                if ($counts > 0) {
                    $this->myApiPrint('current users sign in', 300);
                }
                $sign['uid'] = $uid;
                $sign['expervalue'] = intval($lt[0]['expervalue']) + 100; //经验值
                //判断当前登录的用户是否是连续签到的,如果是，需要判断用户等级是否为0，如果是等级为0，判断用户签到是否是大于等于第5天
                if (empty($lt[0]["issign"])) {
                    //为空的话表示第一次签到
                    $sign['issign'] = 1;
                    $sign['signcount'] = 1;
                    //先修改用户表的信息
                    $uss = $us->data($sign)->save();
                    var_dump($uss);
                    //为这用户添加经验值
                    $ev->data(array("userid" => $uid, "etype" => 4, "ecount" => 100))->add();
                    //为这用添加签到记录表
                    $sl1 = $sl->data(array("uid" => $uid))->add();
//                    print_r($sl1);die;
                    if ($sl1 > 0) {
                        $this->myApiPrint('success', 200);
                    } else {
                        $this->myApiPrint('submit error', 300);
                    }
                } else {
                    //表示当前用户是连续签到的
                    //先判断最后签到的天数和当前时间相比是不是等于1
                    if ($lt[0]["lastsigndate"]) {
                        $nt = date("Ymd") - intval(date('Ymd', $lt[0]["lastsigndate"]));
                        if ($nt == 1) {
                            $sign['issign'] = 1; //表示当前用户还是连续签到的
                        } else {
                            $sign['issign'] = 0; //断掉了，重新来
                        }
                        $sign['signcount'] += 1; //累计签到天数是不变的
                        //当前用户等级是1级并且连续签到5天的情况下给用户升级成下一级
                        if ($lt[0]["gradeid"] == 1) {
                            if ($sign['signcount'] == 5) {
                                $sign["gradeid"] += 1;
                            }
                        }
                        //先修改用户表的信息
                        $us->data($sign)->save();
                        //为这用户添加经验值
                        $ev->data(array("userid" => $uid, "etype" => 4, "ecount" => 100))->add();
                        //为这用添加签到记录表
                        $sl->data(array("uid" => $uid))->add();
                        if ($sl > 0) {
                            $this->myApiPrint('success', 200);
                        } else {
                            $this->myApiPrint('submit error', 300);
                        }
                    } else {
                        //为空的话表示第一次签到
                        $sign['issign'] = 1;
                        $sign['signcount'] += 1;
                        //先修改用户表的信息
                        $us->data($sign)->save();
                        //为这用户添加经验值
                        $ev->data(array("userid" => $uid, "etype" => 4, "ecount" => 100))->add();
                        //为这用添加签到记录表
                        $sl->data(array("uid" => $uid))->add();
                        if ($sl > 0) {
                            $this->myApiPrint('success', 200);
                        } else {
                            $this->myApiPrint('submit error', 300);
                        }
                    }
                }
            } else {
                $this->myApiPrint('error:Tourists cannot add experience,please login', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     * 签到接口
     * 2016-09-03 sjy
     * Api/User/addsigns/accesstoken/{accesstoken}
     * */

    public function addsigns() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('get.accesstoken'); //获取token
        //判断token
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            $where["userid"] = $uid;
            if ($uid > 0) {
                $us = M('userinfos');
                $now = date("Y-m-d");

                $ev = $us
                        ->field('expervalue,signcount,lastsigndate')
                        ->where($where)
                        ->find();
                /* var_dump($ev["expervalue"]);
                  $cha=$now-$ev["expervalue"]; */

                if (empty($ev["lastsigndate"])) {
                    //当前为第一次登录
                    $data["signcount"] = 1;
                    $data["lastsigndate"] = $now;
                    $data["expervalue"] = $ev["expervalue"] + 100;

                    $expervalue = $data["expervalue"];
                    //这里的作用就是需要判断增加完经验值以后需要知道当前的经验值是否已经够修改等级了
                    //这里的作用就是需要判断增加完经验值以后需要知道当前的经验值是否已经够修改等级了
                    if (intval($expervalue) < 500) {
                        $grade = 1; //默认从1开始 新手
                    } else if (intval($expervalue) >= 500 && intval($expervalue) < 1000) {
                        $grade = 2; //平名
                    } else if (intval($expervalue) >= 1000 && intval($expervalue) < 1500) {
                        $grade = 3; //富人
                    } else if (intval($expervalue) >= 1500 && intval($expervalue) < 3000) {
                        $grade = 4; //财主
                    } else if (intval($expervalue) >= 3000 && intval($expervalue) < 4500) {
                        $grade = 5; //大财主
                    } else if (intval($expervalue) >= 4500 && intval($expervalue) < 6000) {
                        $grade = 6; //富商
                    } else if (intval($expervalue) >= 6000 && intval($expervalue) < 7500) {
                        $grade = 7; //大富商
                    } else if (intval($expervalue) >= 7500 && intval($expervalue) < 8000) {
                        $grade = 8; //富豪
                    } else if (intval($expervalue) >= 8000 && intval($expervalue) < 9500) {
                        $grade = 9; //大富豪
                    } else if (intval($expervalue) >= 9500 && intval($expervalue) < 10000) {
                        $grade = 10; //富绅
                    } else if (intval($expervalue) >= 10000 && intval($expervalue) < 50000) {
                        $grade = 11; //大富绅
                    } else if (intval($expervalue) >= 50000 && intval($expervalue) < 100000) {
                        $grade = 12; //子爵
                    } else if (intval($expervalue) >= 100000 && intval($expervalue) < 500000) {
                        $grade = 13; //公爵
                    } else if (intval($expervalue) >= 500000 && intval($expervalue) < 1000000) {
                        $grade = 14; //伯爵
                    } else if (intval($expervalue) >= 1000000 && intval($expervalue) < 5000000) {
                        $grade = 15; //侯爵
                    } else if (intval($expervalue) >= 5000000 && intval($expervalue) < 10000000) {
                        $grade = 16; //王爵
                    } else if (intval($expervalue) > 10000000) {
                        $grade = 17; //国王
                    }
                    $data["gradeid"] = $grade;
                    $addev = $us
                            ->where($where)
                            ->data($data)
                            ->save();
                    if ($addev) {
                        $signcount = 1;
                        $this->myApiPrint(' sign secessful', 200, $signcount);
                    } else {
                        $this->myApiPrint(' sign fail', 300);
                    }
                } else {
                    //
                    $min = (strtotime(date("Y-m-d")) - strtotime(date($ev["lastsigndate"]))) / 86400;

                    if ($min == 0) {
                        $signcount = $ev["signcount"];
                        $this->myApiPrint('  today is sign', 300, $signcount);
                    } else {

                        if ($min > 1) {
                            $data["signcount"] = 1;
                            $data["lastsigndate"] = $now;

                            $data["expervalue"] = $ev["expervalue"] + 100;

                            $expervalue = $data["expervalue"];
                            //这里的作用就是需要判断增加完经验值以后需要知道当前的经验值是否已经够修改等级了
                            if (intval($expervalue) < 500) {
                                $grade = 1; //默认从1开始 新手
                            } else if (intval($expervalue) >= 500 && intval($expervalue) < 1000) {
                                $grade = 2; //平名
                            } else if (intval($expervalue) >= 1000 && intval($expervalue) < 1500) {
                                $grade = 3; //富人
                            } else if (intval($expervalue) >= 1500 && intval($expervalue) < 3000) {
                                $grade = 4; //财主
                            } else if (intval($expervalue) >= 3000 && intval($expervalue) < 4500) {
                                $grade = 5; //大财主
                            } else if (intval($expervalue) >= 4500 && intval($expervalue) < 6000) {
                                $grade = 6; //富商
                            } else if (intval($expervalue) >= 6000 && intval($expervalue) < 7500) {
                                $grade = 7; //大富商
                            } else if (intval($expervalue) >= 7500 && intval($expervalue) < 8000) {
                                $grade = 8; //富豪
                            } else if (intval($expervalue) >= 8000 && intval($expervalue) < 9500) {
                                $grade = 9; //大富豪
                            } else if (intval($expervalue) >= 9500 && intval($expervalue) < 10000) {
                                $grade = 10; //富绅
                            } else if (intval($expervalue) >= 10000 && intval($expervalue) < 50000) {
                                $grade = 11; //大富绅
                            } else if (intval($expervalue) >= 50000 && intval($expervalue) < 100000) {
                                $grade = 12; //子爵
                            } else if (intval($expervalue) >= 100000 && intval($expervalue) < 500000) {
                                $grade = 13; //公爵
                            } else if (intval($expervalue) >= 500000 && intval($expervalue) < 1000000) {
                                $grade = 14; //伯爵
                            } else if (intval($expervalue) >= 1000000 && intval($expervalue) < 5000000) {
                                $grade = 15; //侯爵
                            } else if (intval($expervalue) >= 5000000 && intval($expervalue) < 10000000) {
                                $grade = 16; //王爵
                            } else if (intval($expervalue) > 10000000) {
                                $grade = 17; //国王
                            }
                            $data["gradeid"] = $grade;
                            $addev = $us
                                    ->where($where)
                                    ->data($data)
                                    ->save();

                            if ($addev) {
                                $signcount = 1;
                                $this->myApiPrint(' sign secess', 200, $signcount);
                            } else {
                                $this->myApiPrint(' sign  is fail', 300);
                            }
                        } else {
                            $data["signcount"] = $ev["signcount"] + 1;
                            $data["lastsigndate"] = $now;

                            $data["expervalue"] = $ev["expervalue"] + 100;

                            $expervalue = $data["expervalue"];


                            //这里的作用就是需要判断增加完经验值以后需要知道当前的经验值是否已经够修改等级了
                            $grade = 1; //默认从1开始 新手
                            if (intval($expervalue) == 500) {
                                $grade = 2; //平名
                            } else if (intval($expervalue) > 500 && intval($expervalue) <= 1000) {
                                $grade = 3; //富人
                            } else if (intval($expervalue) > 1000 && intval($expervalue) <= 1500) {
                                $grade = 4; //财主
                            } else if (intval($expervalue) > 1500 && intval($expervalue) <= 3000) {
                                $grade = 5; //大财主
                            } else if (intval($expervalue) > 3000 && intval($expervalue) <= 4500) {
                                $grade = 6; //富商
                            } else if (intval($expervalue) > 4500 && intval($expervalue) <= 6000) {
                                $grade = 7; //大富商
                            } else if (intval($expervalue) > 6000 && intval($expervalue) <= 7500) {
                                $grade = 8; //富豪
                            } else if (intval($expervalue) > 7500 && intval($expervalue) <= 8000) {
                                $grade = 9; //大富豪
                            } else if (intval($expervalue) > 8000 && intval($expervalue) <= 9500) {
                                $grade = 10; //富绅
                            } else if (intval($expervalue) > 9500 && intval($expervalue) <= 10000) {
                                $grade = 11; //大富绅
                            } else if (intval($expervalue) > 10000 && intval($expervalue) <= 50000) {
                                $grade = 12; //子爵
                            } else if (intval($expervalue) > 50000 && intval($expervalue) <= 100000) {
                                $grade = 13; //公爵
                            } else if (intval($expervalue) > 100000 && intval($expervalue) <= 500000) {
                                $grade = 14; //伯爵
                            } else if (intval($expervalue) > 500000 && intval($expervalue) <= 1000000) {
                                $grade = 15; //侯爵
                            } else if (intval($expervalue) > 1000000 && intval($expervalue) <= 5000000) {
                                $grade = 16; //王爵
                            } else if (intval($expervalue) > 5000000 && intval($expervalue) <= 10000000) {
                                $grade = 17; //国王
                            }
                            $data["gradeid"] = $grade;

                            $addev = $us
                                    ->where($where)
                                    ->data($data)
                                    ->save();

                            if ($addev) {
                                $signcount = $ev["signcount"] + 1;
                                $this->myApiPrint(' sign secess', 200, $signcount);
                            } else {
                                $this->myApiPrint(' sign  is fail', 300);
                            }
                        }
                    }
                }
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     *
     * 账户明细   2016-09-07  lwx
     * Api/User/accountdetail/accesstoken/
     * */

    public function accountdetail() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $atoken = I('get.accesstoken'); //获取用户的token
        $userinfos = M('recharge');
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $where["userid"] = $re["uid"];
            $where["isdelete"] = 0;
			$where["PayStatus"]=2;
            if ($where["userid"] != 0) {
                $useracount = $userinfos
                        ->where($where)
                        ->field('realprice,paymentby,specialdes,createtime')
                        ->order("createtime desc")
                        ->select();
//                var_dump($useracount);
                $result = array();
                foreach ($useracount as $key => $vv) {
                    if ($vv['paymentby'] == 1) {
                        $pw = '支付宝';
                    } else if ($vv['paymentby'] == 2) {
                        $pw = '微信';
                    } else if ($vv['paymentby'] == 3) {
                        $pw = '银行卡';
                    } else if ($vv['paymentby'] == 4) {
                        $pw = '苹果内购';
                    } else {
                        $pw = '余额购买';
                    }
                    $result[$key] = array(
                        "realprice" => '-' . $vv['realprice'],
                        "specialdes" => $vv['specialdes'],
                        "paymentby" => $pw,
                        "createtime" => get_week($vv['createtime']),
                        "ry" => date("m-d", strtotime($vv['createtime'])),
                    );
                }
                $this->myApiPrint('success', 200, $result);
            } else {
                $this->myApiPrint('is tourist', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     * 获取累计签到接口
     * 2016-09-06
     * Api/User/getsigns/accesstoken/{accesstoken}
     *
     * */

    public function getsigns() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('get.accesstoken');
        //判断token
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            if ($uid > 0) {
                //说明是本站用户不是游客
                $us = M('userinfos');
                $lt = $us->where(array('userid' => $uid))->field('signcount,lastsigndate,expervalue')->find();

                $min = (strtotime(date("Y-m-d")) - strtotime(date($lt["lastsigndate"]))) / 86400;

                if ($min > 1) {
                    $signcount = 0;
                    $data["signcount"] = 0;
                    $si = $us
                            ->where(array('userid' => $uid))
                            ->data($data)
                            ->save();

                    if ($si) {
                        $result = array("signcount" => $signcount);
                        $this->myApiPrint('success', 200, $result);
                    }
                }

                if (!empty($lt)) {

                    $this->myApiPrint('success', 200, $lt);

                    $this->myApiPrint('success', 200, $lt['signcount']);

                    $result = array("signcount" => $signcount);
                    $this->myApiPrint('success', 200, $result);
                } else {
                    $this->myApiPrint('error', 300);
                }
            } else {
                $this->myApiPrint('error:Tourists cannot add experience,please login', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     * 修改头像
     * 2016-09-06
     * Api/User/Iosface/accesstoken/{accesstoken}
     *
     * */
    /* public function Iosface(){

      \Predis\Autoloader::register();
      $redis = new \Predis\Client();
      $atoken = I('post.accesstoken');
      $images = I('post.image');
      if($this->checktoken($atoken)){
      $re=$redis->hgetall($atoken);//添加到redis缓存中
      $uid = $re["uid"];
      if($uid>0) {
      header("`Content-Type: application/octet-stream;charset=UTF-8");
      //                header('Content-type: text/json; charset=UTF-8');
      $byte = str_replace(' ','',$images);
      $byte = str_ireplace("<",'',$byte);
      $byte = str_ireplace(">",'',$byte);
      $byte =pack("H*",$byte);//$byte =unpack("H*",$byte);
      $dataname = './Uploads/' . uniqid() . '.png';
      var_dump($byte);
      exit();
      if(file_put_contents($dataname, $byte,true)) {
      $this->myApiPrint('success',200); //返回数据结构自行封装
      }else{
      $this->myApiPrint('上传出错',301);
      }
      }else{
      $this->myApiPrint('error:Tourists cannot add experience,please login',300);
      }
      }else{
      $this->myApiPrint('accesstoken don\'t find',404);
      }
      } */

    public function Iosface() {
        header("Content-Type: application/octet-stream");
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('post.accesstoken');
        $re = $redis->hgetall($atoken); //添加到redis缓存中
        $uid = $re["uid"];
        if ($uid <= 0) {
            $this->myApiPrint('error:Tourists cannot add face,please login', 300);
        }
        //否则，上传头像 IDcard  /headerimage
        $byte = $_POST['image'];
        $byte = str_replace(' ', '', $byte);   //处理数据
        $byte = str_ireplace("<", '', $byte);
        $byte = str_ireplace(">", '', $byte);
        $byte = pack("H*", $byte);      //16进制转换成二进制
        $zs = '/data/wwwroot/api/Uploads/headimg/' . uniqid() . '.jpg';
        $ss = substr($zs, intval(strpos($zs, "headimg/") + 8));
        //写入文件中！
        if (file_put_contents($zs, $byte, true)) {
            //上传到阿里云里
//            $setting=C('UPLOAD_SITEIMG_OSS');
//            $Upload = new \Think\Upload($setting);
//            $info = $Upload->upload($zs);
//            $imgurl = OSS.$info[gifturl][savepath].$info[gifturl][savename];
//            $data["gifturl"] = $imgurl;
//			var_dump($_FILES['txtImgUrl1']);
//			exit();
//            $accessKeyId = "qz2BRTXV7piA6zcK";
//            $accessKeySecret = "iwEFsvsYphgl0xJKOYoxbKPRvm4EAN";
//            $endpoint = "http://oss-cn-hangzhou.aliyuncs.com";
//            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
//            $bucket = "jdcjapp";
//            $object = '/headerimage/'.$zs;
//            $filePath = __FILE__;
//            try{
//                $ossClient->uploadFile($bucket, $object, $filePath);
//            } catch(OssException $e) {
//                printf(__FUNCTION__ . ": FAILED\n");
//                printf($e->getMessage() . "\n");
//                return;
//            }
            //修改用户的数据
            $imgurl = "http://test.api.9dushuju.com/Uploads/headimg/" . $ss;
            $result = M('userinfos')->where(array("userid" => $uid))->save(array("headimage" => $imgurl));

            if ($result) {
                $roomid = M("userinfos")->where("userid=$uid")->field("roomid")->find();
                $rid = $roomid["roomid"];
                if ($roomid > 0 && $roomid != null) {
                    $result = M("rooms")->where("roomid=$rid")->setField("headimage", $rid);
                }
            }


            $this->myApiPrint('success', 200, $imgurl); //返回数据结构自行封装
        } else {
            $this->myApiPrint('上传出错', 301, "fdafds");
        }
//        \Predis\Autoloader::register();
//        $redis = new \Predis\Client();
//        $atoken = I('post.accesstoken');
//        $images = I('post.image');
//        if($this->checktoken($atoken)){
//            $re=$redis->hgetall($atoken);//添加到redis缓存中
//            $uid = $re["uid"];
//            if($uid>0) {
//                $byte = str_replace(' ','',$images);
//                $byte = str_ireplace("<",'',$byte);
//                $byte = str_ireplace(">",'',$byte);
//                $byte =pack("H*",$byte);//$byte =unpack("H*",$byte);
//                $dataname = './Uploads/' . uniqid() . '.png';
//                $result = array(
//                    "erjinzhiliu"=>unpack("H*",$byte)
//                );
//                file_put_contents(uniqid().'.jpg',$byte);//写入文件中！
////                if(file_put_contents(uniqid().'.jpg', $byte,true)) {
//////                if(file_put_contents($dataname, $byte,true)) {
////                    $this->myApiPrint('success',200,$result); //返回数据结构自行封装
////                }else{
////                    $this->myApiPrint('上传出错',301,$result);
////                }
//            }else{
//                $this->myApiPrint('error:Tourists cannot add experience,please login',300);
//            }
//        }else{
//            $this->myApiPrint('accesstoken don\'t find',404);
//        }
    }

    /*
     * 等级接口
     * 2016-09-03
     * Api/User/getgrade/accesstoken/{accesstoken}
     * */

    public function getgrade() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('get.accesstoken');
        //先修改用户值判断用户
        if ($this->checktoken($atoken)) {
            //判断当前登录用户是否是连续签到的，
            //判断这个人最后一次登录时间是
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            if ($uid > 0) {
                //说明是本站用户不是游客
                $us = M('userinfos');
                $exex = $us->where(array("userid" => $uid))->find();
                $dj = $exex['gradeid'];
                $jyz = 0;
                $bfb = 0.0;
                $headimage=$exex["headimage"];
                if ($dj == 1) {
                    $jyz = 500 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 500);
                } else if ($dj == 2) {
                    $jyz = 1000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 1000);
                } else if ($dj == 3) {
                    $jyz = 1500 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 1500);
                } else if ($dj == 4) {
                    $jyz = 3000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 3000);
                } else if ($dj == 5) {
                    $jyz = 4500 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 4500);
                } else if ($dj == 6) {
                    $jyz = 6000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 6000);
                } else if ($dj == 7) {
                    $jyz = 7500 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 7500);
                } else if ($dj == 8) {
                    $jyz = 8000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 8000);
                } else if ($dj == 9) {
                    $jyz = 9500 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 9500);
                } else if ($dj == 10) {
                    $jyz = 10000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 10000);
                } else if ($dj == 11) {
                    $jyz = 50000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 50000);
                } else if ($dj == 12) {
                    $jyz = 100000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 100000);
                } else if ($dj == 13) {
                    $jyz = 500000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 500000);
                } else if ($dj == 14) {
                    $jyz = 1000000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 1000000);
                } else if ($dj == 15) {
                    $jyz = 5000000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 5000000);
                } else if ($dj == 16) {
                    $jyz = 10000000 - intval($exex['expervalue']);
                    $bfb = floatval(intval($exex['expervalue']) / 10000000);
                } else if ($dj == 17) {
                    $jyz = 0;
                    $bfb = 100;
                }
//                var_dump($jyz);
//                var_dump($bfb);
                $result = array(
                    'gradeid' => "{$dj}",
                    'bfb' => "{$bfb}",
                    'syjyz' => "{$jyz}",
                    'headimage'=>"{$headimage}"
                );
                $this->myApiPrint('success', 200, $result);
            } else {
                $this->myApiPrint('error:users is not exists', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
      获取等级接口 sjy 2016-0909
     */

    public function getgrades() {

        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
        $atoken = I('get.accesstoken');
        //判断token
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $uid = $re["uid"];
            if ($uid > 0) {
                $us = M('userinfos');
                $where["userid"] = $re["uid"];

                $ev = $us
                        ->field('expervalue,gradeid')
                        ->where($where)
                        ->find();
                /*  var_dump($ev);
                  $ex=intval($ev["expervalue"]/500);
                  var_dump($ex);
                  $grade=0;$cha=0;$bit=0; */

                if ($ex = 0) {
                    $grade = 0;
                    $cha = 500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 500;
                }


                if ($ex = 1) {
                    $grade = 1;
                    $cha = 1000 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1000;
                }

                if ($ex = 2) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }

                if (3 <= $ex && $ex <= 5) {
                    $grade = 3;
                    $cha = 3000 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 3000;
                }
                if ($ex = 6) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }
                if ($ex = 2) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }
                if ($ex = 2) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }
                if ($ex = 2) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }
                if ($ex = 2) {
                    $grade = 2;
                    $cha = 1500 - $ev["expervalue"];
                    $bit = $ev["expervalue"] / 1500;
                }





                $result = array(
                    'grade' => $ev["gradeid"],
                    'cha' => $cha,
                    'bit' => $bit
                );


                $this->myApiPrint('secess', 200, $result);
            } else {
                $this->myApiPrint('tourist dont getgrade', 404);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    /*
     *
     * 账户余额   2016-09-05  sjy
     * Api/User/getbalance/accesstoken/
     * */

    public function getbalance() {
        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $atoken = I('get.accesstoken'); //获取用户的token
        $userinfos = M('userinfos');
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken); //添加到redis缓存中
            $where["userid"] = $re["uid"];
            if ($where["userid"] != 0) {
                $useracount = $userinfos
                        ->where($where)
                        ->field('balance,ninemoney,nickname')
                        ->find();
                
               $useracount["ninemoney"] = "".floor($useracount["ninemoney"])."";
                
                $this->myApiPrint('success', 200, $useracount);
            } else {
                $this->myApiPrint('is tourist', 300);
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //添加支付历史记录
    public function getPayInfo() {
        
    }

    //用户用余额购买钻石接口
    public function moneyBydiamond() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $token = $_GET["accesstoken"];
        if ($this->checktoken($token)) {
            $user = $redis->hgetall($token);
            $userid = $user["uid"];
            //购买钻石数量
            $diamonds = $_GET["diamonds"];
            //计算购买金额
            $money = floatval(intval($diamonds) / 6);
            $usermoney = M("userinfos")->where("userid=$userid")->field("balance")->find();
            $usermoney = $usermoney["balance"];

            $userdiamond = M("userinfos")->where("userid=$userid")->field("ninemoney")->find();
            $userdiamond = $userdiamond["ninemoney"];
            //余额小于购买的金额
            if (floatval($usermoney) < floatval($money)) {
                return $this->myApiPrint("Insufficient account balance", "300");
            }
            //否则用余额扣钱
            $result = M("userinfos")->where("userid=$userid")->setField(array("balance" => floatval($usermoney) - floatval($money), "ninemoney" => intval($userdiamond) + intval($diamonds)));
            $ss = M("userinfos")->where("userid=$userid")->field('balance')->find();
            if ($result) {
                if ($ss) {
                    $usermoneyarray = array("balance" => $ss['balance'], "diamonds" => intval($userdiamond) + intval($diamonds));
                } else {
                    $usermoneyarray = array("balance" => floatval($usermoney) - floatval($money), "diamonds" => intval($userdiamond) + intval($diamonds));
                }
                //购买成功后增加充值记录
                $ww['userid'] = $userid;
                $ww['totalprice'] = $money;
                $ww['realprice'] = $money;
                $ww['paystatus'] = 2;
                $ww['specialdes'] = "购买" . $diamonds . "个钻石数";
                $ww['paymentby'] = 5; //余额购买
                $ww['launchfrom'] = 1; //mobile
                $ww['userid'] = $userid;
                $ww['orderstatus'] = 1;
                $cc = M("recharge")->data($ww)->add();
                return $this->myApiPrint("success", "200", $usermoneyarray);
            } else {
                return $this->myApiPrint("error", "300");
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //用户苹果内购接口
    public function Buydiamond() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $token = $_GET["accesstoken"];
        if ($this->checktoken($token)) {
            $user = $redis->hgetall($token);
            $userid = $user["uid"];
            //购买钻石数量
            $diamonds = $_GET["diamonds"];
            //计算购买金额
            $money = floatval(intval($diamonds) / 6);

            $userdiamond = M("userinfos")->where("userid=$userid")->field("ninemoney")->find();
            $userdiamond = $userdiamond["ninemoney"];
            $usernine = M("userinfos")->where("userid=$userid")->setField("ninemoney", intval($diamonds) + intval($userdiamond));


            if ($usernine) {
                //购买成功后增加充值记录
                $ww['userid'] = $userid;
                $ww['totalprice'] = $money;
                $ww['realprice'] = $money;
                $ww['paystatus'] = 2;
                $ww['specialdes'] = "购买" . $diamonds . "个钻石数";
                $ww['paymentby'] = 4; //余额购买
                $ww['launchfrom'] = 1; //mobile
                $ww['userid'] = $userid;
                $ww['orderstatus'] = 1;
                $cc = M("recharge")->add($ww);
                return $this->myApiPrint("success", "200");
            } else {
                return $this->myApiPrint("error", "300");
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //修改头像接口，手机端直接上传阿里云，返回链接
    public function changeImageUrl() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();


        $token = $_POST["accesstoken"];
        $userflag = $this->checktoken($token);
        if ($userflag) {
            $headerImage = $_POST["imgurl"];
            $user = $redis->hgetall($token);
            $userid = $user["uid"];
            $result = M("userinfos")->where("userid=$userid")->setField("headimage", $headerImage);
            if ($result) {
                $roomid = M("userinfos")->where("userid=$userid")->field("roomid")->find();
                $rid = $roomid["roomid"];
                if ($roomid > 0 && $roomid != null) {
                    $result = M("rooms")->where("roomid=$rid")->setField("headimage", $headerImage);
                }
                $this->myApiPrint("success", "200");
            } else {
                $this->myApiPrint("error", "500");
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }

    //用户苹果内购接口(余额)
    public function BuyBalance() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $token = $_GET["accesstoken"];
        if ($this->checktoken($token)) {
            $user = $redis->hgetall($token);
            $userid = $user["uid"];
            //购买余额数量
            $balance = $_GET["balance"];
            $userbalance = M("userinfos")->where("userid=$userid")->field("balance")->find();

            $userbalance = $userbalance["balance"];
            $usernine = M("userinfos")->where("userid=$userid")->setField("balance", intval($balance) + intval($userbalance));


            if ($usernine) {
                //购买成功后增加充值记录
                $ww['userid'] = $userid;
                $ww['totalprice'] = $balance;
                $ww['realprice'] = $balance;
                $ww['paystatus'] = 2;
                $ww['specialdes'] = "充值" . $balance . "余额成功！";
                $ww['paymentby'] = 4; //余额购买
                $ww['launchfrom'] = 1; //mobile
                $ww['userid'] = $userid;
                $ww['orderstatus'] = 1;
                $cc = M("recharge")->add($ww);
                return $this->myApiPrint("success", "200");
            } else {
                return $this->myApiPrint("error", "300");
            }
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }
	
	//用户苹果内购接口
	//2016-12-09 post
    public function IosBuydiamond() {

        //链接redis,每次需要的时候重新链接 做初始化
        $redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $token = $_POST["accesstoken"];
        if ($this->checktoken($token)) {
            $user = $redis->hgetall($token);
            $userid = $user["uid"];
            //内购凭证
			$apple_receipt = $_POST['apple_receipt']; //苹果内购的验证收据,由客户端传过来
			//$apple_receipt = '{"receipt-data":"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"}'; //苹果内购的验证收据,由客户端传过来
			//var_dump($apple_receipt);
			//$jsonData = array('receipt-data'=>$apple_receipt);//这里本来是需要base64加密的，我这里没有加密的原因是客户端返回服务器端之前，已经作加密处理
			//$jsonData = json_encode($jsonData);
			
			$url = 'https://buy.itunes.apple.com/verifyReceipt';  正式验证地址
			//$url = 'https://sandbox.itunes.apple.com/verifyReceipt'; //测试验证地址
			//var_dump($jsonData);
			//var_dump($url);
			$response = $this->http_post_data($url,$apple_receipt);
                        
			if($response->{'status'} == 0){
				//验证成功，给用户充值
				$diamonds = $_POST["diamonds"];
				//购买钻石数量
				$diamonds = $_POST["diamonds"];
				//计算购买金额
				$money = floatval(intval($diamonds) / 6);
				$userdiamond = M("userinfos")->where("userid=$userid")->field("ninemoney")->find();
				$userdiamond = $userdiamond["ninemoney"];
				$usernine = M("userinfos")->where("userid=$userid")->setField("ninemoney", intval($diamonds) + intval($userdiamond));
				if ($usernine) {
					//购买成功后增加充值记录
					$ww['userid'] = $userid;
					$ww['totalprice'] = $money;
					$ww['realprice'] = $money;
					$ww['paystatus'] = 2;
					$ww['specialdes'] = "购买" . $diamonds . "个钻石数";
					$ww['paymentby'] = 4; //苹果内购
					$ww['launchfrom'] = 1; //mobile
					$ww['userid'] = $userid;
					$ww['orderstatus'] = 1;
					$cc = M("recharge")->add($ww);
					return $this->myApiPrint("success", "200");
				} else {
					return $this->myApiPrint("error", "300");
				}
				echo '验证成功';
			}else{
				echo '验证失败'.$response->{'status'};
			}
            
        } else {
            $this->myApiPrint('accesstoken don\'t find', 404);
        }
    }
	
	//curl请求苹果app_store验证地址
	public function http_post_data($url, $data_string) {
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL, $url);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle,CURLOPT_HEADER, 0);
		curl_setopt($curl_handle,CURLOPT_POST, true);
		curl_setopt($curl_handle,CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curl_handle,CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, 0);
		$response_json =curl_exec($curl_handle);
		$response =json_decode($response_json);
		curl_close($curl_handle);
		return $response;
	}

}
