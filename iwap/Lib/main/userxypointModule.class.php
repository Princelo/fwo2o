<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class userxypointModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		if($GLOBALS['geo']['xpoint']==0&&$GLOBALS['geo']['ypoint']==0)
		{
			request_api("userxypoint","index");
		}		
		ajax_return(array("status"=>true));
	}
	
	
}
?>