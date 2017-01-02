<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    /*
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/index
     * */
    public function index(){
//        $total=10;//红包总额
//        $num=8;// 分成8个红包，支持8人随机领取
//        $min=0.01;//每个人最少能收到0.01元
//
//        $a = array();
//        for ($i=1;$i<$num;$i++)
//        {
//            $safe_total=($total-($num-$i)*$min)/($num-$i);//随机安全上限
//            $money=mt_rand($min*100,$safe_total*100)/100;
//            $total=$total-$money;
//            //$a=array('money'=>$money);
//            //$a[$i] =$money;
//            $a[$i] = array(
//                'i' => $i,
//                'money' => $money
//            );
//            echo $a[$i]['money'];
//            //array_push($a,$money);
//            echo '第'.$i.'个红包：'.$money.' 元，余额：'.$total.' 元 <br/>';
//        }
//        $a[$num] =$total;
//        echo $a[$i];
//        echo '第'.$num.'个红包：'.$total.' 元，余额：0 元<br/><br/>';
//        echo $a;
//        $pos = array_search(max($a),$a);
//        echo '最佳手气是：'.$a[$pos].'元';
//        //echo 'this is my applicati    on';
        $this->display();
    }

    /*
     *帮助中心详细页
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/articledetail/aautoid/9
     * */
    public function articledetail(){

        $id = I('get.aautoid');
//        var_dump($id);
        $as = M('article')
            ->where(array('aautoid'=>$id))
            ->field('createtime,aautoid,theme,artsouce,acontent,snapshots')
            ->find();
//        var_dump($as);
        $this->assign('lists', $as);// 赋值数据集
        $this->display();
    }

    /*
     * 分享apph5页面
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/shareapp/
     * */
    public function shareapp(){
        $this->display("Index:shareapp");
    }

    /*
     * 帮助中心页面
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/postdetail/aautoid/1
     * */
    public function postdetail(){
        $id = I('get.aautoid');
        $as = M('posts')
            ->where(array('id'=>$id))
            ->field('post_modified,post_content,post_source,post_title,post_excerpt')
            ->find();
        $this->assign('lists', $as);// 赋值数据集
        $this->display();
    }

    /*
     * 支付页面
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/alipay/accesstoken/1
     * */
    public function alipay(){
        //链接redis,每次需要的时候重新链接 做初始化
        $host = "r-bp189ba69c1505c4.redis.rds.aliyuncs.com";
        $port = 6379;
        $user ="r-bp189ba69c1505c4";
        $pwd = "Haoyuecm20161010jsbx";
        $redis = new \Redis();
        if ($redis->connect($host, $port) == false) {
            die($redis->getLastError());
        }
        /* user:password 拼接成AUTH的密码 */
        if ($redis->auth($user . ":" . $pwd) == false) {
            die($redis->getLastError());
        }
		//\Predis\Autoloader::register();
        //$redis = new \Predis\Client();
        $token=$_GET["accesstoken"];
        if($redis->exists($token))
        {
            $re=$redis ->hgetall($token);//添加到redis缓存中
            if(!empty($re)){
                if($re['uid']>0){
                    $count= M('userinfos')->where(array("userid"=>$re['uid']))->count();
                    if($count<=0){
                        $this->error("用户不存在！");
                    }
                    //把用户id返回页面上
                    $this->assign('userid', $re['uid']);// 赋值数据   集
                    $this->display();
                }else{
                    $this->error("您当前没有登录，不是本站会员用户！");
                }
            }
            else{
                $this->error("您当前没有登录，不是本站会员用户！");
            }
        }
        else{
            $this->error("您当前没有登录，不是本站会员用户！");
        }
    }

    /*
     *九度数据
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/ninedata
     * */
    public function ninedata(){
        $this->display("Index:ninedata");
    }

    /*
     *用户协议
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/useragreement
     * */
    public function useragreement(){
        $as = M('company')
            ->field('useragreement')
            ->find();
        $this->assign('lists', $as);// 赋值数据集
        $this->display("Index:useragreement");
    }

    /*
     *免责声明
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/disclaimer
     * */
    public function disclaimer(){
        $as = M('company')
            ->field('disclaimer')
            ->find();
        $this->assign('lists', $as);// 赋值数据集
        $this->display("Index:disclaimer");
    }


}