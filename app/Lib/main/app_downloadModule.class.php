<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------


class app_downloadModule extends MainBaseModule
{
	public function index()
	{	
		//用户app下载地址连接
		if (isios()){
			//$down_url = app_conf("APPLE_PATH");
			$down_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'ios_down_url'");
		}else{
			//$down_url = app_conf("ANDROID_PATH");
			$down_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'android_filename'");
		}	
		app_redirect($down_url);		
	}	
	
}
?>