<?php 
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

if (isset($read_api) && $read_api == true)
{
    return false;
}

define("FILE_PATH","/dh"); //文件目录
require_once '../system/system_init.php';
require_once APP_ROOT_PATH.'app/Lib/main_init.php';


function emptyTag($string)
{
		if(empty($string))
			return "";
			
		$string = strip_tags(strim($string));
		$string = preg_replace("|&.+?;|",'',$string);
		
		return $string;
}
function convertUrl($url)
{
		$url = str_replace("&","&amp;",$url);
		return $url;
}
?>