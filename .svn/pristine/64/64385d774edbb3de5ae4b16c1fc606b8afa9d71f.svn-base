<?php
/**
 * 聊天公开接口API
 * 基本的查询，登录，注册，等
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class ChatRoomController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }
/*
 * 2016-08-18 根据直播视频id 获取直播聊天信息
 * 测试接口：Api/ChatRoom/chatifo/webcastid/1
 * */
    public function chatifo(){

        $webcastid = I('get.webcastid');

        $isdelete = 0;
        $owner = M('webcastmessage');

        $resn = $owner
            ->where("webcastid='%d'",$webcastid)
            ->select();
        if (!$resn) {
            $this->myApiPrint('错误!',300);
        }
        else{
            $msg = 'success';
            $this->myApiPrint($msg,200,$resn);
        }

    }

    /*
 * 2016-08-18 根据房间id 获取房间公告
 * 测试接口：Api/Chatroom/roomnotice/roomid/1
 * */
    public function roomnotice(){
        $roomid = I('get.roomid');

        $isdelete = 0;
        $owner = M('rooms');

        $resn = $owner
            ->where("roomid='%d'",$roomid)
            ->field("roomnotice")
            ->find();
        if (!$resn) {
            $this->myApiPrint('错误!',300);
        }
        else{
            $msg = 'success';
            $this->myApiPrint($msg,200,$resn);
        }
    }


}
