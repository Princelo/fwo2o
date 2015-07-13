<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/biz/core/BizBaseModule.class.php';
require APP_ROOT_PATH.'app/Lib/biz/core/biz_init.php';
define("CTL",'ctl');
define("ACT",'act');

class BizApp{		
	private $module_obj;
	//网站项目构造
	public function __construct(){
		if($GLOBALS['pay_req'][CTL])
			$_REQUEST[CTL] = $GLOBALS['pay_req'][CTL];
		if($GLOBALS['pay_req'][ACT])
			$_REQUEST[ACT] = $GLOBALS['pay_req'][ACT];
		
		$module = strtolower($_REQUEST[CTL]?$_REQUEST[CTL]:"index");
		$action = strtolower($_REQUEST[ACT]?$_REQUEST[ACT]:"index");
		
		$module = filter_ctl_act_req($module);
		$action = filter_ctl_act_req($action);
		
		if(!file_exists(APP_ROOT_PATH."app/Lib/biz/".$module."Module.class.php"))
		$module = "index";
		
		require_once APP_ROOT_PATH."app/Lib/biz/".$module."Module.class.php";				
		if(!class_exists($module."Module"))
		{
			$module = "index";
			require_once APP_ROOT_PATH."app/Lib/biz/".$module."Module.class.php";	
		}
		if(!method_exists($module."Module",$action))
		$action = "index";
		
		define("MODULE_NAME",$module);
		define("ACTION_NAME",$action);
		
		$module_name = $module."Module";
		$this->module_obj = new $module_name;
		$this->module_obj->$action();
	}
	
	public function __destruct()
	{
		unset($this);
	}
}
?>