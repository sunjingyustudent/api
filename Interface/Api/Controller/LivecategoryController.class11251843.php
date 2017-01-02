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


class LivecategoryController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
        vendor("Pili");
    }

    /*
     * 直播分类  redis读取数据
     * 测试接口：http://localhost:8080/jiuducaijingwebapi/Api/Livecategory/categorylist/accesstoken
     * */
    
    public function categorylist()
    {
//        $token=$_GET["accesstoken"];
//        $userid=$this->checktoken($token);
//        if($userid){
//            $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
//            $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
//            $videoList=M("rooms")->where("status=1")->field("webcastkeyid")->select();
//            $array=array();
//            foreach($videoList as $item=>$value){
//                $streamid=$videoList[$item]["webcastkeyid"];
//                $array[]=$hub->getStream($streamid)->toJSONString();
//            }
//            return $this->myApiPrint("success","200",$array);
//        }else{
//            $this->myApiPrint("error","300");
//        }


        //保存到redis里
        $sitename=C('SITENAME');
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        if($redis->exists($sitename.'category'))
        {
            $categorylist = $redis->get($sitename.'category');
            if($categorylist) {
                $this->myApiPrint("success",200,unserialize($categorylist));
            }
            else{
                $this->myApiPrint('redis error',300);
            }
        }
        else
        {
            //查询mysql数据库缓存到redis里
            $va = M('classify')
                ->where(array('isenable'=>'0','isdelete'=>'0'))
                ->order('sort asc')
                ->field('classifyid,classifyname,sort')
                ->select();
            if($va)
            {
                $redis->set($sitename.'category',serialize($va));//添加到redis缓存中
                $retval = $redis->get($sitename.'category');
                if($redis -> exists ($sitename.'category')){
                    $this->myApiPrint('success',200, unserialize($retval));
                }else{
                    $this->myApiPrint('redis  error ','300');
                }
            }
            else {
                $this->myApiPrint('redis error',300);
            }
        }
    }

    //通过传值判断当前是否开始直播
    public function getVideosStatu(){
        $token=$_GET["accesstoken"];
        $user=$this->checktoken($token);
        if($user){
            //开启直播拉取流
            $title=$_GET["title"];
            $type=$_GET["type"];

            $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
            $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
            $roomid=$_GET["roomid"];

            if($roomid!=null&&$roomid>0){
                $streamid=M("rooms")->where("roomid=$roomid")->field("webcastkeyid")->find();
                $stream=$hub->getStream($streamid["webcastkeyid"]);
                $streamjson=$stream->toJSONString();

                $userinfo=M("userinfos u")->join("wht_rooms r on u.roomid=r.roomid")->where("u.roomid=$roomid")->field("u.nickname,u.headimage")->find();
                $nickname=$userinfo["nickname"];
                $headimage=$userinfo["headimage"];

                $result=M("rooms")->where("roomid=$roomid")->setField(array("classifyid"=>$type,"roomtitle"=>$title));
                echo "{\"code\":\"200\",\"msg\":\"success\",\"result\":$streamjson,\"nickname\":\"$nickname\",\"headimage\":\"$headimage\"}";
            }else{
                return $this->myApiPrint("er","300");
            }
        }else{
            return $this->myApiPrint("token is error","300");
        }
    }


    /*
     * 根据直播分类获取所有分类下的直播列表
     * 测试接口：
     * */
    public function webcastlist()
    {
        $token=$_GET["accesstoken"];
        $userid=$this->checktoken($token);
        if($userid){
//            $classifyid = I('get.typeid');
//            $resn =M("rooms")->where("classifyid=$classifyid")->select();
//            if ($resn) {
//                $this->myApiPrint("error","200",$resn);
//            }
//            else{
//                $credentials = new \Qiniu\Credentials(ACCESS_KEY, SECRET_KEY); #=> Credentials Object
//                $hub = new \Pili\Hub($credentials, HUB); # => Hub Object
//                $array=array();
//                foreach($resn as $item=>$value){
//                    $streamJson=$hub->getStream($resn["webcastkeyid"])->toJSONString();
//                    $array[]=$streamJson;
//                }
//                $msg = 'success';
//                $this->myApiPrint('error',"300");
//            }
//        }else{
//            return $this->myApiPrint("error","300");



//            $token=$_GET["accesstoken"];
//            $user=$this->checktoken($token);
//            if($userid){
                $istype=$_GET["typeid"];
                //0为热门，1为股市,2为期货，3为贵金属，4为生活
                if($istype==0){
                    $live=M("livehall")->where("livetype=0")->select();
                }
                if($istype==1){
                    $live=M("livehall")->where("livetype=1")->select();
                }
                if($istype==2){
                    $live=M("livehall")->where("livetype=2")->select();
                }
                if($istype==3){
                    $live=M("livehall")->where("livetype=3")->select();
                }
                if($istype==4){
                    $live=M("livehall")->where("livetype=4")->select();
                }
                $this->myApiPrint("success","200",$live);
            }else{
                $this->myApiPrint('accesstoken don\'t find',404);
            }
//        }
    }

    /*
     * 所有的直播视频列表 redis读取数据【分页】
     * 这个是第一次存取比如40条直播数据在redis里，之后的分页数据链接mysql读取
     * 测试接口：
     * */
    public function allwebcasttest()
    {
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $token=$_GET["accesstoken"];

        $userflag=$this->checktoken($token);
        if($userflag){
            $type=$_GET["type"];
            $pageIndex=$_GET["pageIndex"];
			//周日
			$week = date("w");
			$date=date('H:i');
            if($pageIndex=="1"){
			$recomsult=M()->query('SELECT ut.utid,ut.roomid,r.createtime,r.roomtitle,r.speaker,r.title,r.hits,r.roomcode,r.webcastkeyid,r.wsurl,r.roomnumber,r.isonly,r.isrtype,r.classifyid,ut.teachname,ut.teacherimg,ut.tlabel,c.classifyname FROM wht_userteacher AS ut LEFT JOIN wht_rooms AS r ON r.roomid = ut.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where ttype=2 and r.classifyid='.$type.' limit 20');
				//echo M()->getLastSql();
				$result = array();
				$result1 = array();//zhibo
                $result2 = array();//weizhibo
				foreach ($recomsult as $key => $value) {
					//查询老师的今日节目单，根据当前的时间端判断老师是否在直播中
					$aa = M('programmenu')->field('progname,progtime')->where(array('weekday'=>$week,'teacherid'=>$value['utid']))->select();
					//echo dump($aa);
					$status =2;
					$progname = '';
					foreach($aa as $key1 => $vv){
						//echo dump($vv);
						if($vv['progtime']){
							//获取当前时间和周几
							$start=substr($vv['progtime'],0,5);
							$end=substr($vv['progtime'],-5);
							if($date>=$start&&$date<=$end){
								$status=1;
								$progname = $vv['progname'];
								//var_dump($status);
								break;
							}else{
								continue;//跳出本次循环,继续执行下一次循环 
							}
						}else{
							break;
						}
					}
					if($value['wsurl']){
						$wsurls = 'ws://'.$value['wsurl'];
					}
					if($status==1){
				$result1[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }else{
                            
                            $result2[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }
				}
				shuffle($result1);
        shuffle($result2);
       $result=array_merge($result1,$result2);
				$this->myApiPrint("success","200",$result);
			}else{
				$pageCount=20*($pageIndex-1);
				$recomsult=M()->query('SELECT ut.utid,ut.roomid,r.createtime,r.roomtitle,r.speaker,r.title,r.hits,r.roomcode,r.webcastkeyid,r.wsurl,r.roomnumber,r.isonly,r.isrtype,r.classifyid,ut.teachname,ut.teacherimg,ut.tlabel,c.classifyname FROM wht_userteacher AS ut LEFT JOIN wht_rooms AS r ON r.roomid = ut.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where ttype=2 and r.classifyid='.$type.' LIMIT '.$pageCount.',20');
				//echo M()->getLastSql();
				//dump(count($recomsult));
				$result = array();
				$result1 = array();//zhibo
                $result2 = array();//weizhibo
				if(count($recomsult)!=0){
					foreach ($recomsult as $key => $value) {
					//查询老师的今日节目单，根据当前的时间端判断老师是否在直播中
					$aa = M('programmenu')->field('progname,progtime')->where(array('weekday'=>$week,'teacherid'=>$value['utid']))->select();
					//echo dump($aa);
					$status =2;
					$progname = '';
					foreach($aa as $key1 => $vv){
						//echo dump($vv);
						if($vv['progtime']){
							//获取当前时间和周几
							$start=substr($vv['progtime'],0,5);
							$end=substr($vv['progtime'],-5);
							if($date>=$start&&$date<=$end){
								$status=1;
								$progname = $vv['progname'];
								//var_dump($status);
								break;
							}else{
								continue;//跳出本次循环,继续执行下一次循环 
							}
						}else{
							break;
						}
					}
				}
				if($value['wsurl']){
					$wsurls = 'ws://'.$value['wsurl'];
				}
				if($status==1){
				$result1[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }else{
                            
                            $result2[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }
				}
			}
			shuffle($result1);
        shuffle($result2);
       $result=array_merge($result1,$result2);
		$this->myApiPrint("success","200",$result);
		}else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
        
    }

     public function allwebcast()
    {
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $token=$_GET["accesstoken"];

        $userflag=$this->checktoken($token);
        if($userflag){
            $type=$_GET["type"];
            $pageIndex=$_GET["pageIndex"];
			//周日
			$week = date("w");
			$date=date('H:i');
          
			$recomsult=M()->query('SELECT ut.utid,ut.roomid,r.createtime,r.roomtitle,r.speaker,r.title,r.hits,r.roomcode,r.webcastkeyid,r.wsurl,r.roomnumber,r.isonly,r.isrtype,r.classifyid,ut.teachname,ut.teacherimg,ut.tlabel,c.classifyname FROM wht_userteacher AS ut LEFT JOIN wht_rooms AS r ON r.roomid = ut.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where ttype=2 and r.classifyid='.$type);
			
                        
                        $count=count($recomsult);
                        
                       //echo M()->getLastSql();
				$result = array();
				$result1 = array();//zhibo
                               $result2 = array();//weizhibo
                               
                               
				foreach ($recomsult as $key => $value) {
					//查询老师的今日节目单，根据当前的时间端判断老师是否在直播中
					$aa = M('programmenu')->field('progname,progtime')->where(array('weekday'=>$week,'teacherid'=>$value['utid']))->select();
					//echo dump($aa);
					$status =2;
					$progname = '';
					foreach($aa as $key1 => $vv){
						//echo dump($vv);
						if($vv['progtime']){
							//获取当前时间和周几
							$start=substr($vv['progtime'],0,5);
							$end=substr($vv['progtime'],-5);
							if($date>=$start&&$date<=$end){
								$status=1;
								$progname = $vv['progname'];
								//var_dump($status);
								break;
							}else{
								continue;//跳出本次循环,继续执行下一次循环 
							}
						}else{
							break;
						}
					}
					if($value['wsurl']){
						$wsurls = 'ws://'.$value['wsurl'];
					}
					if($status==1){
				$result1[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }else{
                            
                            $result2[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }
				}
				shuffle($result1);
                                shuffle($result2);
                                
                                 $result=array_merge($result1,$result2);
                             
                                 
                                 $start=0;
                                 
                                  $list=array();
                                
                                 if($pageIndex==1){
                                     if($count<20){
                                          $start=0;
                                     $end=$count;
                                     }else{
                                     $start=0;
                                     $end=20;
                                     } 
                                 }else{
                                     
                                     if($count<($pageIndex-1)*20){
                                       $start=$count;
                                     $end=$count;
                                     }else{
                                         if($count<$pageIndex*20){
                                              $start=($pageIndex-1)*20;
                                              $end=$count;
                                         }else{
                                              $start= ($pageIndex-1)*20;
                                    $end=$pageIndex*20;
                                         }
                                     }
                                     
                                   
                                    
                                 }
                                 
                                
                                 $ic=0;
                                 for($i=$start;$i<=$end-1;$i++){
                                     $list[$ic]=$result[$i]!=null?$result[$i]:"";
                                     $ic+=1;
                                 }
                                 
                                 
                                
//                                $countre1=count($result1);
//                                 $countre2=count($result2);
//                                 
//                                $shengyu=array();
//                                
//                                $count=$pageIndex*20;
//                                
//                                
//                                if($countre1<20){
//                                    $shengyucount=20-$countre1;
//                                    if($countre2<$shengyucount){
//                                        
//                                        $result=array_merge($result1,$result2);
//                                    }else{
//                                    for($i=0;$i<$shengyucount;$i++){
//                                        $shengyu[$i]=$result2[$i];
//                                    }
//                                     $result=array_merge($result1,$shengyu);
//                                    }
//                                }
//                   
                                
                                 
       
                             
                              
				$this->myApiPrint("success","200",$list);
			}
                        
                        else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
        
    }
    
    
       public function allwebcastlist()
    {
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $token=$_GET["accesstoken"];

        $userflag=$this->checktoken($token);
        if($userflag){
            $type=$_GET["type"];
            $pageIndex=$_GET["pageIndex"];
			//周日
			$week = date("w");
			$date=date('H:i');
          
			$recomsult=M()->query('SELECT ut.utid,ut.roomid,r.createtime,r.roomtitle,r.speaker,r.title,r.hits,r.roomcode,r.webcastkeyid,r.wsurl,r.roomnumber,r.isonly,r.isrtype,r.classifyid,ut.teachname,ut.teacherimg,ut.tlabel,c.classifyname FROM wht_userteacher AS ut LEFT JOIN wht_rooms AS r ON r.roomid = ut.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where ttype=2 and r.classifyid='.$type);
			
                        
                        $count=count($recomsult);
                        
                       //echo M()->getLastSql();
				$result = array();
				$result1 = array();//zhibo
                               $result2 = array();//weizhibo
                               
                               
				foreach ($recomsult as $key => $value) {
					//查询老师的今日节目单，根据当前的时间端判断老师是否在直播中
					$aa = M('programmenu')->field('progname,progtime')->where(array('weekday'=>$week,'teacherid'=>$value['utid']))->select();
					//echo dump($aa);
					$status =2;
					$progname = '';
					foreach($aa as $key1 => $vv){
						//echo dump($vv);
						if($vv['progtime']){
							//获取当前时间和周几
							$start=substr($vv['progtime'],0,5);
							$end=substr($vv['progtime'],-5);
							if($date>=$start&&$date<=$end){
								$status=1;
								$progname = $vv['progname'];
								//var_dump($status);
								break;
							}else{
								continue;//跳出本次循环,继续执行下一次循环 
							}
						}else{
							break;
						}
					}
					if($value['wsurl']){
						$wsurls = 'ws://'.$value['wsurl'];
					}
					if($status==1){
				$result1[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }else{
                            
                            $result2[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$progname,
					//"roomtitle"=>$value['roomtitle'],
					"hits"=>$value['hits'],
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['teachname'],
					"headimage"=>$value['teacherimg'],
					"classifyname"=>$value['classifyname'],
					"tlabel"=>$value['tlabel'],
					"status"=>$status,
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>$value['utid'],
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,
					"roomnumber"=>$value['roomnumber'],
					"title"=>$value['title'],
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],	
            );
                        }
				}
                                
				
                                
                                 $result=array_merge($result1,$result2);
                             
                                 
                                 $start=0;
                                  $list=array();
                                
                                 if($pageIndex==1){
                                     if($count<20){
                                          $start=0;
                                     $end=$count;
                                     }else{
                                     $start=0;
                                     $end=20;
                                     } 
                                 }else{
                                     
                                     if($count<($pageIndex-1)*20){
                                       $start=$count;
                                     $end=$count;
                                     }else{
                                         if($count<$pageIndex*20){
                                              $start=($pageIndex-1)*20;
                                              $end=$count;
                                         }else{
                                              $start= ($pageIndex-1)*20;
                                    $end=$pageIndex*20;
                                         }
                                     }
                                     
                                   
                                    
                                 }
                                 
                          
                               
                                 
                                  $zhibolist=array();
                                  $weizhibolist=array();
                                 
                                  
                                  
                             
                                  
                                  
                                 for($i=$start;$i<=$end-1;$i++){
                                 
                                       $shuju=$result[$i]!=null?$result[$i]:"";
                                     if($result[$i]["status"]==1){
                                       $zhibolist[]=$shuju;
                                 
                                     }else{
                                       
                                          $weizhibolist[]=$shuju;
                
                                     }
                                   
                                 }
                                 
                              shuffle($zhibolist);
                                shuffle($weizhibolist);
                                 
                             
                         
                                    $list=array_merge($zhibolist,$weizhibolist);

                
                                
                                 
       
                             
                              
				$this->myApiPrint("success","200",$list);
			}
                        
                        else{
			$this->myApiPrint('accesstoken don\'t find',404);
		}
        
    }
    
    //根据分类显示当前正在直播的视频
//    public function getClassId(){
//        \Predis\Autoloader::register();
//        $redis = new \Predis\Client();
//
//        $token=$_GET["accesstoken"];
//        $userflag=$this->checktoken($token);
//        if($userflag){
//            $user=$redis->hgetall($userflag);
//            $userid=$user["uid"];
//            $classifyid=$_GET["classifyid"];
//            $type=$_GET["type"];
//            $roomid=$_GET["roomid"];
//
//        }
//    }

    //获取主播信息
    public function getUserInfo(){
        $token=$_GET["accesstoken"];
        $userflag=$this->checktoken($token);
        if($userflag){
            $roomid=$_GET["roomid"];
            $userinfo=M("userinfos")->where("roomid=$roomid")->field("nickname,sharecode,headimage")->find();
            if($userinfo){
                $this->myApiPrint("success","200",$userinfo);
            }else{
                $this->myApiPrint("没有当前主播信息！","404");
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }
}