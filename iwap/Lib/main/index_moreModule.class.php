<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class index_moreModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		
		$data = request_api("index","more");

		foreach($data['indexs'] as $k=>$v)
		{
			$data['indexs'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		$GLOBALS['tmpl']->assign("data",$data);
		
		$GLOBALS['tmpl']->display("index_more.html");
	}
	
	
}
?>