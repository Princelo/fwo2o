<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class dealModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data_id = intval($_REQUEST['data_id']);
		if($data_id==0)
			$data_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".strim($_REQUEST['data_id'])."'"));
		
		$data = request_api("deal","index",array("data_id"=>$data_id));
		
		if(intval($data['id'])==0)
		{
			app_redirect(wap_url("index"));
		}
		
		$GLOBALS['tmpl']->assign("data",$data);		
		$GLOBALS['tmpl']->display("deal.html");
	}
	
	public function add_collect(){
	    global_run();
	    init_app_page();
	
	
	    $param=array();
	    $param['id'] = intval($_REQUEST['id']);
	    $data = request_api("deal","add_collect",$param);
	    ajax_return($data);
	}
	
}
?>