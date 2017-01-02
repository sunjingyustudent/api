<?php
/**
 * 视学堂公开接口API
 * 1.获取分类
 * 2.
 * 3.忘记密码
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;


class VideoController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 20160825 lwx
     * 获取视学堂分类
     * 测试链接：http://localhost:8080/jiuducaijingapi/Api/Video/getcattypeid/accesstoken/wht1-token-5fbf3a297f454b203f22929d014657f0
     * http://api.9dushuju.com/Api/Video/getcattypeid/accesstoken/wht1-token-5fbf3a297f454b203f22929d014657f0
     * */
    public function getcattypeid(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
//        var_dump($userid);
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();

            if($redis->exists("catlist")){//如果redis中有数据
                $postsList=$redis->get("catlist");
                return $this->myApiPrint("success","200",json_decode($postsList));
            }else{//不存在redis中
                $postList=M("category")->where("isdelete=0 and parentid=3")->order("sort asc")->field("catid,catname,url")->select();//审核通过并且是已推荐的前四条
//                var_dump($postList);
//                exit();;
                $redis->set("catlist",json_encode($postList));//保存如redis
                $redis->expire("catlist",604800);//设置过期时间168个小时
                return $this->myApiPrint("success","200",$postList);
            }
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    /*
     * 20160825 lwx
     * 获取前20条视频学堂数据
     *分页查询
     * 测试链接：http://localhost:8080/jiuducaijingapi/Api/Video/getvideospage/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce/p/1/catid/3
     * http://api.9dushuju.com/Api/Video/getvideospage/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce/p/1/catid/3
     * */
    public function  getvideospage(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
            //链接redis,每次需要的时候重新链接 做初始化
            $redis = $this->connectredis();
            $model = D("videos");
            $catid = $_GET["catid"];
            $where = array();
            $where["isdelete"] = 0;
            $where["post_status"] = 1;
            if($catid) $where["catid"] = $catid;
            $postList=$redis->get("videos_".$catid);
            if($redis->exists("videos_".$catid) && $postList && $_GET["pageIndex"] == 1){//判断存在
                return $this->myApiPrint("success","200",json_decode($postList));
            }
            $count = $model->where($where)->where($where)->count();
            $page = new \Think\MyPage($count, 5,array($_GET["pageIndex"]));
            $postList = $model->where($where)->field("createtime,nid,title,source,thumb,keywords,description,vurl,sort,clickcount,videourl")->where($where)->limit($page->firstRow.",".$page->listRows)->order("nid DESC")->select();
            
            if(empty($_GET["pageIndex"]) || $_GET["pageIndex"] == 1){
                // 判断是第一页才缓存
                $redis->set("videos_".$catid,json_encode($postList));//保存如redis
                $redis->expire("videos_".$catid,7200);
            }
            //返回数据
            $this->myApiPrint("success","200",$postList);
            
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    /*
     * 视学堂详细页
     *http://api.9dushuju.com/Api/Video/getvideodetail/accesstoken/wht1-token-5fbf3a297f454b203f22929d014657f0/nid/1
     * */
    public function getvideodetail(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();

            $re=$redis ->hgetall($token);//添加到redis缓存中
            $uid = $re["uid"];

            $nid = $_GET["nid"];
            $where["nid"]=$nid;
            $where["isdelete"]=0;
            $video=M("videos");
            $result1=$video
                ->where($where)
                ->field("createtime,title,description,vurl,clickcount,vdesc,keywords")
                ->select();
            if($uid>0){
                $acount = M("uservideos")->where(array("videoid"=>$nid,"userid"=>$uid))->field('uvid')->find();
                if($acount>0){
                    $isfavorite = 1;//表示已收藏
                }else{
                    $isfavorite = 0;//表示未收藏
                }
            }else{
                $isfavorite=0;//表示未收藏
            }

            $keywords=$result1[0]["keywords"];
            $whe['keywords']=array('like',$keywords);
            $videos=$video->where($whe)->field("createtime,title,nid,thumb,description,vurl,clickcount,vdesc")->limit(2)->select();

            
             $count=count($videos);
                       
            for($i=0;$i<=$count-1;$i++){
                $videos[$i]["createtime"]="{$videos[$i]["createtime"]}";
                $videos[$i]["title"]="{$videos[$i]["title"]}";
                $videos[$i]["nid"]="{$videos[$i]["nid"]}";
                $videos[$i]["thumb"]="{$videos[$i]["thumb"]}";
                $videos[$i]["description"]="{$videos[$i]["description"]}";
                $videos[$i]["vurl"]="{$videos[$i]["vurl"]}";
                 $videos[$i]["clickcount"]="{$videos[$i]["clickcount"]}";
                   $videos[$i]["vdesc"]="{$videos[$i]["vdesc"]}";
                
            }
            
            
            $result = array(
                 'createtime'=>"{$result1[0]["createtime"]}",
                'title'=>"{$result1[0]["title"]}",
                'description'=>"{$result1[0]["description"]}",
                'vurl'=>"{$result1[0]["vurl"]}",
                'isfavorite'=>"{$isfavorite}",
                'clickcount'=>"{$result1[0]['clickcount']}",
                'vdesc'=>"{$result1[0]['vdesc']}",
				'nid'=>"{$result1[0]['nid']}",
				'thumb'=>"{$result1[0]['thumb']}",
                'videos'=>$videos
            );
            if($result){
                $this->myApiPrint("success","200",$result);
            }else{
                $this->myApiPrint("error","300");
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    /*
     * 视学堂
     * 取消关注收藏这个视频
     *http://api.9dushuju.com/Api/Video/unfavoritevideo/accesstoken/wht1-token-5fbf3a297f454b203f22929d014657f0/nid/1
     * */
    public function unfavoritevideo(){
        $token=$_GET["accesstoken"];//获取用户token
       
        $userid=$this->checktoken($token);//检查用户token
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();
			
            $re=$redis ->hgetall($token);//从redis中获取token信息

            $uid = $re["uid"];

            $nid = $_GET["nid"];
            $where["videoid"]=$nid;
            $where["userid"]=$uid;
            $video=M("uservideos");
            if($uid>0){
                $info = $video->where($where)->select();

                if(!$info){
                    $this->myApiPrint('collect not exist',300);
                }

                $info2 = $video->where($where)->delete();

                if($info2>0) {
                    $this->myApiPrint('success',200);
                }
                else{
                    $this->myApiPrint('delte error',300);
                }
            }else{
                $this->myApiPrint('not logged on user',300);
            }
            }else{
                $this->myApiPrint('accesstoken don\'t find',404);
            }
    }




    /*
     * 视学堂
     * 收藏这个视频
     *http://api.9dushuju.com/Api/Video/favoritevideo/accesstoken/wht1-token-5fbf3a297f454b203f22929d014657f0/nid/1
     * */
    public function favoritevideo(){
        $token=$_GET["accesstoken"];//获取用户token
       // var_dump($token);
        $userid=$this->checktoken($token);//验证token是否存在
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();
            $re=$redis ->hgetall($token);//获取token的信息
            $uid = $re["uid"];//获取用户id
          //  var_dump($uid);
            $nid = $_GET["nid"];//获取要收藏的视频id
            //var_dump($nid);
            $where["videoid"]=$nid;
            $where["userid"]=$uid;
            $video=M("uservideos");


            if($uid>0){

                $videoexist=$video
                    ->where($where)
                    ->find();
                if($videoexist){
                    $this->myApiPrint('collect exist',300);
                }

              //  var_dump($videoexist);

                $info2 = $video->data($where)->add();
                //var_dump($info2);
                if($info2>0) {
                    $this->myApiPrint('add success',200);
                }
                else{
                    $this->myApiPrint('add error',300);
                }
            }else{
                $this->myApiPrint('not logged on user',300);
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }




}