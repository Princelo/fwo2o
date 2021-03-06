<?php

$payment_lang = array(
	'name'	=>	'货到付款手机端',
);
$config = array(
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Mcod';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '5';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = '';
    return $module;
}

// 手机货到付款支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Mcod_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
			
		
		$sql = "select name ".
						  "from ".DB_PREFIX."deal_order_item ".					
						  "where order_id =". intval($payment_notice['order_id']);
		$title_name = $GLOBALS['db']->getOne($sql);

		$pay['pay_info'] = $title_name;
		$pay['pay_action'] = wap_url("index","uc_order#view",array("id"=>$payment_notice['order_id']));
		$pay['payment_name'] = "您已成功下单，请到货后再付款";
		$pay['pay_money'] = $money;
		$pay['class_name'] = "Mcod";
		return $pay;		

	}
	
	public function response($request)
	{}
	
	//响应通知
	function notify($request)
	{}
	
	//获取接口的显示
	function get_display_code()
	{
		return "货到付款";
	}
	
}
?>