<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class user_centerModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$data = request_api("user_center","index",$param);
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		
		//print_r($data);exit;
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("user_center.html");
	}
	
	
}
?>