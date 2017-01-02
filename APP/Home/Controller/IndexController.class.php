<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    /*
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/index
     * */
    public function index(){
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
    
    //初始化aliyunoss
    private function connectredis(){
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
        return $redis;
    }

    /*
     * 支付页面
     * http://localhost:8080/jiuducaijingwebapi/index.php/Home/Index/alipay/accesstoken/1
     * */
    public function alipay(){
        $redis = $this->connectredis();
        $token=$_GET["accesstoken"];
        var_dump($redis ->hgetall($token));exit;
        if(!$redis->exists($token)){
            $this->error("您当前没有登录，不是本站会员用户！");
        }
        $re=$redis ->hgetall($token);//添加到redis缓存中
        if(!empty($re) && $re['uid'] > 0){
            $count= M('userinfos')->where(array("userid"=>$re['uid']))->count();
            if($count <= 0){
                $this->error("用户不存在！");
            }
            //把用户id返回页面上
            $this->assign('userid', $re['uid']);// 赋值数据   集
            $this->display();
        }else{
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