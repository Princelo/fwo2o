<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

require_once(APP_ROOT_PATH.'system/libs/msg.php');
class notify_msg implements msg 
{		
	public function send_msg($user_id,$content,$data_id)
	{
		$msg = array();
		$msg['content'] = $content;
		$msg['user_id'] = $user_id;
		$msg['create_time'] = NOW_TIME;
		$msg['type'] = "notify";
		$msg['data_id'] = $data_id;

		$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg,"INSERT","","SILENT");
	} 	

	public function load_msg($msg)
	{
		$msg['title'] = "通知";
		$msg['short_title'] = msubstr($msg['title']);
		return $msg;
	}
	
	public function load_type()
	{
		return "用户事件通知";
	}
}
?>