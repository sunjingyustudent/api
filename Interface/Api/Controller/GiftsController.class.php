<?php
/**
 * 礼物公开接口API
 * 基本的查询，登录，注册，等
 * 不需要验证用户身份token
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class GiftsController extends ApiController{
    public function _initialize(){
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 发礼物接口 （记录数据）
     * 测试接口：
     * */
    public function recordgifts()
    {
        
    }


}