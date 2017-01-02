<?php
/**
 * 七牛直播公开接口API
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;
use Think\Exception;
use Think\Upload\Driver\Qiniu;

//require(join(DIRECTORY_SEPARATOR, array(dirname(dirname(__FILE__)), 'lib', 'Pili.php')));
class QiNiuLiveController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
        vendor('Pili');
    }

    public function createOrGetStream(){
        $token=$_GET["accesstoken"];
        if($token!=null||$token!=""){
            $userid = $this->checktoken($token);
        }
        if($userid){
        $classifyid=$_GET["classifyid"];
        $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
        $hub = new \Pili\Hub($credentials, HUB); # => Hub Object

        //鉴权生成

        $Key="NEW-JD-".time().$userid;

        $roomid=M("userinfos")->where("userid=$userid")->field("roomid")->select();
        try {
            if($roomid){
                $result=M("rooms r")->join("wht_userinfos u on r.roomid=u.roomid")->where("u.userid=$userid")->field("r.webcastkeyid")->find();


                if($result["webcastkeyid"]==null){
                    $this->createStream($hub,$classifyid,$userid,$Key);
                }else{
                    $stream = $hub->getStream($result["webcastkeyid"]);
                    $streamjson=$stream->toJSONString();
                    echo "{\"code\":\"200\",\"msg\":\"success\",\"result\":$streamjson}";
//                    echo $stream->toJSONString();
//                    return $this->myApiPrint("success","200",$stream->toJSONString());
                }


            }else{
                $this->createStream($hub,$classifyid,$userid,$Key);



            }
        } catch (Exception $e) {
            return $this->myApiPrint("error","300");
        }

    }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    //新增流并且加入数据库
    public function createStream($hub,$classifyid,$userid,$Key)
    {
        $roomcod=time().$userid;
        $title = $roomcod;     // optional, auto-generated as default 一般对应主播房间号
        $publishKey = $Key;     // optional, auto-generated as default 流密钥，用于生成推流鉴权凭证
        $publishSecurity ="static";     // optional, can be "dynamic" or "static", "dynamic" as default 推流鉴权策略, 一般为"static", 针对安全要求较高的UGC推流建议用"dynamic"
        $stream = $hub->createStream($title, $publishKey, $publishSecurity); # => Stream Object
        //房间名
        $data["roomcode"] = $roomcod;
        $data["webcastkeyid"] = $stream->id;
        $data["authentication"] = $publishKey;
        $data["classifyid"] = $classifyid;
        $data["authentication"]=$Key;
        $data["streamname"]=$roomcod;
        $roomid = M("rooms")->add($data);
        if ($roomid) {
            $id = M("userinfos")->where("userid=$userid")->setField("roomid", $roomid);
            return $this->myApiPrint("success", "200");
        }else{
            return $this->myApiPrint("success", "300");
        }

    }


    //获取流列表
//    public function getStreamList(){
//        $token=I("get.accesstoken");
//        if($token!=null||$token!=""){
//            $userid = $this->checktoken($token);
//        }
//        if($userid) {
//            try {
//                $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
//                $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
//
//                $marker = NULL;      // optional
//                $limit = NULL;      // optional
//                $title_prefix = NULL;      // optional
//                $status = "connected";      // optional, "connected" only
//
//                $result = $hub->listStreams($marker, $limit, $title_prefix, $status); # => Array
//
////            var_dump($result);
//                $array = array();
//                foreach ($result["items"] as $key => $value) {
//                    $data = json_decode($result["items"][$key]->toJSONString());
//                    $array[] = $data;
//                }
//
//                $this->myApiPrint("success", "200", $array);
////
//            } catch (Exception $e) {
//                return $this->myApiPrint("error", "300");
//            }
//        }else{
//            return $this->myApiPrint("error", "300");
//        }
//    }


    //更新流
    public function updateStream(){
        $token=I("get.accesstoken");
        $userid=$this->checktoken($token);
        if($userid){
        $disabled=$_GET["disabled"];
        try {
            $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
            $hub = new \Pili\Hub($credentials, HUB); # => Hub Object

            $result=M("rooms r")->join("wht_userinfos u on r.roomid=u.roomid")->where("u.userid=$userid")->field("r.webcastkeyid")->find();
            $stream = $hub->getStream($result["webcastkeyid"]);

            $stream->publishKey      = $stream["publishKey"]; // optional
            $stream->publishSecurity = "static";           // optional, can be "dynamic" or "static"
            $stream->disabled        = $disabled;               // optional, can be "true" of "false"

            $stream = $stream->update(); # => Stream Object


            var_export($stream->toJSONString());


        } catch (Exception $e) {
            echo "Stream update() failed. Caught exception: ",  $e->getMessage(), "\n";
        }
    }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    //启用/禁用流
    public function enableStream()
    {
        $token = I("get.accesstoken");
        if($token!=null||$token!=""){
            $userid = $this->checktoken($token);
        }
        if ($userid) {
            $status = $_GET["status"];

            $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
            $hub = new \Pili\Hub($credentials, HUB); # => Hub Object

            $streamid = M("userinfos u")->join("wht_rooms r on r.roomid=u.roomid")->where("u.userid=$userid")->field("r.webcastkeyid")->find();
            $stream = $hub->getStream($streamid);
            //0为启用，1位禁用
            try {
                if ($status == 1) {
                    $disabledTill = time() + 10; # disabled in 10s from now
                    $result = $stream->disable($disabledTill); # => NULL

                    var_export($result);
                    return $this->myApiPrint("success", "200");
                } else {
                    $disabledTill = time() + 10; # disabled in 10s from now
                    $result = $stream->enable($disabledTill); # => NULL

                    var_export($result);
                    return $this->myApiPrint("success", "200");
                }
            } catch (Exception $e) {
                return $this->myApiPrint("error", "300");
            }

        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    //转换成历史记录视频
    public function getAnchorStatus(){
        $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
        $hub = new \Pili\Hub($credentials, HUB); # => Hub Object

        $stream=$hub->getStream("z1.9dcj1.JDCF14733986781");

        $result = $stream->status();
        var_dump($result);

    }


    //录播视频
//    public function saveStream()
//    {
//        $token =$_GET["accesstoken"];
//        if($token!=null||$token!=""){
//            $userid = $this->checktoken($token);
//        }
//        if ($userid) {
//            try {
//                $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
//                $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
//
//                $streamid = M("userinfos u")->join("wht_rooms r on r.roomid=u.roomid")->where("u.userid=$userid")->field("r.webcastkeyid")->find();
//                //$stream = $hub->getStream($streamid["webcastkeyid"]); # => Stream Object
//                $stream = $hub->getStream("z1.9dcj1.57b2dd3f75b6253fb2015e24"); # => Stream Object
//
//                $result=$stream->segments();
//
//                $array=array();
//                foreach($result["segments"] as $key=>$value){
//                   // $array[]=$result["segments"];
//                }
//
//                var_dump($stream);
//
//                $strattime=1471575417;
//
//                $endtime=1471576307;
//
////                $strattime=null;
////
////                $endtime=null;
//
//                $name = 'videoName.mp4'; // required
//                $format = NULL;            // optional
//                $start = $strattime;              // optional, in second, unix timestamp
//                $end = $endtime;              // optional, in second, unix timestamp
//                $notifyUrl = NULL;            // optional
//                $pipeline = NULL;            // optional
//
//                $result = $stream->saveAs($name, $format, $start, $end, $notifyUrl, $pipeline); # => Array
//                var_dump($result);
//                exit();
//                $jsonString = "{\"url\":\"$result[url]\",\"duration\":\"$result[duration]\",\"persistentId\":\"$result[persistentId]\"}";
//                return "{\"code\":\"200\",\"msg\":\"success\",\"result\":\"$jsonString\"}";
//
//            } catch (Exception $e) {
//                return $this->myApiPrint("error", "300");
//            }
//        }else{
//            return $this->myApiPrint("error","300");
//        }
//    }

    //删除流接口
    public function delStream(){
        $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
        $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
        $stream=$hub->getStream("z1.9dcj1.1474266804143");//替换成流的id，最后一组数字替换掉即可
        $stream->delete();
    }
}