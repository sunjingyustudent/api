<?php
/**
 * 支付的公开接口API
 * 基本的查询，登录，注册，等
 * 1.微信支付 d
 * 2.支付宝支付
 * 3.记录用户充值交易记录接口
 * 4.记录交易信息接口
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class PaymentController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    //充值记录
    public function getPayment(){

        //共有参数
        $token=I("post.accesstoken");
        $userid=$this->checktoken($token);
        if($userid!=false) {

            $paymentby = $_POST["paymentby"];//支付方式 1：支付宝 2：微信 3: 银行卡 4：苹果内购

            $price = $_POST["totalprice"];//订单总金额
            //$orderdate=$_POST["orderdate"];//订单日期
            /*
             * 用户充值记录信息
             */
            $data["userid"] = $userid;//用户ID
            // $data["ordercode"]=$_POST["ordercode"];//订单号
            $data["totalprice"] = $price;//订单总金额
            // $data["paystatus"]=$_POST["paystatus"];//状态 1：待付款 2：已付款
            //$data["paymentby"]=$paymentby;
            //$data["orderstatus"]=$_POST["orderstatus"];//订单状态 1：打开 0：关闭
            //$data["launchfrom"]=$_POST[""];//提交的客户端 0：web 1：Mobile
            //$data["realprice"]=$_POST["realprice"];//订单支付金额
            //$data["specialdes"]=$_POST["specialdes"];//特别说明
            //$data["remark"]=$_POST["remark"];//备注
            $data["isdelete"] = $_POST["isdelete"];
            $reslut = M("recharge")->add($data);
            if ($reslut) {
                /*
                * 交易信息表
                */
                $data["paytype"] = 2;//1:网页版支付 2:手机版支付
                $data["payby"] = $paymentby;//支付品牌 1：支付宝2：易宝3：微信4：银联5：苹果内购
                $data["rechargeid"] = $reslut;
                //$data["sellerid"]=$_POST["sellerid"];//商户编号/商户号 支付宝：seller_id 微信：mch_id
                //$data["outtradeno"]=$_POST["buyerid"];//买家在支付系统账号对应的唯一用户ID 支付宝：buyer_id 微信：openid
                $data["tradeno"] = $_POST["tradeno"];//支付系统的交易流水号 支付宝:trade_no 微信：transaction_id
                $data["dealtime"] = $_POST["dealtime"];//成交时间
                $data["totalfee"] = $_POST["totalfee"];//交易金额 支付宝:total_fee 微信：total_fee
                //添加日志信息
                $res = M("payment")->add($data);
                if ($res) {
                    $usernine = M("userinfos")->where("userid=$userid")->field("ninemoney")->find();
                    $ninechange = M("userinfos")->where("userid=$userid")->setField("ninemoney", $usernine + $price);
                }
            }
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }






        //$data["buyer"]=$_POST["buyer"];//买家支付账号，可以是Email或手机号码或用户名 支付宝：buyer_email 微信：支付账号


        //$datap["tradestatus"]=$_POST["tradestatus"];//支付系统的支付状态 支付宝:trade_status 易宝：r1_Code 微信：trade_state

        //$data["banktype"]=$_POST["banktype"];//银行通道类型 支付宝:-- 微信：--
        //$data["bankorderid"]=$_POST["bankorderid"];//银行订单号 支付宝:-- 微信：--
        //$data["tradefee"]=$_POST["tradefee"];//手续费
    }

    //获取九币列表
    public function getNinemoney(){
        $token=I("get.accesstoken");
        $userid=$this->checktoken($token);
        if($userid!=false){
            //\Predis\Autoloader::register();
            //$redis = new \Predis\Client();
			//链接redis,每次需要的时候重新链接 做初始化
				$redis = $this->connectredis();
			
            if($redis->exists("ninemoney")){
                $moneylist=$redis->hgetall("ninemoney");
                return $this->myApiPrint("success","200",$moneylist);
            }else{
                $ninemoney=M("ninemoney")->field("nmid,nmname,money")->where("isdelete=0")->select();
                $redis->hmset("ninemoney");
                return $this->myApiPrint("success","200",$ninemoney);
            }
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

//
//    //记录支付记录
//    public function getPay(){
//        $token=$_GET["accesstoken"];
//        $userid=$this->checktoken($token);
//        if($userid){
//
//        }
//    }

    //充值接口
    /*
     * 用户充值表
     * $totalprice:订单总金额
     * $paymentby:支付方式：1：支付宝 2：微信 3: 银行卡 4：苹果内购
     * $realprice:订单支付金额
     *
     *交易信息表
     * $paytype：支付来源：1、网页版支付 2、手机版支付
     * $payby:支付品牌: 1：支付宝 2：易宝 3：微信 4：银联 5：苹果内购
     * $sellerid:商品编号，商户号
     * $outtradeno:商户订单号
     * $buyerid：买家用户号
     * $buyer:买家支付账号
     * $tradeno:系统交易流水号
     * $totalfee:交易金额
     * $tradestatus:交易状态
     * $dealtime:成交时间
     * $banktype:银行通道类型
     * $bankorderid:银行订单号
     * $tradefee:商户手续费
     */
    public function buyInfo($accesstoken=null,$totalprice=null,$paymentby=null,$realprice=null,
                            $paytype=null,$payby=null,$sellerid=null,$outtradeno=null,$buyerid=null,$buyer=null,$tradeno=null,$totalfee=null,$tradestatus=null,$dealtime=null,$banktype=null,$bankorderid=null,$tradefee=null){
        //\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
		
		//链接redis,每次需要的时候重新链接 做初始化
		$redis = $this->connectredis();
		
        $userflag=$this->checktoken($accesstoken);
        if($userflag){
            $user=$redis->hgetall($accesstoken);
            $userid=$user["uid"];

            //插入用户充值表
            $data["userid"]=$userid;
            $data["totalprice"]=$totalprice;
            $data["paymentby"]=$paymentby;
            $data["realprice"]=$realprice;
            $data["isdelete"]=0;
            $result=M("recharge")->add($data);

            if($result){
                $array["paytype"]=$paytype;
                $array["payby"]=$payby;
                $array["rechargeid"]=$result;
                $array["sellerid"]=$sellerid;
                $array["outtradeno"]=$outtradeno;
                $array["buyerid"]=$buyerid;
                $array["buyer"]=$buyer;
                $array["tradeno"]=$tradeno;
                $array["totalfee"]=$totalfee;
                $array["tradestatus"]=$tradestatus;
                $array["dealtime"]=$dealtime;
                $array["banktype"]=$banktype;
                $array["bankorderid"]=$bankorderid;
                $array["tradefee"]=$tradefee;
                $result=M("payment")->add($array);


            }else{
                $this->myApiPrint("error","500");
            }

            $usernine=M("userinfos")->where("userid=$userid")->field("ninemoney")->find();
            $result=M("userinfos")->where("userid=$userid")->setField("ninemoney",intval($usernine)+$totalprice*6);
            if($result){
                $this->myApiPrint("success","200");
            }else{
                $this->myApiPrint("error","300");
            }
        }else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }


//        $user=session("userinfo");
//        $userid=$user["userid"];



    }
}