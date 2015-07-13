<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class deal_detailModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data_id = intval($_REQUEST['data_id']);
		
		$data = request_api("deal","detail",array("data_id"=>$data_id));
        
		$GLOBALS['tmpl']->assign("data",$data);		
		$GLOBALS['tmpl']->display("deal_detail.html");
	}
	
	
}
?>