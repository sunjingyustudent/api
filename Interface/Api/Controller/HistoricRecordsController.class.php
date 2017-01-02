<?php
/**
 * 观看历史公开接口API
 * 1.观看历史记录列表接口
 * 2.站内信（公告）接口
 *
 */
namespace Api\Controller;

use Common\Controller\ApiController;

class HistoricRecordsController extends ApiController
{
    public function _initialize()
    {
        //模块初始化，重写父类方法，避免该模块进入token验证
    }

    /*
     * 观看历史公开接口API
     *
     *
     */
    public function historyvideo()
    {
        $postvideo = I('get.');
        if(!empty($postvideo)) {
            $atoken = $postvideo['accesstoken'];
           if ($this->checktoken($atoken)) {
           // if(1){
                $webcast = M('webcast');
                    $roomvideo['roomid'] =  $postvideo['roomid'];
                    $roomvideo['isdelete'] = '0';
                    $videolist = $webcast->where($roomvideo)->field('createtime,updatetime,videoname,videotitle,videodescribe,roomid,starttime,endtime,webcastkey,webcoverurl,sort,likes,webcasts')->select();
                    if ($videolist) {
                        $this->myApiPrint('success', '200', $videolist);
                    } else {
                        $this->myApiPrint('add error ', '300');
                    }
            } else {
                $this->myApiPrint('accesstoken don\'t find', 404);
            }
        }else{
            $this->myApiPrint('数据传输错误 ', '300');
        }
    }
}