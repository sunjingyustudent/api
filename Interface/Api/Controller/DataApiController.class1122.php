<?php
namespace Api\Controller;

use Common\Controller\ApiController;

class DataApiController extends ApiController {
	
     public function _initialize(){
        header('Content-Type: text/html; charset=utf-8');
    }
    /*
     * 取redis股指key=1中数据
     * */
    public function index(){
		$host = "120.26.140.39";
        $port = 6379;
        $redis = new \Redis();
		$ws = $redis->get('hq-index-futures');
        if ($redis->connect($host, $port) == false) {
            die($redis->getLastError());
        }
		
        echo $ws;
    }
/*
     * 取redis主力合约 行情 key=2中数据
     * */
    public function futures(){
		\Predis\Autoloader::register();
        $redis = new \Predis\Client();
		$ws = $redis->get('hq-index-futures');
		//var_dump($ws);
		//exit();
		if($ws){
			$ws = json_decode($ws,true);
			return $this->myApiPrint("success","200",$ws);
			//var_dump($ws);
		//exit();
			//$ws1 = explode(",",$ws['IF1611']);
			//dump($ws1);
			//$array['zuo'] = $ws1[0];//昨收
			//$array['jin'] = $ws1[1];//今开
			//$array['gao'] = $ws1[2];//最高
			//$array['di'] = $ws1[3];//最低
			//$array['jin'] = $ws1[4];//今开
			//$array['buy'] = $ws1[5];//买入
			//$array['pay'] = $ws1[6];//卖出
		   //// $array['zuo'] = $ws1[0];//昨收
			//$v['IF1611'] = $array;
			//$ws1 = explode(",",$ws['IC1611']);
			//$array['zuo'] = $ws1[0];//昨收
			//$array['jin'] = $ws1[1];//今开
			//$array['gao'] = $ws1[2];//最高
			//$array['di'] = $ws1[3];//最低
			//$array['jin'] = $ws1[4];//今开
			//$array['buy'] = $ws1[5];//买入
			//$array['pay'] = $ws1[6];//卖出
			//$v['IC1611'] = $array;
			//$ws1 = explode(",",$ws['IH1611']); 
		//	$array['zuo'] = $ws1[0];//昨收
			//$array['jin'] = $ws1[1];//今开
			//$array['gao'] = $ws1[2];//最高
			//$array['di'] = $ws1[3];//最低
			//$array['jin'] = $ws1[4];//今开
			//$array['buy'] = $ws1[5];//买入
			//$array['pay'] = $ws1[6];//卖出
			//$v['IH1611'] = $array;
			//$ws = json_encode($v,true);
			//return $this->myApiPrint("success","200",$v);
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }
    /*
     * 取redis贵金属key=3中数据
     * */
    public function metal(){
        \Predis\Autoloader::register();
        $redis = new \Predis\Client();
		$ws = $redis->get('hq-metal');
		if($ws){
			$ws = json_decode($ws,true);
			return $this->myApiPrint("success","200",$ws);
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }
    /*
     * 取贵金属2 股指key=4中数据
     * */
    public function stock(){
      
		\Predis\Autoloader::register();
        $redis = new \Predis\Client();
		$ws = $redis->get('hq-stock');
            $ws=str_replace("\\\"","",$ws);
         
             
		if($ws){
        $ws = json_decode($ws,true);
        //$ws1 = explode(",",$ws['深证成指']);
        //$array['symbol'] = '深证成指';//urlencode('深证成指');
        //$array['open'] = $ws1[0];//今开
        //$array['prec'] = $ws1[1];//昨收
        //$array['trade'] = $ws1[2];//当前
        //$array['high'] = $ws1[3];//最高价
        //$array['low'] = $ws1[4];//最低价
        //$array['volume'] = $ws1[9];//成交量
        //$array['transaction'] = $ws1[10];//成交额
        //$v[] = $array;
        //$ws1 = explode(",",$ws['沪深300']);
        //$array['symbol'] = '沪深300';//urlencode('沪深300');
        //$array['open'] = $ws1[0];//今开
        //$array['prec'] = $ws1[1];//昨收
        //$array['trade'] = $ws1[2];//当前
        //$array['high'] = $ws1[3];//最高价
        //$array['low'] = $ws1[4];//最低价
        //$array['volume'] = $ws1[9];//成交量
        //$array['transaction'] = $ws1[10];//成交额
        //$v[] = $array;
        //$ws1 = explode(",",$ws['上证指数']);
        //$array['symbol'] = '上证指数';//urlencode('上证指数');
        //$array['open'] = $ws1[0];//今开
        //$array['prec'] = $ws1[1];//昨收
        //$array['trade'] = $ws1[2];//当前
        //$array['high'] = $ws1[3];//最高价
        //$array['low'] = $ws1[4];//最低价
        //$array['volume'] = $ws1[9];//成交量
        //$array['transaction'] = $ws1[10];//成交额
        //$v[] = $array;
        //$ws1 = explode(",",$ws['创业板指']);
        //$array['symbol'] = '创业板指';//urlencode('创业板指');
        //$array['open'] = $ws1[0];//今开
        //$array['prec'] = $ws1[1];//昨收
        //$array['trade'] = $ws1[2];//当前
        //$array['high'] = $ws1[3];//最高价
        //$array['low'] = $ws1[4];//最低价
        //$array['volume'] = $ws1[9];//成交量
        //$array['transaction'] = $ws1[10];//成交额
        //$v[] = $array;
        //$ws1 = explode(",",$ws['I100']);
        //$array['symbol'] = 'I100';//urlencode('I100');
        //$array['open'] = $ws1[0];//今开
        //$array['prec'] = $ws1[1];//昨收
        //$array['trade'] = $ws1[2];//当前
        //$array['high'] = $ws1[3];//最高价
        //$array['low'] = $ws1[4];//最低价
        //$array['volume'] = $ws1[9];//成交量
        //$array['transaction'] = $ws1[10];//成交额
        //$v[] = $array;
        //$ws =  urldecode(json_encode($v,true));
			return $this->myApiPrint("success","200",$ws);
        }else{
            return $this->myApiPrint('accesstoken don\'t find',404);
        }
    }
     

}