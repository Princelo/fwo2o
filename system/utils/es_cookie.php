<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class es_cookie 
{
    // 判断Cookie是否存在
    static function is_set($name) {
        return isset($_COOKIE[$name]);
    }

    // 获取某个Cookie值
    static function get($name) {
        $value   = $_COOKIE[$name];
        return $value;
    }

    // 设置某个Cookie值
    static function set($name,$value,$expire='',$path='',$domain='') {   
    	$path = $GLOBALS['distribution_cfg']['COOKIE_PATH'];
     	$domain = $GLOBALS['distribution_cfg']['DOMAIN_ROOT'];
        $expire =   !empty($expire)?NOW_TIME+$expire:0;
        setcookie($name, $value,$expire,$path,$domain,false,true);
    }

    // 删除某个Cookie值
    static function delete($name) {
        es_cookie::set($name,'',0);
    }

    // 清空Cookie值
    static function clear() {
        unset($_COOKIE);
    }
}
?>