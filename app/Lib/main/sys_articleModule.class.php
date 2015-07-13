<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class sys_articleModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME."system_article".trim($_REQUEST['act']));		
		if (!$GLOBALS['tmpl']->is_cached('system_article.html', $cache_id)){
	
		
			$id = intval($_REQUEST['act']);
			if($id==0)app_redirect(APP_ROOT."/");	
			
			$article = $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.id = ".$id." and a.is_effect = 1 and a.is_delete = 0");		
			
			if(!$article||$article['type_id']!=3){
				app_redirect(APP_ROOT."/");
			}
			
			$GLOBALS['tmpl']->assign("article",$article);
			$seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
		}		
		$GLOBALS['tmpl']->display("system_article.html",$cache_id);
		
	}
	
	

	
	

	
	
}
?>