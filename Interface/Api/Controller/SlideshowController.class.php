<?php

/**
 * 帐号公开接口API
 * 1.banner图列表
 * //2.根据banner图类型点击跳转页面
 * 3.推荐的直播list接口
 * 4.弹出活动层接口
 */

namespace Api\Controller;

use Common\Controller\ApiController;

class SlideshowController extends ApiController {

    public function _initialize() {
        //模块初始化，重写父类方法，避免该模块进入token验证
        vendor('Pili');
    }

    /*
     * banner图列表 redis读取数据
     * 测试接口：
     * */

    public function slidelist() {
//        $getslide = I('get.');
//        $adslide = M('adslide');
//        if(!empty($getslide)) {
//            $atoken = $getslide['accesstoken'];
//            if ($this->checktoken($atoken)) {
//                \Predis\Autoloader::register();
//                $redis = new \Predis\Client();
//                if ($redis->exists('adslidenav')) {
//                    $retval = $redis->get('adslidenav');
//                    $this->myApiPrint('success', '200', json_decode($retval,true));
//                } else {
//                    \Predis\Autoloader::register();
//                    $redis = new \Predis\Client();
//                    $adscc = $adslide->field('adtype,navtype,imageurl,adurl,roomid')->where('isdelete=0 and adtype=1')->limit(5)->order('sort desc')->select();
//
//                    $redis->set('adslidenav', json_encode($adscc)); //存储多个key对应的value
//                    $retval = $redis->get('adslidenav'); //获取多个key对应的value
//                    if ($redis->exists('adslidenav')) {
//                        $this->myApiPrint('success', 200, json_decode($retval,true));
//                    } else {
//                        $this->myApiPrint('redis  error ', '300');
//                    }
//
//                }
//            }else{
//                $this->myApiPrint('accesstoken don\'t find', 404);
//
//            }
//        }else{
//            $this->myApiPrint('数据传输错误 ', '300');
//
//        }
        $getslide = I('get.');
        $adslide = M('adslide');
        //链接redis,每次需要的时候重新链接
        $redis = $this->connectredis();
        if (!empty($getslide)) {
            $atoken = $getslide['accesstoken'];
            //$retval = $redis->del('adslidenav');
			//exit();
            if ($this->checktoken($atoken)) {
                if ($redis->exists('adslidenav')) {
                    $retval = $redis->get('adslidenav');
                    if (empty($retval)) {
                        $adscc = $adslide->field('adtype,navtype,imageurl,adurl,roomid')->where('isdelete=0 and adtype=1')->limit(5)->order('sort desc')->select();
                        $redis->set('adslidenav', json_encode($adscc)); //存储多个key对应的value
                        $this->myApiPrint('success', '200', json_decode($adscc, true));
                    } else {
                        $this->myApiPrint('success', '200', json_decode($retval, true));
                    }
                } else {
                    $adscc = $adslide->field('adtype,navtype,imageurl,adurl,roomid')->where('isdelete=0 and adtype=1')->limit(5)->order('sort desc')->select();
                    $redis->set('adslidenav', json_encode($adscc)); //存储多个key对应的value
                    $retval = $redis->get('adslidenav'); //获取多个key对应的value
                    if ($redis->exists('adslidenav')) {
                        $this->myApiPrint('success', 200, json_decode($retval, true));
                    } else {
                        $this->myApiPrint('redis  error ', '300');
                    }
                }
            } else {
                $this->myApiPrint('accesstoken don\'t find', 404);
            }
        } else {
            $this->myApiPrint('数据传输错误 ', '300');
        }
    }

    public function slidelistss() {

        $adslide = M('adslide');
        //链接redis,每次需要的时候重新链接
        $redis = $this->connectredis();

        //$redis->del('adslidenav');
        //exit();
        if ($redis->exists('adslidenav')) {
            $retval = $redis->get('adslidenav');
            $this->myApiPrint('success', '200', json_decode($retval, true));
        } else {
            $adscc = $adslide->field('adtype,navtype,imageurl,adurl,roomid')->where('isdelete=0')->limit(5)->order('sort desc')->select();
            //$adscc = $adslide->field('adtype,navtype,imageurl,adurl,roomid')->where('isdelete=0')->limit(5)->order('sort desc')->fetchsql('true')->select();
            //var_dump($adscc);
            $redis->set('adslidenav', json_encode($adscc)); //存储多个key对应的value
            $retval = $redis->get('adslidenav'); //获取多个key对应的value
            if ($redis->exists('adslidenav')) {
                $this->myApiPrint('success', 200, json_decode($retval, true));
            } else {
                $this->myApiPrint('redis  error ', '300');
            }
        }
    }

    /*
     * 推荐的直播list接口
     * 测试接口：
     * */

    public function recommentlist_zzzz() {
         $recomment = I('get.');
        if(!empty($recomment)) {
            $atoken = $recomment['accesstoken'];
        if ($this->checktoken($atoken)) {
			$recomrooms=M('rooms');
			$where=array("status"=>1,"isdelete"=>0);
		//$recomsult=$recomrooms->join('inner join wht_userinfos as us on wht_rooms.roomid = us.roomid')->limit(10)->field('wht_rooms.createtime,wht_rooms.roomid,wht_rooms.roomcode,wht_rooms.roomtitle,wht_rooms.webcastkeyid,wht_rooms.headimage,wht_rooms.classifyid,wht_rooms.hits,wht_rooms.title,wht_rooms.isonly,wht_rooms.isrtype,us.nickname,us.username')->order("sort desc")->select();
		//$recomsult=$recomrooms->limit(10)->field('createtime,roomid,roomcode,roomtitle,webcastkeyid,headimage,classifyid,hits,wsurl,roomnumber,title,isonly,isrtype')->where("isdelete=0 and isrecommend=1")->order("sort desc")->select();
		$week = date("w");
                $date=date('H:i');
		$recomsult=M()->cache(60,true)->query('SELECT ut.utid,ut.roomid,r.createtime,r.roomtitle,r.speaker,r.title,r.hits,r.roomcode,r.webcastkeyid,r.wsurl,r.roomnumber,r.isonly,r.isrtype,r.classifyid,ut.teachname,ut.teacherimg,ut.tlabel,c.classifyname FROM wht_userteacher AS ut LEFT JOIN wht_rooms AS r ON r.roomid = ut.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where ttype=2 order by status desc');
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
                      $value['title']=mb_substr($value['title'],0,7,'utf-8');
                        
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
            
             
			//echo dump($status);
			if($status==1){
				$result1[] = array(
					"roomid"=>"{$value['roomid']}",
					"progname"=>"{$progname}",
				
					"hits"=>"{$value['hits']}",
					"roomcode"=>"{$value['roomcode']}",
					"roomtitle"=>"{$value['teachname']}",
					"headimage"=>"{$value['teacherimg']}",
					"classifyname"=>"{$value['classifyname']}",
					"tlabel"=>"{$value['tlabel']}",
					"status"=>"{$status}",
					"speaker"=>"{$value['speaker']}",
					"classifyid"=>"{$value['classifyid']}",
					"utid"=>"{$value['utid']}",
					"createtime"=>"{$vv['createtime']}",
					"webcastkeyid"=>"{$value['webcastkeyid']}",
					"wsurl"=>"{$wsurls}",
					"roomnumber"=>"{$value['roomnumber']}",
					"title"=>"{$value['title']}",
					"isonly"=>"{$value['isonly']}",
					"isrtype"=>"{$value['isrtype']}",	
            );
                        }else{
                            
                            $result2[] = array(
					"roomid"=>"{$value['roomid']}",
					"progname"=>"{$progname}",
				
					"hits"=>"{$value['hits']}",
					"roomcode"=>"{$value['roomcode']}",
					"roomtitle"=>"{$value['teachname']}",
					"headimage"=>"{$value['teacherimg']}",
					"classifyname"=>"{$value['classifyname']}",
					"tlabel"=>"{$value['tlabel']}",
					"status"=>"{$status}",
					"speaker"=>"{$value['speaker']}",
					"classifyid"=>"{$value['classifyid']}",
					"utid"=>"{$value['utid']}",
					"createtime"=>"{$vv['createtime']}",
					"webcastkeyid"=>"{$value['webcastkeyid']}",
					"wsurl"=>"{$wsurls}",
					"roomnumber"=>"{$value['roomnumber']}",
					"title"=>"{$value['title']}",
					"isonly"=>"{$value['isonly']}",
					"isrtype"=>"{$value['isrtype']}",	
            );
                        }
                            
                        
        }
        
        
        shuffle($result1);
        shuffle($result2);
       $result=array_merge($result1,$result2);
        
     
        
        
        
        
			if($recomsult){
				
				 $this->myApiPrint('success',200,$result);
			}else{
				$this->myApiPrint('数据不存在 ','300');	
			}
        } else {
            $this->myApiPrint('accesstoken don\'t find',404);
        }
        }else{
            $this->myApiPrint('数据传输错误 ', '300');

        }
    }
	public function recommentlist() {
         $recomment = I('get.');
        if(!empty($recomment)) {
            $atoken = $recomment['accesstoken'];
        if ($this->checktoken($atoken)) {
			/*20161116首页推荐需求如下：显示所有的分析师，带分页，最新直播的放在前面取top60条数据 不做分页*/
		$recomsult=M()->cache(60,true)
						->query('SELECT u.userid,r.roomid,r.roomtitle,r.speaker,r.hits,r.roomcode,r.roomnumber,r.title,r.webcastkeyid,r.wsurl,r.rtmpurl,r.tlabel,r.status,r.headimage as roomimage,r.isonly,r.isrtype,r.classifyid,u.nickname,u.headimage,r.classifyid,c.classifyname FROM wht_rooms AS r LEFT JOIN wht_userinfos AS u ON r.roomid = u.roomid LEFT JOIN wht_classify AS c ON c.classifyid = r.classifyid where r.isdelete=0 and u.isdelete=0 and u.usergrade=1 and u.usertype=2 and r.isonly=0 and r.status in(1,2)  and r.shenhe=1 order by status asc LIMIT 60');
		//echo M()->getLastSql();
		$result = array();
        foreach ($recomsult as $key => $value) {
			if($value['wsurl']){
				$wsurls = 'ws://'.$value['wsurl'];
            }//websocket链接
			if(empty($value['headimage'])){
				$headimage='http://jdcjapp.oss-cn-hangzhou.aliyuncs.com/images/personal_default.png';
			}else{
				$headimage=$value['headimage'];
			}
			if(empty($value['roomimage'])){
				$roomimage='http://jdcjapp.oss-cn-hangzhou.aliyuncs.com/images/personal_default.png';
			}else{
				$roomimage=$value['roomimage'];
			}//房间封面
			if(empty($value['roomtitle'])){
				$roomtitle=$value['nickname'].'的直播';
			}else{
				$roomtitle=$value['roomtitle'];
			}//房间标题
			$result[] = array(
					"roomid"=>$value['roomid'],
					"progname"=>$roomtitle,
					"hits"=>$value['hits'],//app上显示的点击数
					"roomcode"=>$value['roomcode'],
					"roomtitle"=>$value['nickname'],//app上显示的标题
					"headimage"=>$roomimage,//头像
					"classifyname"=>$value['classifyname'],//类型
					"tlabel"=>$value['tlabel'],
					"status"=>$value['status'],
					"speaker"=>$value['speaker'],
					"classifyid"=>$value['classifyid'],
					"utid"=>"0",
					"createtime"=>$vv['createtime'],
					"webcastkeyid"=>$value['webcastkeyid'],
					"wsurl"=>$wsurls,//wsurl链接
					"roomnumber"=>$value['roomnumber'],
					"title"=>$roomtitle,//app上显示的标题
					"isonly"=>$value['isonly'],
					"isrtype"=>$value['isrtype'],
				);        
        }
		if($result){
			$this->myApiPrint('success',200,$result);
			}else{
				$this->myApiPrint('数据不存在 ','300');	
			}
        } else {
            $this->myApiPrint('accesstoken don\'t find',404);
        }
        }else{
            $this->myApiPrint('数据传输错误 ', '300'); 

        }
    }
	
	
	/*
     * 推荐的直播list接口
     * 测试接口：
     * */
    public function recommentlist_zx(){
        $accesstoken = I("get.accesstoken","","strip_tags");
        if(empty($accesstoken) || !$this->checktoken($accesstoken)){
            $this->myApiPrint('accesstoken don\'t find', 404);exit;
        }
        
        $userinfos_model = D("userinfos");
        $where = array();
        $where["wht_userinfos.isdelete"] = 0;
        $where["wht_userinfos.usergrade"] = 1;
        $where["wht_rooms.isdelete"]=0;
        $count = $userinfos_model->join("wht_rooms ON wht_rooms.roomid = wht_userinfos.roomid")->where($where)->count();
        $listRows = I("get.listRows","9","intval");
        $page = new \Think\Page($count, $listRows);
        $lists = $userinfos_model->field("wht_rooms.roomtitle as title,wht_rooms.rtmpurl,wht_rooms.isclosed,wht_rooms.classifyid,wht_rooms.headimage as roomimage,wht_rooms.wsurl,wht_rooms.roomid,wht_rooms.hits,wht_userinfos.headimage,wht_userinfos.username")
				->join("wht_rooms ON __ROOMS__.roomid = wht_userinfos.roomid")
				->where($where)
				->limit($page->firstRow.",".$page->listRows)
				->order("wht_rooms.isrecommend DESC,wht_rooms.status DESC")->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['roomid'] = "{$v['roomid']}";
            $lists[$k]['title'] = "{$v['title']}";
            $lists[$k]['hits'] = "{$v['hits']}";
            $lists[$k]['classifyid'] = "{$v['classifyid']}";
            $lists[$k]['roomimage'] = "{$v['roomimage']}";
            $lists[$k]['wsurl'] = "{$v['wsurl']}";
            $lists[$k]['headimage'] = "{$v['headimage']}";
            $lists[$k]['username'] = "{$v['username']}";
            $lists[$k]['rtmpurl'] = "{$v['rtmpurl']}";
            $lists[$k]['isclosed'] = "{$v['isclosed']}";
        }
        
        $this->myApiPrint("success",200,$lists);
        
        //dump($lists);exit; 
    }
	
	/*
     * 分类直播list接口
     * 测试接口：
     * */
    public function typelist(){
        $accesstoken = I("get.accesstoken","","strip_tags");
        if(empty($accesstoken) || !$this->checktoken($accesstoken)){
            $this->myApiPrint('accesstoken don\'t find', 404);exit;
        }
        
        $userinfos_model = D("userinfos");
        $where = array();
        $where["wht_userinfos.usertype"] = 3;//公司内部直播室
        $where["wht_userinfos.isdelete"] = 0;
        $where["wht_userinfos.usergrade"] = 1;
        $where["wht_rooms.isdelete"]=0;
        $classifyid = I("get.classifyid");
        if($classifyid){
            $where["wht_rooms.classifyid"] = array("like","%{$classifyid}%");
        }
        $count = $userinfos_model->join(" wht_rooms ON wht_rooms.roomid = wht_userinfos.roomid")->where($where)->count();
        $listRows = I("get.listRows","9","intval");
        $page = new \Think\Page($count, $listRows);
        $lists = $userinfos_model->field("wht_rooms.roomtitle as title,wht_rooms.rtmpurl,wht_rooms.isclosed,wht_rooms.classifyid,wht_rooms.headimage as roomimage,wht_rooms.wsurl,wht_rooms.roomid,wht_rooms.hits,wht_userinfos.headimage,wht_userinfos.username")->join("wht_rooms ON __ROOMS__.roomid = wht_userinfos.roomid")->where($where)->limit($page->firstRow.",".$page->listRows)->order("wht_rooms.isrecommend DESC,wht_rooms.status DESC")->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['roomid'] = "{$v['roomid']}";
            $lists[$k]['title'] = "{$v['title']}";
            $lists[$k]['hits'] = "{$v['hits']}";
            $lists[$k]['classifyid'] = "{$v['classifyid']}";
            $lists[$k]['roomimage'] = "{$v['roomimage']}";
            $lists[$k]['wsurl'] = "{$v['wsurl']}";
            $lists[$k]['headimage'] = "{$v['headimage']}";
            $lists[$k]['username'] = "{$v['username']}";
            $lists[$k]['rtmpurl'] = "{$v['rtmpurl']}";
            $lists[$k]['isclosed'] = "{$v['isclosed']}";
        }
        
        $this->myApiPrint("success",200,$lists);
        
//        dump($page->p);exit;
        
        
    }

    /*
     * 弹出活动层接口
     * 测试接口：
     * */

    public function showactivity() {
        /* vendor('Gateway');//导入类库
          $Gateway=new \Gateway();
          $Gateway->closeClient('7f00000108fc00000001');
          $cid = $Gateway->getAllClientSessions();
          var_dump($cid); */
    }

}
