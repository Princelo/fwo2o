<?php 
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------



function load_dc_cart_list($reload=false,$location_id='',$type=0)
{
	$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
	if(!$reload)
	{
		static $result;
		if($result)
		{
			return $result;
		}
		
		$result = es_session::get("dc_cart_result");
		if($result&&is_array($result)&&count($result)>0)
		{
			return $result;
		}
		if($location_id==''){	
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.cart_type=".$type." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and cart_type=".$type." and  user_id=".$user_id);
		}else{
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.cart_type=".$type." and c.location_id=".$location_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and cart_type=".$type." and location_id=".$location_id);
		}
		$cart_list = array();
		foreach($cart_list_res as $k=>$v)
		{	
			if($type==0){
				$v['url'] = url("index","dcrsitem&lid=".$v['location_id']);
			}else{
				$v['url'] = url("index","dcbuy&lid=".$v['location_id']);
			}	
			
			
			$cart_list[$v['id']] = $v;
		}
		if(count($cart_list)>0){
		$result = array("cart_list"=>$cart_list,"total_data"=>$total_data);	
		es_session::set("dc_cart_result", $result);
		return $result;
		}
	}
	else
	{
		if($location_id==''){
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.cart_type=".$type." and c.user_id=".$user_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and cart_type=".$type." and user_id=".$user_id);
		}else{
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.cart_type=".$type." and c.location_id=".$location_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and cart_type=".$type." and location_id=".$location_id);
		}

		$cart_list = array();
		foreach($cart_list_res as $k=>$v)
		{		
			if($type==0){
				$v['url'] = url("index","dcrsitem&lid=".$v['location_id']);
			}else{
				$v['url'] = url("index","dcbuy&lid=".$v['location_id']);
			}	
			
			$cart_list[$v['id']] = $v;
		}
		
		//有操作程序就更新购物车状态
		/*
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_cart set update_time=".NOW_TIME.",user_id = ".intval($GLOBALS['user_info']['id'])." where session_id = '".es_session::id()."'");
		*/
		if(count($cart_list)>0){
		$result = array("cart_list"=>$cart_list,"total_data"=>$total_data);
		es_session::set("dc_cart_result", $result);
		return $result;
		}
		
	}
}


function refresh_dccart_list(){
	
	if(!es_session::get("dc_cart_result"))
	{
		//session过期清空购物车
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where add_time < ".NOW_TIME."-".SESSION_TIME);
	}
	
}


function get_delivery_time(){
	$time_span=900;//15分钟一个时段
	$delivery_time=array('am'=>array("11:00","14:00"),'pm'=>array("16:00","20:00"));
	$delivery_time_span=array();
	foreach($delivery_time as $delivery_time_item){	
		$delivery_time_span[]=get_time_span(strtotime($delivery_time_item[0]),strtotime($delivery_time_item[1]),$time_span);
	}
	$delivery_time_new=array();
	foreach($delivery_time_span as $delivery_time_span_row){
		
		foreach($delivery_time_span_row as $delivery_time_span_one){
			
			$delivery_time_new[]=$delivery_time_span_one;
		}
	}	
	$delivery_time_arr=array();
	foreach($delivery_time_new as $delivery_time_row){
		if(time()+3600 <= $delivery_time_row){
		$delivery_time_arr[]= date("H:i",$delivery_time_row);
		}
	}
	return $delivery_time_arr;
}


function get_time_span($begin_time,$end_time,$time_span){
	$time_arr=array();
	$num=($end_time-$begin_time)/$time_span+1;
	for($i=0;$i<$num;$i++){	
		$time_arr[]=$begin_time+$time_span*$i;
	}
	return $time_arr;
	
}

function dc_order_total_price($location_id){
	global_run();
	$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
	$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
	$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
	
	$GLOBALS['tmpl']->assign('location_dc_table_cart',$location_dc_table_cart);
	$GLOBALS['tmpl']->assign('location_dc_cart',$location_dc_cart);
	$location_delivery_price=0;
	if(count($location_dc_table_cart)<=0 && count($location_dc_cart)>0){
		
		$consignee_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where user_id=".$user_id);

		 $xpoint = $consignee_info['xpoint']; 
		$ypoint = $consignee_info['ypoint']; 
		
		$tname='sl';
		/* 配送费  */
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = "(ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r) as distance ";
		
			$distance=$GLOBALS['db']->getOne("select ".$field_append." from ".DB_PREFIX."supplier_location as ". $tname." where ".$tname.".id=".$location_id);
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$location_delivery_info=get_location_delivery_info($location_id,$distance);
		$GLOBALS['tmpl']->assign('location_delivery_info',$location_delivery_info);
		
		if($location_delivery_info['is_free_delivery']==0){
			$location_delivery_price=$location_delivery_info['delivery_price'];
		}
		
		}
		/*  打包费计算*/
		$location_package_conf=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$location_id);
		$dc_package_info=array();

		if($location_package_conf['package_start_price']==0){
			
			$dc_package_info['is_free_package']=1;
		}elseif($location_package_conf['package_start_price']==-1){
			$dc_package_info['is_free_package']=0;
			$dc_package_info['package_price']=$location_package_conf['package_price'];
			$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
		}elseif($location_package_conf['package_start_price']>0){
			
			if($location_dc_cart['total_data']['total_price']>=$location_package_conf['package_start_price']){
				$dc_package_info['is_free_package']=1;	
			}else{
				$dc_package_info['is_free_package']=0;
				$dc_package_info['package_price']=$location_package_conf['package_price'];
				$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
			}
			
		}
		
		$GLOBALS['tmpl']->assign('dc_package_info',$dc_package_info);
	}
	
	
	$final_total_price=$location_dc_table_cart['total_data']['total_price']+$location_dc_cart['total_data']['total_price']+$dc_package_info['total_package_price']+$location_delivery_price;
	$GLOBALS['tmpl']->assign('final_total_price',$final_total_price);
	$dc_order_total_html=$GLOBALS['tmpl']->fetch('inc/dc_order_total.html');
	return $dc_order_total_html;
	
}

?>