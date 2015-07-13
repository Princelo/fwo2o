<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
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