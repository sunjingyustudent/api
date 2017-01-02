<?php
/**
 * 我要当主播公开接口API
 * 1.主播活动接口 1
 * 2.主播身份认证接口
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class AnchorController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * IOS端 主播上传认证
     * Api/Anchor/Iosaccredid
     * */
    public function Iosaccredid()
    {
        header("Content-Type: application/octet-stream");
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $token=$_POST["accesstoken"];
        if($this->checktoken($token)){
            $user=$redis->hgetall($token);
            if($user['uid']>0){
                $idcard=$_POST["identitycord"];
                $username=$_POST["name"];
                $idcardimage=$_POST["imgurl"];
                //将16进制流转换成二进制流转换成图片
                $byte=$idcardimage;
                $byte = str_replace(' ','',$byte);   //处理数据
                $byte = str_ireplace("<",'',$byte);
                $byte = str_ireplace(">",'',$byte);
                $byte=pack("H*",$byte);      //16进制转换成二进制
                $zs = '/data/wwwroot/api/Uploads/IDcard/'.uniqid().'.jpg';
                $ss = substr($zs,(strpos($zs,"IDcard/")+7));
                //写入文件中！
                if(file_put_contents($zs,$byte,true)){
                    //上传成功以后把路径写道阿里云里然后保存修改数据，之后把本地图片删掉
//                    $imgurl = "http://api.9dushuju.com/Uploads/IDcard/".$ss;
                    $imgurl = "http://api.9dushuju.com/Uploads/IDcard/".$ss;
                    $data["realname"]=$username;
                    $data["idcard"]=$idcard;
                    $data["idcardimg"]=$imgurl;
                    $data["status"]=3;
                    //添加到用户表里
                    $result=M("accred")->add($data);
                    //把获取到$id修改用户表的字段
                    if($result){
                        $ss = M('userinfos')->where(array("userid"=>$user["uid"]))->save(array("accredid"=>$result));

//                        var_dump($result);
//                        var_dump($user["userid"]);
//                        if($ss){
                            //用户上传成功以后把本地图片删掉
//                            unlink($zs);
                            $mm = array("status"=>3,
                                "img"=>$imgurl
                            );
                            $this->myApiPrint("success","200",$mm);
//                        }else{
//                            $this->myApiPrint("certified submit fail","300");
//                        }
                    }else{
                        $this->myApiPrint("error","500");
                    }
                }else{
                    $this->myApiPrint('上传图片步骤出错，无法进行主播认证',301);
                }
            }else{
                $this->myApiPrint("tourists cannot be certified","300");
            }
        }else{
            //$this->myApiPrint("accesstoken is not exists","300");
			$this->myApiPrint('accesstoken don\'t find',404);
        }
    }
//    public function Iosaccredid()
//    {
//        $getaccred = I('get.');
//        if(!empty($getaccred)) {
//            $atoken =$getaccred['accesstoken'] ;
//            if ($this->checktoken($atoken)) {
//                $data["userid"] = $this->checktoken($atoken);
//                $userinfos = M('userinfos');
//                $va = $userinfos->where($data)->count();
//                if (!$va) {//判断用户名是否存在
//                    $this->myApiPrint('don\'t find', '404');
//                } else {
//                   // $accred['idcardimg'] = $getaccred['idcardimg'];//id正面
//
//					$accred['idcardimg'] = myStream2Img($getaccred['idcardimg']['data'],$getaccred['idcardimg']['userid'],$getaccred['idcardimg']['type'],$getaccred['idcardimg']['name']);
//                    //$accred['idcardinvimg'] = $getaccred['idcardinvimg'];//id反面
//					$accred['idcardinvimg'] =myStream2Img($getaccred['idcardinvimg']['data'],$getaccred['idcardinvimg']['userid'],$getaccred['idcardinvimg']['type'],$getaccred['idcardinvimg']['name']);
//
//                   // $accred['idcardperimg'] = $getaccred['idcardperimg'];//id半身照
//					$accred['idcardperimg'] =myStream2Img($getaccred['idcardperimg']['data'],$getaccred['idcardperimg']['userid'],$getaccred['idcardperimg']['type'],$getaccred['idcardperimg']['name']);
//                    if($accred!==false){
//						  $accredadd = M('accred')->data($accred)->add();
//                    if ($accredadd > 0) {
//
//						$result=M('accred')->where('accredid=$accredadd')->find();
//                        $this->myApiPrint('success', '200',$result);
//                    } else {
//                        $this->myApiPrint('add error ', '300');
//                    }
//
//				}else{
//					$this->myApiPrint('add error ', '300');
//
//					}
//                }
//            } else {
//                $this->myApiPrint('accesstoken don\'t find', 404);
//            }
//        }else{
//            $this->myApiPrint('数据传输错误 ', '300');
//        }
//    }

    /*
     * android端 主播上传认证
     * Api/Anchor/ChangeUrl
     * */
    public function changeUrl(){
        //链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $token=$_POST["accesstoken"];
        $userflag=$this->checktoken($token);
        if($userflag){
            $user=$redis->hgetall($token);
            if($user['uid']>0){
                $idcardimage=$_POST["imgurl"];
                $idcard=$_POST["identitycord"];
                $username=$_POST["name"];
                $data["realname"]=$username;
                $data["idcard"]=$idcard;
                $data["idcardimg"]=$idcardimage;
                $data["status"]=3;
                //添加到用户表里
                $result=M("accred")->add($data);
                //把获取到$id修改用户表的字段
                if($result){
                    $ss = M('userinfos')->where(array("userid"=>$user["uid"]))->save(array("accredid"=>$result));
                    if($ss){
                        $mm = array(
                            "status"=>3
                        );
                        $this->myApiPrint("success","200",$mm);
                    }else{
                        $this->myApiPrint("certified submit fail","300");
                    }
                }else{
                    $this->myApiPrint("error","500");
                }
            }else{
                $this->myApiPrint("tourists cannot be certified","300");
            }
        }else{
            //$this->myApiPrint("accesstoken is not exists","300");
			$this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    /*
     * 获取认证的接口状态
     * Api/Anchor/getaccedstatus/accesstoken/wht1-token-5e6a6186665f100aac2a19f9077a56bd
     * */
    public function getaccedstatus(){
        //链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();

        $atoken = I('get.accesstoken');//获取用户的token
        if ($this->checktoken($atoken)) {
            $re = $redis->hgetall($atoken);//添加到redis缓存中
            $where["userid"] = $re["uid"];
            $userid=$where["userid"];
            if ($where["userid"] != 0) {
                //查询用户的认证表是否存在，并且状态是什么
                //认证ID
                $dd = M('userinfos')->where(array("userid"=>$userid))->field("accredid")->find();
                $count  = intval($dd["accredid"]);
                if($count<=0){
                    $this->myApiPrint('users do not have certification',300);
                }
                //否则再次查询认证表一下把最新取到的状态给返回
                $result =M('accred')->where(array("accredid"=>$dd["accredid"]))->field('status,remark')->find();
//                var_dump($result);
                if($result){
                    $this->myApiPrint('success',200,$result);
                }else{
                    $this->myApiPrint('users do not have certification',300);
                }
            }else{
                $this->myApiPrint('is tourist',300);
            }
        }else{
            //$this->myApiPrint('accesstoken not exits',300);
			$this->myApiPrint('accesstoken don\'t find',404);
        }
    }
}