<?php
/**
 * 活动公开接口API
 * 1.活动页面接口
 * 2.参与活动记录数据接口
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class ActivityController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    //根据用户id和token获取用户关注列表
    public function getUserRoomsList(){
        $token=$_GET["token"];
        $userid=$_GET["userid"];
        $userRooms=M("userrooms r")
            ->join("wht_userinfos u on r.userid=u.userid")
            ->join("wht_rooms o on r.roomid=o.roomid")
            ->join("wht_classify c on c.classifyid=o.classifyid")
            ->where("r.userid=$userid")
            ->field("o.roomcode,o.roomtitle,c.classifyname")
            ->select();
        return $this->myApiPrint("success","200",$userRooms);
    }

    //根据用户id和token取消用户关注列表
    public function delUserRooms(){
        $token=$_GET["token"];
        $userid=$_GET["userid"];
        $roomid=$_GET["roomid"];
        $result=M("userrooms")->where("userid=$userid and roomid=$roomid")->delete();
        if($result){
            return $this->myApiPrint("success","200");
        }else{
            return $this->myApiPrint("error","300");
        }
    }

    //根据用户id和token添加用户关注列表
    public function addUserRooms(){
        $token=$_GET["token"];
        $userid=$_GET["userid"];
        $roomid=$_GET["roomid"];

        $data["userid"]=$userid;
        $data["roomid"]=$roomid;

        $result=M("userrooms")->add($data);
        if($result){
            return $this->myApiPrint("success","200");
        }else{
            return $this->myApiPrint("error","300");
        }
    }
}