<?php 
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

/**
 * 刷新会员安全登录状态
 */
function refresh_user_info()
{
	global $user_info;
	global $user_logined;
	//实时刷新会员数据
	if($user_info)
	{
		$user_info = load_user($user_info['id']);
		$user_level = load_auto_cache("cache_user_level");
		$user_info['level'] = $user_level[$user_info['level_id']]['level'];
		$user_info['level_name'] = $user_level[$user_info['level_id']]['name'];
		es_session::set('user_info',$user_info);

		$user_logined_time = intval(es_session::get("user_logined_time"));
		$user_logined = es_session::get("user_logined");
		if(NOW_TIME-$user_logined_time>=MAX_LOGIN_TIME)
		{
			es_session::set("user_logined_time",0);
			es_session::set("user_logined", false);
			$user_logined = false;
		}
		else
		{
			if($user_logined)
				es_session::set("user_logined_time",NOW_TIME);
		}
	}
}

/**
 * 前端全运行函数，生成系统前台使用的全局变量
 * 1. 定位城市 GLOBALS['city'];
 * 2. 加载会员 GLOBALS['user_info'];
 * 3. 定位经纬度 GLOBALS['geo'];
 * 4. 加载推荐人与来路
 * 5. 更新购物车
 */
function global_run()
{
	if(app_conf("SHOP_OPEN")==0)  //网站关闭时跳转到站点关闭页
	{
		app_redirect(wap_url("index","close"));
	}

	
	//处理城市
	global $city; 
	require_once APP_ROOT_PATH."system/model/city.php";
	$city = City::locate_city(intval($_REQUEST['city_id']));

	//处理经纬度
	global $geo;
	$geo = City::locate_geo($_REQUEST['m_longitude'],$_REQUEST['m_latitude']);
		
	global $ref_uid;
	
	//保存返利的cookie
	if($_REQUEST['r'])
	{
		$rid = intval(base64_decode($_REQUEST['r']));
		$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($rid)));
		es_cookie::set("REFERRAL_USER",intval($ref_uid));
	}
	else
	{
		//获取存在的推荐人ID
		if(intval(es_cookie::get("REFERRAL_USER"))>0)
			$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval(es_cookie::get("REFERRAL_USER"))));
	}
	
	//会员自动登录及输出
	global $cookie_uname;
	global $cookie_upwd;
	global $user_info;
	global $user_logined;
	global $wx_info;
	global $is_weixin; //是否为微信访问
	$is_weixin=isWeixin();
	$user_info = es_session::get('user_info');
	$wx_info = es_session::get("wx_info");
	if(empty($user_info))
	{
		//关于微信登录		
		$m_config = getMConfig();//初始化手机端配置		
		if($is_weixin)			
		{
			require_once APP_ROOT_PATH.'system/utils/weixin.php';			
			if($m_config['wx_appid']&&$m_config['wx_secrit'])
			{
				$wx_code = strim($_REQUEST['code']);
				$wx_status = intval($_REQUEST['state']);
				if($wx_code&&$wx_status)
				{
					//微信端回跳回wap
					$url =  get_current_url();
					$weixin=new weixin($m_config['wx_appid'],$m_config['wx_secrit'],SITE_DOMAIN.$url);
					$wx_info=$weixin->scope_get_userinfo($wx_code);
				}
				
				if($wx_info&&$wx_info['openid'])
				{					
					//用户未登陆		
					$wx_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where wx_openid='".$wx_info['openid']."'");				
					if($wx_user_info){
						//如果会员存在，直接登录
						auto_do_login_user($wx_user_info['user_name'],$wx_user_info['user_pwd'],false);
					}else{
						//会员不存在进入自动创建流程
						$user_data = array();
						$user_data['user_name'] = $wx_info['nickname'];
						$user_data['wx_openid'] = $wx_info['openid'];		
						$rs = auto_create($user_data);
						$user_data = $rs['user_data'];
						auto_do_login_user($user_data['user_name'],$user_data['user_pwd'],false);
					}
					$user_info = es_session::get('user_info');
				}
				else
				{
					//跳转至微信的授权页
					$url =  get_current_url();
					$weixin = new weixin($m_config['wx_appid'],$m_config['wx_secrit'],SITE_DOMAIN.$url);
					$wx_url=$weixin->scope_get_code();
					app_redirect($wx_url);
				}
			}
			else
			{
				//showErr("微信功能未开通");
			}
		}
		else
		{
			$cookie_uname = es_cookie::get("user_name")?es_cookie::get("user_name"):'';
			$cookie_upwd = es_cookie::get("user_pwd")?es_cookie::get("user_pwd"):'';
			if($cookie_uname!=''&&$cookie_upwd!=''&&!es_session::get("user_info"))
			{
				$cookie_uname = strim($cookie_uname);
				$cookie_upwd = strim($cookie_upwd);
				auto_do_login_user($cookie_uname,$cookie_upwd);
				$user_info = es_session::get('user_info');
			}
		}		
	}
	refresh_user_info();
	
	//此处是会员（商家登录状态的初始化）
	require_once APP_ROOT_PATH."system/libs/biz_user.php";
	global $cookie_biz_uname;
	global $cookie_biz_upwd;
	global $account_info;
	$account_info = es_session::get('account_info');
	
	if(empty($account_info))
	{
		$cookie_biz_uname = es_cookie::get("biz_uname")?es_cookie::get("biz_uname"):'';
		$cookie_biz_upwd = es_cookie::get("biz_upwd")?es_cookie::get("biz_upwd"):'';
		if($cookie_biz_uname!=''&&$cookie_biz_upwd!=''&&!es_session::get("account_info"))
		{
			$cookie_biz_uname = strim($cookie_biz_uname);
			$cookie_biz_upwd = strim($cookie_biz_upwd);
			do_login_biz($cookie_biz_uname, $cookie_biz_upwd);
			$account_info = es_session::get('account_info');
		}
	}
	//实时刷新会员数据
	if($account_info)
	{
		$account_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where is_delete = 0 and is_effect = 1 and id = ".intval($account_info['id']));
		if($account_info['is_main'] == 1){ //主账户取所有门店
			$account_locations = $GLOBALS['db']->getAll("select id as location_id from ".DB_PREFIX."supplier_location where supplier_id = ".$account_info['supplier_id']);
		}else
			$account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$account_info['id']);
	
		$account_location_ids = array();
		foreach($account_locations as $row)
		{
			$account_location_ids[] = $row['location_id'];
		}
		$account_info['location_ids'] =  $account_location_ids;
		$GLOBALS['account_info']['location_ids'] =  $account_location_ids;
	
		es_session::set('account_info',$account_info);
	}
	//end 商家端处理
	
	//刷新购物车
	require_once APP_ROOT_PATH."system/model/cart.php";
	refresh_cart_list();

	

	global $referer;
	//保存来路
	// 	es_cookie::delete("referer_url");
	if(!es_cookie::get("referer_url"))
	{
		if(!preg_match("/".urlencode(SITE_DOMAIN.APP_ROOT)."/",urlencode($_SERVER["HTTP_REFERER"])))
		{
			$ref_url = $_SERVER["HTTP_REFERER"];
			if(substr($ref_url, 0,7)=="http://"||substr($ref_url, 0,8)=="https://")
			{
				preg_match("/http[s]*:\/\/[^\/]+/", $ref_url,$ref_url);
				$referer = $ref_url[0];
				if($referer)
					es_cookie::set("referer_url",$referer);
			}
		}
	}
	else
	{
		$referer = es_cookie::get("referer_url");
	}
	$referer = strim($referer);

	es_cookie::delete("is_pc");
}

/**
 * 初始化页面信息，如会员登录状态的显示输出
 */
function init_app_page()
{
	if($GLOBALS['user_info'])
	{
		$GLOBALS['tmpl']->assign("is_login",1);
	}
	else
	{
		$GLOBALS['tmpl']->assign("is_login",0);
	}
	
	if ($GLOBALS['account_info']){
	    $GLOBALS['tmpl']->assign("biz_is_login",1);
	}else{
	    $GLOBALS['tmpl']->assign("biz_is_login",0);
	}
	$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
	
	$GLOBALS['tmpl']->assign("account_info",$GLOBALS['account_info']);
	
	if($GLOBALS['geo']['address'])
	$GLOBALS['tmpl']->assign("geo",$GLOBALS['geo']);
	
	$GLOBALS['tmpl']->assign("pc_url",url("index","index",array("is_pc"=>1)));
}

//编译生成css文件
function parse_css($urls)
{
	$color_cfg = require_once APP_ROOT_PATH.FOLDER_NAME."/Tpl/".APP_TYPE."/".TMPL_NAME."/color_cfg.php";
	$showurl = $url = md5(implode(',',$urls).SITE_DOMAIN);
	$css_url = 'public/runtime/statics/'.CACHE_SUBDIR.'/'.$url.'.css';
	$pathwithoupublic = 'runtime/statics/'.CACHE_SUBDIR.'/';
	$url_path = APP_ROOT_PATH.$css_url;

	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);
		
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'.CACHE_SUBDIR.'/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/'.CACHE_SUBDIR.'/',0777);
		$tmpl_path = $GLOBALS['tmpl']->_var['TMPL'];

		$css_content = '';
		foreach($urls as $url)
		{
			$css_content .= @file_get_contents($url);
		}
		$css_content = preg_replace("/[\r\n]/",'',$css_content);
		$css_content = str_replace("../images/",$tmpl_path."/images/",$css_content);
		$css_content = str_replace("./public/",SITE_DOMAIN.APP_ROOT."/public/",$css_content);
		$css_content = str_replace("@rand",time(),$css_content);
		foreach($color_cfg as $k=>$v)
		{
			$css_content = str_replace($k,$v,$css_content);
		}
		//		@file_put_contents($url_path, unicode_encode($css_content));
		@file_put_contents($url_path, $css_content);
		if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			syn_to_remote_file_server($css_url);
			$GLOBALS['refresh_page'] = true;
		}
	}
	if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		$domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
	}
	else
	{
		$domain = SITE_DOMAIN.APP_ROOT;
	}
	return $domain."/".$css_url."?v=".app_conf("DB_VERSION").".".app_conf("APP_SUB_VER");
}

/**
 *
 * @param $urls 载入的脚本
 * @param $encode_url 需加密的脚本
 */
function parse_script($urls,$encode_url=array())
{
	$showurl = $url = md5(implode(',',$urls));
	$js_url = 'public/runtime/statics/'.CACHE_SUBDIR.'/'.$url.'.js';
	$pathwithoupublic = 'runtime/statics/'.CACHE_SUBDIR.'/';
	$url_path = APP_ROOT_PATH.$js_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);
		
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'.CACHE_SUBDIR.'/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/'.CACHE_SUBDIR.'/',0777);

		if(count($encode_url)>0)
		{
			require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		}

		$js_content = '';
		foreach($urls as $url)
		{
			$append_content = @file_get_contents($url)."\r\n";
			if(in_array($url,$encode_url))
			{
				$packer = new JavaScriptPacker($append_content);
				$append_content = $packer->pack();
			}
			$js_content .= $append_content;
		}
		//		require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		//	    $packer = new JavaScriptPacker($js_content);
		//		$js_content = $packer->pack();
		@file_put_contents($url_path,$js_content);
		if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			syn_to_remote_file_server($js_url);
			$GLOBALS['refresh_page'] = true;
		}
	}
	if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		$domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
	}
	else
	{
		$domain = SITE_DOMAIN.APP_ROOT;
	}
	return $domain."/".$js_url."?v=".app_conf("DB_VERSION").".".app_conf("APP_SUB_VER");
}




function getWebAdsUrl($data){
	//2:URL广告;9:团购列表;10:商品列表;11:活动列表;12:优惠列表;14:团购明细;15:商品明细;17:优惠明细;22:商家列表;23：商家明细; 24:门店自主下单

	if($data['ctl']=="url")
	{
		$url = $data['data']['url'];
	}
	else
	$url = wap_url("index",$data['ctl'],$data['data']);

	return $url;

}




/**
 * 获取前次停留的页面地址
 * @return string url
 */
function get_gopreview()
{
	$gopreview = es_session::get("wap_gopreview");
	if($gopreview==get_current_url())
	{
		$gopreview = wap_url("index");
	}
	if(empty($gopreview))
		$gopreview = wap_url("index");
	return $gopreview;
}


/**
 * 获取当前的url地址，包含分页
 * @return string
 */
function get_current_url()
{
	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");
	$parse = parse_url($url);
	if(isset($parse['query'])) {
		parse_str($parse['query'],$params);
		$url   =  $parse['path'].'?'.http_build_query($params);
	}
	return $url;
}

/**
 * 将当前页设为回跳的上一页地址
 */
function set_gopreview()
{
	$url =  get_current_url();
	es_session::set("wap_gopreview",$url);
}


function load_sms_lesstime()
{
	$data	=	es_session::get("send_sms_code_0_ip");
	$lesstime = SMS_TIMESPAN -(NOW_TIME - $data['time']);  //剩余时间
	if($lesstime<0)$lesstime=0;
	return $lesstime;
}
?>