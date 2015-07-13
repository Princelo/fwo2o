<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class noticeModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$id=intval($_REQUEST['data_id']);
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$id);		
		if (!$GLOBALS['tmpl']->is_cached('notice_detail.html', $cache_id)){
				$param=array();
				$param['id'] = intval($_REQUEST['data_id']);
				$data = request_api("notice","detail",$param);
					
		}
		$GLOBALS['tmpl']->assign("data",$data);		
		$GLOBALS['tmpl']->display("notice_detail.html",$cache_id);	
	}

}
?>