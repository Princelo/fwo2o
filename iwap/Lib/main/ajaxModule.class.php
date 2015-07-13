<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class ajaxModule extends MainBaseModule
{

	public function send_sms_code()
	{
		$mobile = strim($_REQUEST['mobile']);
		$unique = intval($_REQUEST['unique']);
		$data = request_api("sms","send_sms_code",array("mobile"=>$mobile,"unique"=>$unique));
		ajax_return($data);
	}
	
	
	public function close_appdown()
	{
		es_cookie::set('is_app_down',1,3600*24*7);
	}
	
	
	public function count_buy_total()
	{
		$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
		$ecvsn = $_REQUEST['ecvsn']?addslashes(trim($_REQUEST['ecvsn'])):'';
		$ecvpassword = $_REQUEST['ecvpassword']?addslashes(trim($_REQUEST['ecvpassword'])):'';
		$payment = intval($_REQUEST['payment']);
		$all_account_money = intval($_REQUEST['all_account_money']);
		
		$data = request_api("cart","count_buy_total",array("delivery_id"=>$delivery_id,"ecvsn"=>$ecvsn,"ecvpassword"=>$ecvpassword,"payment"=>$payment,"all_account_money"=>$all_account_money));
		
		$feeinfo['feeinfo'] = $data['feeinfo'];
		$GLOBALS['tmpl']->assign("data",$feeinfo);
		$ajaxdata['html'] = $GLOBALS['tmpl']->fetch("inc/cart_total.html");
		$ajaxdata['pay_price'] = $data['pay_price'];
		ajax_return($ajaxdata);
	}
	
	public function count_order_total()
	{
		$order_id = intval($_REQUEST['id']);
		$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
		$payment = intval($_REQUEST['payment']);
		$all_account_money = intval($_REQUEST['all_account_money']);
	
		$data = request_api("cart","count_order_total",array("id"=>$order_id,"delivery_id"=>$delivery_id,"payment"=>$payment,"all_account_money"=>$all_account_money));
	
		$feeinfo['feeinfo'] = $data['feeinfo'];
		$GLOBALS['tmpl']->assign("data",$feeinfo);
		$ajaxdata['html'] = $GLOBALS['tmpl']->fetch("inc/cart_total.html");
		$ajaxdata['pay_price'] = $data['pay_price'];
		ajax_return($ajaxdata);
	}

	
}
?>