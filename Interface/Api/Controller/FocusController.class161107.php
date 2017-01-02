<?php
/**
 * 关注 公开接口API
 * 1.关注列表接口
 * 2.取消关注列表列表接口
 * 验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class FocusController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }


    /*
     * 2016-09-10 lwx
     * 关注
     * */
    public function focus()
    {
        $getfocus = I('get.');
        if(!empty($getfocus))
        {
            $atoken=$getfocus['accesstoken'];
            if($this->checktoken($atoken))
            {
                $data["userid"] = $this->checktoken($atoken);
                $data["roomid"] = $getfocus["roomid"];
                $userrooms = M('userrooms');
                $result= $userrooms->data($data)->add();
                if($result>0){
                    $this->myApiPrint('succes','200');
                }
                else{
                    $this->myApiPrint('数据添加失败','300');
                }
            } else {
                $this->myApiPrint('accesstoken don\'t find','404');
            }
        }
        else
        {
            $this->myApiPrint('data error','300');
        }
    }


    /*
     * 20160825 lwx
     * 取消关注列表接口
     * 测试接口：
     * */
    public function unfocus()
    {
        $getfocus = I('post.');
        if(!empty($getfocus))
        {
            $atoken=$getfocus['accesstoken'];
            if($this->checktoken($atoken))
            {
				//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();
                //\Predis\Autoloader::register();
                //$redis = new \Predis\Client();
                $user=$redis->hgetall($atoken);
                $userid=$user["uid"];
                //根据逗号拆分字符串
                $lines=explode(",",$getfocus["roomid"]);
//                foreach($lines as $item=>$value){
//                    $result=M("userrooms")->where("userid=$userid and roomid=$value")->delete();
//                    var_dump($result);
//                }
//                $where="roomid in($lines) and userid=$userid";
                $result=M("userrooms")->where(array("userid"=>$userid,"roomid"=>array("in",$lines)))->delete();
                if($result){
                    $this->myApiPrint("success","200");
                }else{
                    $this->myApiPrint("error","300");
                }

            } else {
                $this->myApiPrint('accesstoken don\'t find','404');
            }
        }
        else
        {
            $this->myApiPrint('get data is empty','300');
        }


    }


    //查询用户自选列表 mk
    public function usermarket(){
        $token=$_GET["accesstoken"];
		 if(!empty($token))
			{
				//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();
				//\Predis\Autoloader::register();
				//$redis = new \Predis\Client();
				$user=$this->checktoken($token);
				if($user){
					$userid=$redis->hgetall($token);
					$userid=$userid["uid"];
					$result1=M("rooms r")
						->where('r.status = 1 AND r.isdelete = 0 AND ui.usergrade = 1 AND r.roomid in(select roomid from wht_userrooms where userid='.$userid.')')
		//				array('r.status'=>1,'r.isdelete'=>0,'ui.usergrade'=>1,'ui.userid'=>$userid))
						//->join("left join wht_userrooms u on r.roomid=u.roomid ")
						->join("wht_userinfos ui on ui.roomid=r.roomid")
						->field('ui.nickname,ui.userid,r.createtime,r.roomid,r.roomcode,r.roomtitle,r.webcastkeyid,r.wsurl,r.headimage,r.classifyid,r.hits,r.title,r.isrtype,r.domain,r.roomnumber')
						->select();
						
						$result = array();
						foreach($result1 as $key => $vv){
								if($vv['wsurl']){
									$wsurls = 'ws://'.$vv['wsurl'];
								}
								$result[$key] = array(
									"createtime"=>$vv['createtime'],
									"roomid"=>$vv['roomid'],
									"roomcode"=>$vv['roomcode'],
									"roomtitle"=>$vv['roomtitle'],
									"webcastkeyid"=>$vv['webcastkeyid'],
									"headimage"=>$vv['headimage'],
									"classifyid"=>$vv['classifyid'],
									"hits"=>$vv['hits'],
									"wsurl"=>$wsurls,
									"roomnumber"=>$vv['roomnumber'],
									"title"=>$vv['title'],
									"isonly"=>$vv['isonly'],
									"isrtype"=>$vv['isrtype'],
									"status"=>1,
								);
						}
						
					if($result!=null){
						$this->myApiPrint("success","200",$result);
					}else{
						$this->myApiPrint("success","200","0");
					}
				}else{
					//$this->myApiPrint("error","300");
					$this->myApiPrint('accesstoken don\'t find',404);
				}
			}else{
				//$this->myApiPrint("数据传输失败","300");
				$this->myApiPrint('accesstoken don\'t find',404);
			}
    }

    public function usermarket_del(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        $oid=$_GET["oid"];
        if($userid){
            $result=M("optional")->where("oid=$oid")->delete();
            if($result){
                $this->myApiPrint("success","200",$result);
            }else{
                $this->myApiPrint("error","300");
            }
        }
    }

    //全部清空 mk
    public function usermarket_flush(){
        $token=$_GET["accesstoken"];
        $userflag=$this->checktoken($token);
        if($userflag){
            //链接redis,每次需要的时候重新链接 做初始化
			$redis = $this->connectredis();
			//\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
            $user=$redis->hgetall($token);
            $userid=$user["uid"];
            $result=M("userrooms")->where("userid=$userid")->delete();
            if($result){
                $this->myApiPrint("success","200");
            }else{
                $this->myApiPrint("error","300");
            }
        }else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
    }

    //根据用户id和token添加用户关注列表
    public function addUserRooms(){
        $token=$_GET["accesstoken"];
        if($this->checktoken($token)){
			//链接redis,每次需要的时候重新链接 做初始化
			$redis = $this->connectredis();
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();

            $user=$redis->hgetall($token);
            $userid=$user["uid"];
            $roomid=$_GET["roomid"];
            if($userid>0){
                //判断用户是否已经关注了
                $count = M("userrooms")->where(array("userid"=>$userid,"roomid"=>$roomid))->field('urid')->count();
                if($count>0){
                    return $this->myApiPrint("user has concerns","300");
                }
                $data["userid"]=$userid;
                $data["roomid"]=$roomid;
                $result=M("userrooms")->add($data);
                if($result){
                    return $this->myApiPrint("success","200");
                }else{
                    return $this->myApiPrint("error","300");
                }
            }else{
                //return $this->myApiPrint("accesstoken is not exists","300");
				$this->myApiPrint('accesstoken don\'t find',404);
            }
        }else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
    }
}