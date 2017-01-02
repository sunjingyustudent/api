<?php
/**
 * 帮助于反馈以及分享APP公开接口API
 * 1.提交意见反馈接口
 * 2.常见问题接口
 * 3.我的粉丝列表接口
 * 4.分享app接口
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class CompanyIntroController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 提交意见反馈接口
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/feedback/accesstoken/{accesstoken}
     * */
    public function feedback()
    {
        $feedpost = I('post.');
        if($this->checktoken($feedpost['accesstoken'])){
            $uid=$this->checktoken($feedpost['accesstoken']);
            if($uid>0){
                $fk = M('feedback');
                $feedback['userid'] = $uid;
                $feedback['content'] =$feedpost['content'];
                $feedback['qq'] = $feedpost['qq'];
                $feedback['email'] =$feedpost['email'];
                //$user['creat_time'] = date('Y-m-d H:i:s');
                $info = $fk->add($feedback);
                if ($info > 0) {
                    $this->myApiPrint('success','200');
                } else {
                    $this->myApiPrint('submit error','300');
                }
            }
            else{
                $this->myApiPrint('error:Tourists cannot add feedback','404');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find','404');
        }

    }

    /*
     * 常见问题接口[父类]
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/questionlist/accesstoken/{accesstoken}
     * */
    public function questionlist()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $vs = M('helptype')
                ->where(array('isshowpast'=>'1'))
                ->field('helptypeid,category,tdescription')
                ->select();
            if(!empty($vs))
            {
                $this->myApiPrint('success', '200', $vs);
            }
            else{
                $this->myApiPrint('don\'t find ', '404');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    /*
     * 帮助分类列表接口[子类]
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/articlelist/helptypeid/1
     * */
    public function articlelist()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $atid = I('get.helptypeid');
            $as = M('article')
                ->where(array('atype'=>$atid,'isshow'=>'1','isdelete'=>'0'))
                ->field('aautoid,theme')
                ->order('sort asc')
                ->select();
            if(!empty($as))
            {
                $this->myApiPrint('success', '200', $as);
            }
            else{
                $this->myApiPrint('don\'t find ', '404');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }

    }

    /*
     * 文章详细
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/articledetail/aautoid/9  | 4
     * */
    public function articledetail()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $atid = I('get.aautoid');
            $as = M('article')
                ->where(array('aautoid'=>$atid))
                ->field('createtime,aautoid,theme,artsouce,acontent,snapshots')
                ->select();
            if(!empty($as))
            {
                $this->myApiPrint('success', '200', $as);
            }
            else{
                $this->myApiPrint('don\'t find ', '404');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }


    /*
     * 分享app接口
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/shareapp/accesstoken/{accesstoken}
     * */
    public function shareapp()
    {
        $feedpost = I('post.');
        if($this->checktoken($feedpost['accesstoken'])){
            $uid=$this->checktoken($feedpost['accesstoken']);
            if($uid>0){
                $fk = M('sharelog');
                $feedback['userid'] = $uid;
                $feedback['artsource'] = $feedpost['artsource'];
                $feedback['shareurl'] =$feedpost['shareurl'];
                $feedback['snapshots'] =$feedpost['snapshots'];
                //$user['creat_time'] = date('Y-m-d H:i:s');
                $info = $fk->data($feedback)->add();
                if ($info > 0) {
                    $this->myApiPrint('success',200,$info);
                } else {
                    $this->myApiPrint('submit error',300);
                }
            }
            else{
                $this->myApiPrint('error:Tourists cannot add feedback',404);
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }

    }


    /*
     * 帮助分类列表接口不带分类的
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/CompanyIntro/helplist/accesstoken
     * */
    public function helplist()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $as = M('article')
                ->where(array('isshow'=>'1','isdelete'=>'0'))
                ->field('aautoid,theme')
                ->order('sort asc')
                ->select();
            if(!empty($as))
            {
                $this->myApiPrint('success', '200', $as);
            }
            else{
                $this->myApiPrint('don\'t find ', '404');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }

    }



}