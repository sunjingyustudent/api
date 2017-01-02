<?php
namespace Home\Controller;
use Think\Controller;
use Think\Think;

class AliPayController extends Controller {
    /*
     * 初始化方法
     */
    public function _initialize()
    {
        vendor('Alipay.Corefunction');
        vendor('Alipay.Rsafunction');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }


    function index(){
        $this->display("AliPay:AliPay");
    }

    /*
     * 发送订单请求
     * */
    function sendAliPayMessagePhone(){
        /**************************请求参数**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
//        $out_trade_no = $_POST['WIDout_trade_no'];
        $userid=$_POST["Userid"];
//        var_dump($userid);
//        exit();-
        //生成订单号
        $ordernum="JDZB-".date("YmdHis")."-".$userid;
        $out_trade_no = $ordernum;
        //订单名称，必填
        $subject = $_POST['WIDsubject'];
        //付款金额，必填
        $total_fee = $_POST['WIDtotal_fee'];

        //商品描述，可空
        $body = $_POST['WIDbody'];


        $aliPayConf=C('ALI_CONFIG');
        $aliPayConf["out_trade_no"]=$out_trade_no;
        $aliPayConf["subject"]=$subject;
        $aliPayConf["total_fee"]=$total_fee;
        $aliPayConf["body"]=$body;
        
        
        //生成订单
        $data["userid"]=$userid;
        $data["totalprice"]=$total_fee;
        $data["paymentby"]="1";
        $data["realprice"]=$total_fee;
        $data["orderdate"]=date("Y-m-d H:i:s",time());
        $data["ordercode"]=$ordernum;
        $data["paystatus"]=1;
        $data["specialdes"]="购买".(floatval($total_fee)*6)."个钻石";
        $data["isdelete"]=0;
        $result=M("recharge")->add($data);
        
        //建立请求
        $alipaySubmit = new \AlipaySubmit($aliPayConf);
        $html_text = $alipaySubmit->buildRequestForm($aliPayConf,"get", "确认");
        echo $html_text;
    }


    function sendAliPayMessage(){
        /**************************请求参数**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
//        $out_trade_no = $_POST['WIDout_trade_no'];
        $userid=$_POST["Userid"];
        //dump($user);exit;
        //生成订单号
        $ordernum="JDZB-".date("YmdHis")."-".$userid;
        $out_trade_no = $ordernum;
        //订单名称，必填
        $subject = $_POST['WIDsubject'];

        //付款金额，必填
        $total_fee = $_POST['WIDtotal_fee'];

        //商品描述，可空
        $body = $_POST['WIDbody'];


        $aliPayConf=C('ALI_CONFIG');
        $aliPayConf["out_trade_no"]=$out_trade_no;
        $aliPayConf["subject"]=$subject;
        $aliPayConf["total_fee"]=$total_fee;
        $aliPayConf["body"]=$body;
        
        

        //生成订单
        $data["userid"]=$userid;
        $data["totalprice"]=$total_fee;
        $data["paymentby"]="1";
        $data["realprice"]=$total_fee;
        $data["orderdate"]=date("Y-m-d H:i:s",time());
        $data["ordercode"]=$ordernum;

        $data["paystatus"]=1;
        $data["specialdes"]="购买".(floatval($total_fee)*6)."个钻石";
        $data["isdelete"]=0;
        $result=M("recharge")->add($data);


        //建立请求
        $alipaySubmit = new \AlipaySubmit($aliPayConf);
        $html_text = $alipaySubmit->buildRequestForm($aliPayConf,"get", "确认");
        echo $html_text;
    }

    /*
     *异步回调
     * */
    public function notify_url(){
        //异步通知调用返回地址
        $aliPayConf=C('ALI_CONFIG');

        $alipayNotify = new \AlipayNotify($aliPayConf);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {//验证成功

            if ($_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序
                $res = $this->buyInfo($aliPayConf,"成功进入异步回调通知");
                
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";		//请不要修改或删除
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }


    /*
     *同步回调
     * */
    public function return_url(){
        $aliPayConf=C('ALI_CONFIG');

        //同步通知返回调用地址
        $alipayNotify = new \AlipayNotify($aliPayConf);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            $this->assign("totil_fee",$_GET["total_fee"]);
            $this->assign("buyer_email",$_GET["buyer_email"]);
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                $res = $this->buyInfo($aliPayConf,'成功进入同步回调通知','get');

            }
            else {
                echo "trade_status=".$_GET['trade_status'];
            }


           $this->display("AliPay:pay");

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }
    }

    /**
     * 回调成功调用-添加信息到数据库
     * @param type $aliPayConf 支付配置
     * @param type $log_msg log日志信息
     * @param type $method 获取参数类型{post或者get}
     * @param type $paytype 支付类型 {1：网页，2：手机版}
     * @param type $payby 支付方式 {1：支付宝，2：易宝，3：微信，4：银联，5：苹果内购}
     * @return boolean 是否添加成功
     */
    private function buyInfo($aliPayConf,$log_msg,$method = 'post',$paytype = 1,$payby = 1){

        $log=new \Think\Log();
        //请在这里加上商户的业务逻辑程序代
        $log->write("{$log_msg}");
        
        $para = I("$method.");
        // 判断 seller_id （卖家支付宝用户号）是否和配置中的一样
        if($para["seller_id"] != $aliPayConf["seller_id"]){
            $log->write("卖家商户号错误");
            return false;
        }

        $recharge = M("recharge");
        $where = array();
        $where["ordercode"] = $para["out_trade_no"];
        //$where["totalprice"] = $para["total_fee"];
        $rec_info = $recharge->field("rechargeid,userid,paystatus,totalprice")->where($where)->find();

        if(empty($rec_info) || $para["total_fee"] != $rec_info["totalprice"] || $rec_info["paystatus"]==2){
            return false;
        }

        $result = $recharge->where($where)->setField("paystatus",2);
        
        if(!$result){
            return false;
        }
        $payment_array = array();
        $payment_array["paytype"] = $paytype;//支付类型 {1：网页，2：手机版}
        $payment_array["payby"] = $payby; // 支付方式 {1：支付宝，2：易宝，3：微信，4：银联，5：苹果内购}
        $payment_array["rechargeid"] = $rec_info["rechargeid"];//订单编号
        $payment_array["sellerid"] = $para["seller_id"];//卖家商户号
        $payment_array["outtradeno"] = $para["out_trade_no"];
        $payment_array["buyerid"] = $para["buyer_id"];
        $payment_array["buyer"] = $para["buyer_email"];
        $payment_array["tradeno"] = $para["trade_no"];
        $payment_array["totalfee"] = $para["total_fee"];
        $payment_array["tradestatus"] = $para["trade_status"];
        $payment_array["dealtime"] = $para["notify_time"];
        $payment_array["banktype"] = "";
        $payment_array["bankorderid"] = "";
        $payment_array["tradefee"] = "";
        $pay = M("payment")->add($payment_array);
        

        $wh = array();
        $wh["userid"]=$rec_info["userid"];
        $user_model = D("userinfos");
        $user_info=$user_model->where($wh)->field("isfirst,expervalue,ninemoney")->find();
        //只能变量和变量加
        $total_fee = $para["total_fee"] * 6;
        $data = array();
        //第一次充值98
        if($para["total_fee"] == 98 && $user_info["isfirst"] == 0){
            $total_fee = $total_fee + 100;
            $data["expervalue"] = $user_info["expervalue"] + 500;
            $data["isfirst"] = 1;
        }
        $data["ninemoney"] = $user_info["ninemoney"] + $total_fee;
        
        $result = $user_model->where($wh)->save($data);
        return $result;

    }

}