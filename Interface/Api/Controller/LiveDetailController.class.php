<?php
/**
 * 直播详细公开接口API
 * 基本的查询，登录，注册，等
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class LiveDetailController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
        vendor("Pili");
    }

	//七牛
    //根据房间id获取流并返还
    public function getvideos(){
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $token=I("get.accesstoken");
        $roomid=I("get.roomid");
        if($this->checktoken($token)){
            $re=$redis->hgetall($token);//添加到redis缓存中
            $uid = $re["uid"];

            if($uid>0) {
                //判断当前用户是否已经关注此房间

                $count = M("userrooms")->where(array("userid"=>$uid,"roomid"=>$roomid))->field('urid')->count();
                if($count>0){
                    $focus = 1;//用户已关注
                }else{
                    $focus = 0;//用户未关注
                }
            }else{
                $focus = 2;//当前是游客
            }
            $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
            $hub = new \Pili\Hub($credentials, HUB); # => Hub Object


            $userinfo=M("userinfos u")->join("wht_rooms r on u.roomid=r.roomid")->where("u.roomid=$roomid")->field("u.nickname,u.headimage")->find();

            if($roomid!=null){
                $streamid=M("rooms")->where("roomid=$roomid")->field("webcastkeyid")->find();
                $stream=$hub->getStream($streamid["webcastkeyid"]);
//                $result = array(
//                    "stream"=>$stream->toJSONString(),
//                     "foucus"=>$focus,
//                    "nickname"=>$userinfo["nickname"],
//                    "headimage"=>$userinfo["headimage"]
//                );

                $nickname=$userinfo["nickname"];
                $headimage=$userinfo["headimage"];
                $status=$userinfo["status"];
//                $retrunStr="{\"code\":\"200\",\"msg\":\"success\",\"result\":\"{\"stream\":\"".$stream->toJSONString()."\",\"nickname\":\"".$userinfo["nickname"]."\",\"headimage\",\"".$userinfo["headimage"]."\",\"foucus\":\"$focus\"}";
                $retrunStr="{\"code\":\"200\",\"msg\":\"success\",\"result\":".$stream->toJSONString().",\"nickname\":\"$nickname\",\"headimage\":\"$headimage\",\"foucus\":\"$focus\"}";
                echo $retrunStr;
//                return $this->myApiPrint("success","200",$result);
            }else{
                return $this->myApiPrint("error","300");
            }

        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }
	
	
	//展示的
	//根据房间id获取流并返还
    public function zsgetvideos(){
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $token=I("get.accesstoken");
        $roomid=I("get.roomid");
        if($this->checktoken($token)){
            $re=$redis->hgetall($token);//获取redis数据
            $uid = $re["uid"];
            if($uid>0) {
                //判断当前用户是否已经关注此房间
                $count = M("userrooms")->where(array("userid"=>$uid,"roomid"=>$roomid))->field('urid')->count();
                if($count>0){
                    $focus = 1;//用户已关注
                }else{
                    $focus = 0;//用户未关注
                }
            }else{
                $focus = 2;//当前是游客
            }
            $userinfo=M("userinfos u")->join("wht_rooms r on u.roomid=r.roomid")->where("u.roomid=$roomid")->field("u.nickname,u.headimage,u.username,r.domain,r.roomnumber,r.wsurl")->find();
            //var_dump($userinfo);
			if($roomid!=null){
				//var_dump($userinfo['domain']);
				$result = array(
                   "domain"=>$userinfo["domain"],
                   "roomumber"=>$userinfo["roomnumber"],
                   "nickname"=>$userinfo["nickname"],
				   "username"=>$userinfo["username"],
				   "status"=>$userinfo["status"],
                   "headimage"=>$userinfo["headimage"],
				   "wsurl"=>$userinfo["wsurl"],
				   "foucus"=>$focus
               );
				$this->myApiPrint('success',200,$result);
            }else{
                return $this->myApiPrint("error","300");
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    //根据房间id获取房间信息
    public function getroom(){
        $token=I("get.accesstoken");
        $userid=$this->checktoken($token);
        if($userid!=false){
            $roomid=$_GET["roomid"];
            $model=M("rooms")->where("roomid=$roomid")->find();
            if($model){
                return $this->myApiPrint("success","200",$model);
            }else{
                return $this->myApiPrint("error","300");
            }
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    //获取个人主播的个人信息
//    public function getperson(){
//
//        $token = I("get.accesstoken");
//        $userid = $this->checktoken($token);
//        if (!empty($token)) {
//            if ($userid != false) {
//                $model = M("userinfos")->where("userid=$userid")->find();
//                if ($model) {
//                    return $this->myApiPrint("success", "200", $model);
//                } else {
//                    return $this->myApiPrint("error", "300");
//                }
//            } else {
//                return $this->myApiPrint("error", "300");
//            }
//        } else {
//            return $this->myApiPrint("error", "300");
//        }

//    }
}