<?php
/**
 * 设置公开接口API
 * 1.关于我们接口
 * 2.android版检查是否是最新版本接口
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class SetupController extends ApiController
{
    public function _initialize()
    {
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     *关于我们
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/Setup/aboutus/accesstoken/值/versiontype/1
     * */
    public function aboutus()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $versiontype = I('get.versiontype');
            if(!empty($versiontype))
            {
                //查询公司信息表
                $where['versiontype']=$versiontype;
                $where['isenables']='1';
                $va = M('company')
                    ->field('companytel,companyemail,addresscn,copyright')
                    ->select();
                $vs = M('version')
                    //->where(array('versiontype'=>$versiontype,'isenables'=>'1'))
                    ->where($where)
                    ->field('versinname,apkurl,versionnum')
                    ->select();
                $cc['versinname']=$vs[0]["versinname"];
                $cc['apkurl']=$vs[0]["apkurl"];
                $cc['companytel']=$va[0]["companytel"];
                $cc['companyemail']=$va[0]["companyemail"];
                $cc['addresscn']=$va[0]["addresscn"];
                $cc['copyright']=$va[0]["copyright"];
                $cc['versionnum']=$vs[0]["versionnum"];
                if($cc)
                {
                    $this->myApiPrint('success', '200', $cc);
                }
                else{
                    $this->myApiPrint('don\'t find ', '404');
                }
            }
            else
            {
                $this->myApiPrint('data error', '300');
            }
        }
        else{
            $this->myApiPrint('accesstoken don\'t find',404);
        }
    }

    /*
     * 是否是最新版本
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/Setup/latestversion
     * */
    public function latestversion()
    {
        $atoken = I('get.accesstoken');
        if($this->checktoken($atoken)){
            $vs = M('version')
                ->where(array('versiontype'=>'1','isenables'=>'1','isdelete'=>'0'))
                ->field('versinname,apkurl','versionnum','versiondes')
                ->select();
            //var_dump($vs);
            if(!empty($vs)){
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
     * IOS是否是最新版本(推送)
     * 测试连接:
     * http://localhost:8080/jiuducaijingwebapi/Api/Setup/iosversion
	 * 1:显示 0：不显示
     * */
    public function iosversion()
    {
        $status=1;
        $this->myApiPrint('success', '200', $status);
    }
}