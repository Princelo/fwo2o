<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

//将语言包载入JS
class LangAction extends BaseAction{
	public function js(){
		$str = "var LANG = {";
		foreach($this->lang_pack as $k=>$lang)
		{
			$str .= "\"".$k."\":\"".$lang."\",";
		}
		$str = substr($str,0,-1);
		$str .="};";
		header("Content-Type: text/javascript");
		echo $str;
	}
}
?>