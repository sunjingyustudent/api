<?php
/**
 * 咨询公开接口API
 * 1.登录、
 * 2.注册
 * 3.忘记密码
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;


class PostsController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }


    /*
     * 获取前四条头条新闻 banner图
     * 20160824 mk
     * http://localhost:8080/jiuducaijingapi/Api/Posts/getHotPost/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce
     * http://api.9dushuju.com/Api/Posts/getHotPost/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce
     * */
    public function getHotPost(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
            //链接redis,每次需要的时候重新链接 做初始化
			$redis = $this->connectredis();
			//\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
            if(0){//如果redis中有数据
                $postsList=$redis->get("poststop4");
                return $this->myApiPrint("success","200",$postsList);
            }else{//不存在redis中
                $udata=M("posts")->where("post_status=1 and recommended=1")->field('post_title,id,smeta,post_author,post_source,post_date,post_excerpt')->limit(4)->select();//审核通过并且是已推荐的前四条
				$mm=array();
				foreach($udata as $key=>$vv)
				{
					//解析图片
					$mj = json_decode($vv['smeta'],true);
					$cc = $mj['thumb'];
					$mm[$key] = array(
                        'id'=>$vv['id'],
						'post_title'=>$vv['post_title'],
						'post_author'=>$vv['post_author'],
						'post_source'=>$vv['post_source'],
						'post_date'=>$vv['post_date'],
						'post_excerpt'=>$vv['post_excerpt'],
						'smeta'=>$cc,
                    );
				}
                $redis->set("poststop4",json_encode($mm));//保存如redis
                $redis->expire("poststop4",7200);//设置过期时间两个小时
                $aa = $redis->get("poststop4");//保存如redis
                return $this->myApiPrint("success","200",json_decode($aa));
            }

        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    /*
     * 20160825 lwx
     *获取前15条头条 分页查询
     *测试链接：
     * http://localhost:8080/jiuducaijingapi/Api/Posts/getPostsPage/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce
     * http://api.9dushuju.com/Api/Posts/getPostsPage/accesstoken/wht1-token-f8e24f3e565dcd3f3059ebb67d615cce/pageIndex/1
     * */
    public function  getPostsPage(){
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
			$redis = $this->connectredis();
			
            //传参开始
            $pageIndex=$_GET["pageIndex"];
            //传参结束
            //判断是否是第一页
            if($pageIndex==1){
                //判断是否在redis中存在
                if($redis->exists("Posts")){//判断存在
                    $postList=$redis->get("Posts");
                    return $this->myApiPrint("success","200",json_decode($postList));
                }else{//不存在redis中
                    $postList=M("posts")->order('id desc')->field('post_title,id,smeta,post_author,post_source,post_date,post_excerpt')->limit(20)->select();
					$mm=array();
					foreach($postList as $key=>$vv)
					{
						//解析图片
						$mj = json_decode($vv['smeta'],true);
						$cc = $mj['thumb'];
						$mm[$key] = array(
							'id'=>$vv['id'],
							'post_title'=>$vv['post_title'],
							'post_author'=>$vv['post_author'],
							'post_source'=>$vv['post_source'],
							'post_date'=>$vv['post_date'],
							'post_excerpt'=>$vv['post_excerpt'],
							'smeta'=>$cc,
						);
					}
                    $redis->set("Posts",json_encode($mm));//保存如redis
                    $redis->expire("Posts",7200);
                    //返回数据
                    $this->myApiPrint("success","200",json_decode($mm));
                }
            }else{
				$pageCount=20*($pageIndex-1);
				$postList=M("posts")->order('id desc')->field('post_title,id,smeta,post_author,post_source,post_date,post_excerpt')->limit($pageCount,20)->select();
				//->fetchsql(true)
				//echo dump($postList);
				$mm=array();
				foreach($postList as $key=>$vv){
					//解析图片
					$mj = json_decode($vv['smeta'],true);
					$cc = $mj['thumb'];
					$mm[$key] = array(
                        'id'=>$vv['id'],
						'post_title'=>$vv['post_title'],
						'post_author'=>$vv['post_author'],
						'post_source'=>$vv['post_source'],
						'post_date'=>$vv['post_date'],
						'post_excerpt'=>$vv['post_excerpt'],
						'smeta'=>$cc,
                    );
					
				}
				//var_dump(count($mm));
				//return $mm;
                return $this->myApiPrint("success","200",$mm);
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    /*
     * 获取所有的数据
     * http://api.9dushuju.com/Api/Posts/getData/accesstoken/wht1-token-3e744834f8dac1c84184f7fdf1457fb4
     * */
    public function getData(){

        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
			$redis = $this->connectredis();
			
            //判断是否在redis中存在
            if($redis->exists("data")){//判断存在
                $postList=$redis->get("data");
                return $this->myApiPrint("success","200",json_decode($postList));
            }else{//不存在redis中
                $url='http://115.28.6.92:8094/apishu/apiback.asp';
                $html = file_get_contents($url);
                $postList=json_decode($html);
                $redis->set("data",json_encode($postList));//保存如redis
                $redis->expire("data",7200);
                //返回数据
                $this->myApiPrint("success","200",json_encode($postList));
            }
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }

    }
}