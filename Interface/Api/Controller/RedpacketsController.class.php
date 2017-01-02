<?php
/**
 * 红包公开接口API
 * 1.收到的红包
 * 2.发出的红包
 * 3.红包被抢详细
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class RedpacketsController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 2016-08-15 根据用户id获取用户所有收到的红包 sjy
     * http://localhost:8080/jiuducaijingwebapi/interface.php/Api/Redpackets/ReceiveRedPackets/accesstoken/{accesstoken}
     * */
    public function ReceiveRedPackets(){
        //链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('post.accesstoken');//获取用户的token
        if($this->checktoken($atoken)){
            $re=$redis ->hgetall($atoken);//添加到redis缓存中
            $where["userid"] =$re["userid"];
            if( $where["userid"]!=0){
            $where["isdelete"]=0;
            $owner = M('recpacketrecord');

            $resn = $owner
                ->where($where)
                ->select();
            if (!$resn) {
                $this->myApiPrint('错误!',300);
            }
            else{
                $msg = 'success';
                $this->myApiPrint($msg,200,$resn);
            }
            }else{
                $this->myApiPrint('error!',300);
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }


    }


    /*
     *
     * 2016-08-15 根据用户id获取用户所有收到的红包 sjy
     * Api/Redpackets/SendRedPackets/accesstoken/{accesstoken}
    */
    public function SendRedPackets(){
       // echo 'hello';
       // exit();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
        $atoken = I('post.accesstoken');//获取用户的token

        if($this->checktoken($atoken)) {
            $re=$redis ->hgetall($atoken);//添加到redis缓存中
            $where["userid"] =$re["userid"];
            if( $where["userid"]!=0){
            $where["isdelete"] = 0;
            $owner = M('redpackets');

            $resn = $owner

                ->where($where)
                ->select();
            if (!$resn) {
                $this->myApiPrint('错误!', 300);
            } else {
                $msg = 'success';
                $this->myApiPrint($msg, 200, $resn);
            }
        }else{
            $this->myApiPrint('错误!', 300);
        }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

   /*
   2016-08-15 根据accesstoken和红包id获取指定红包id的红包详情 sjy
Api/Redpackets/RedPacketsDetail/accesstoken/{accesstoken}/redpacketid/{redpacketid}
   */
    public function RedPacketsDetail(){
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $atoken = I('post.accesstoken');//获取用户的token
        $redpacketid = I('post.redpacketid');
        if($this->checktoken($atoken)) {
            $re=$redis ->hgetall($atoken);//添加到redis缓存中
            $where["userid"] =$re["userid"];
            if( $where["userid"]!=0){
            $where["wht_redpackets.isdelete"] = 0;
            $where["wht_redpackets.redpacketid"] =$redpacketid;
            /* $where["wht_redpackets.redpacketid"]=$redpacketid;
             $where["userid"]=$userid;
             $where["wht_redpackets.isdelete"]=0;*/
            $owner = M('redpackets');
            $resn = $owner
                ->join("left join wht_recpacketrecord as u on u.redpacketid = wht_redpackets.redpacketid ")
                ->field('u.*')
                ->where($where)
                /* ->where($where)*/
                ->select();
            if (!$resn) {

                $this->myApiPrint('error!!', 300);
            } else {
                $msg = 'success';
                $this->myApiPrint($msg, 200, $resn);
            }
            }else{
                $this->myApiPrint('error!!', 300);
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);

        }
    }




}