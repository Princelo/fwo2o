<?php

// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------


/**
 * 获取餐厅列表
 $limit：分页
 $type：餐厅类型
 $param：餐厅搜索，包括城市，区域，商圈，类型
 $tag:餐厅标签
 $serach_center搜索中心的经纬度,格式为 $serach_center=array('xpoint'=>555,'ypoint'=>888);
 */
function get_dc_location_list($limit='',$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0),$tag=array(), $join='', $where='',$orderby = '',$append_field='')
{

	$search_scale=app_conf('SEARCH_SCALE');/*  搜索范围内*/
	$search_scale=5000;
	if(empty($param)){
		$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0);
	}
	$tname='sl';
	$condition =$tname.'.is_dc=1';
	$param_condition = build_dc_filter_condition($param,$tname);
	
	$condition.=' '.$param_condition;
	if($where != '')
	{
		$condition.=$where;
	}
	if($join)
		$sql = "select aa.* from (select ".$tname.".*".$append_field." from ".DB_PREFIX."supplier_location as ".$tname." ".$join." where  ".$condition." ) aa where aa.distance <= ".$search_scale;
	else
		$sql = "select aa.* from (select ".$tname.".*".$append_field." from ".DB_PREFIX."supplier_location as ".$tname." where  ".$condition." ) aa where aa.distance <= ".$search_scale;
	
	$condition2=$sql;
	if($orderby=='')
		$sql.=" order by aa.id desc";
	else
		$sql.=" order by ".$orderby;
	if($limit!=''){
		$sql.=" limit ".$limit;	
	}
	

	$dc_location_list=$GLOBALS['db']->getAll($sql,false);

	/*
	foreach( $dc_location_list as $k=>$v){
		
		
		// 排除搜索范围之外的餐厅和餐厅的配送范围小于搜索范围的餐厅  
		if(isset($serach_center['xpoint']) && isset($serach_center['ypoint'])){

		$distance=getDistance($serach_center['xpoint'],$serach_center['ypoint'],$v['xpoint'],$v['ypoint']);
		$location_max_delivery=$GLOBALS['db']->getOne("select max(`end_scale`) from ".DB_PREFIX."dc_delivery where location_id=".$v['id']);
			
			if($distance > $search_scale || $location_max_delivery < $distance){
			unset( $dc_location_list[$k]);
			}	
		}	
		
	}
	*/
	

	return array('list'=>$dc_location_list,'condition'=>$condition2);
}


/**
 * 构建商品查询条件
 * @param unknown_type $param
 * @return string
 */
function build_dc_filter_condition($param,$tname="")
{

	$area_id = intval($param['aid']);
	$quan_id = intval($param['qid']);
	$cate_id = intval($param['cid']);
	$city_id = intval($param['city_id']);
	$condition = "";
	if($city_id>0)
	{
		
		$city_pid=$GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city where id=".$city_id);
		
		if($city_pid)
		{
			if($tname)
				$condition .= " and ".$tname.".city_id in (".$city_id.",".$city_pid.")";
			else
				$condition .= " and city_id in (".$city_id.",".$city_pid.")";
		}
	}
	if($area_id>0)
	{	
		if($quan_id>0)
		{

			$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);
			$kw_unicodes[] = str_to_unicode_string($area_name);

			$kw_unicode = implode(" ",$kw_unicodes);
			//有筛选
			if($tname)
				$condition .=" and (match(".$tname.".locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";
		}
		else
		{
			$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
			$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
			$unicode_quans = array();
			foreach($quan_list as $k=>$v){
				$unicode_quans[] = str_to_unicode_string($v['name']);
			}
			$kw_unicode = implode(" ", $unicode_quans);
			if($tname)
				$condition .= " and (match(".$tname.".locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			else
				$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
		}
	}

	if($cate_id>0)
	{
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."dc_cate where id = ".$cate_id);
		$cate_name_unicode = str_to_unicode_string($cate_name);

			if($tname)
				$condition .= " and (match(".$tname.".dc_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .= " and (match(dc_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";

	}
	return $condition;
}


function get_location($tname,$location_id,$field_append){
	
	
	$location_info=$GLOBALS['db']->getRow("select ".$tname.".* ".$field_append." from ".DB_PREFIX."supplier_location as ".$tname." where id=".$location_id);
	
	return $location_info;
	
}


function get_location_delivery_info($location_id,$distance){
	

	$delivery_conf_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_delivery where location_id=".$location_id);
	if($delivery_conf_count>0){
		$delivery_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_delivery where location_id=".$location_id." and begin_scale <= ".$distance." and end_scale >= ".$distance);
		if($delivery_info){
			$delivery_start_price=$delivery_info['start_price'];
			$delivery_price=$delivery_info['delivery_price'];
			$is_free_delivery=0;    //0为不免运费
		}else{
			$delivery_start_price=0;
			$delivery_price=0;
			$is_free_delivery=2;//2为超过配送范围，不配送
		}
	
	}else{
		$delivery_start_price=0;
		$delivery_price=0;
		$is_free_delivery=1;  //1为免运费
	}
	
	return array('delivery_start_price'=>$delivery_start_price,'delivery_price'=>$delivery_price,'is_free_delivery'=>$is_free_delivery);
	
}

function get_location_menu_cate($location_id){
	
	$location_menu_cate=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id);
	$location_menu=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_menu where is_effect=1 and location_id=".$location_id);
	$location_menu_arr=array();
	$location_menu_cate_arr=array();
	foreach($location_menu_cate as $k=>$v){
		
		$location_menu_cate_arr[$v['id']]=$v;
	}
	if($location_menu){
		
		foreach($location_menu as $k=>$v){
			$location_menu_arr[$v['cate_id']]['main_cate']=$location_menu_cate_arr[$v['cate_id']];
			$location_menu_arr[$v['cate_id']]['sub_menu'][]=$v;
		}
	}
	return $location_menu_arr;
}


function get_location_notice($dclocation){
	$location_notice=array();
	$location_notice['is_close']=$dclocation['is_close'];
	$location_notice['is_dc']=$dclocation['is_dc'];
	$location_notice['is_reserve']=$dclocation['is_reserve'];
	$location_notice['dc_allow_cod']=$dclocation['dc_allow_cod'];
	$location_notice['forbid_online_pay']=$dclocation['forbid_online_pay'];
	
	return $location_notice;
}



//餐桌列表

function get_rs_item($location_id,$date){
	
	$sql = "select * from ".DB_PREFIX."dc_rs_item where is_effect = 1 and location_id = ".$location_id;
	
	$rs_list = $GLOBALS['db']->getAll($sql,false);
	
	 
	foreach($rs_list as $k=>$v){ 
		
		$sql="select * from ".DB_PREFIX."dc_rs_item_time where is_effect = 1 and item_id = ".$v['id'];
		
		
		$rs_time_list = $GLOBALS['db']->getAll($sql,false);
		
		foreach($rs_time_list as $kd=>$kv){
			
			$rs_time=explode(':', $kv['rs_time']); //餐桌的时间分割
			
			$rs_num=$rs_time[0]*3600+$rs_time[1]*60+$rs_time[2];
			
			$rs_time_list[$kd]['rs_num']=$rs_num;
			
			$now_time=explode(':', date("H:i:s"));//现在的时间分割
			
			$now_num=$now_time[0]*3600+$now_time[1]*60+$now_time[2]+3600;//延后1个小时
			
			$date_num=strtotime($date);//判断日期是否是今天 ，对今天过去的时间段做判断，给予餐桌状态
			$now = NOW_TIME;
			
			$now=date("Y-m-d ", $now);
			$now=strtotime($now);
			
			if($date_num==$now)
			{	
			
					if($now_num>$rs_num)
				{
				
				$rs_time_list[$kd]['today_ef']=2;	
				
				}
				else{
			
				$rs_time_list[$kd]['today_ef']=1;	
				
				}
			
				
			}
				
			
			$sql="select id from ".DB_PREFIX."dc_rs_item_day where rs_time = '".$date."' and time_id = ".$kv['id'];
			
			$day_id = $GLOBALS['db']->getOne($sql,false);
			$rs_time_list[$kd]['day_id']=$day_id;
		}
			
			// 时间排序 从早到晚
		 $sort = array(  
       					 'direction' => 'SORT_ASC', 
       					 'field'     => 'rs_num',      
						);
			   $arrSort = array();
			   foreach($rs_time_list as $k=>$v){
			   		
			   		$rs_num[$k]=$v['rs_num'];
				  foreach($v as $key=>$value){  
      			  	$arrSort[$key][$k] = $value;  
   								 }  
			  }		
			
			 array_multisort($arrSort[$sort['field']], constant($sort['direction']), $rs_time_list);
		
		$rs_list[$k]['rs_time_list']=$rs_time_list;
		
	}
	
	
	return array('list'=>$rs_list);
}



//菜单列表
function get_dcmenu_list($limit){
	
	$sql = "select * from ".DB_PREFIX."dc_menu where is_effect = 1";
	
	$sql.=" order by id limit ".$limit;
	$dc_menus = $GLOBALS['db']->getAll($sql,false);
	
	return array('list'=>$dc_menus);
	
	
}	



function get_dcmenu_count($limit){
	
	$sql = "select count(*) from ".DB_PREFIX."dc_menu where is_effect = 1";
	
	$count = $GLOBALS['db']->getOne($sql,false);
	
	return $count;
	
	
}

function get_location_buy_count($location_id){
		$buy_count=$GLOBALS['db']->getOne("select sum(buy_count) as buy_count from ".DB_PREFIX."dc_menu where location_id=".$location_id);
		$buy_count=($buy_count > 0)?$buy_count:0;
		return $buy_count;
}

function get_user_location_collect(){
	
	global_run();
	$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
	$get_collect_location_id=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_location_sc where user_id=".$user_id." order by add_time desc");
	$location_id_arr=array();
	foreach($get_collect_location_id as $get_collect_location_id_one){
			
		$location_id_arr[]=$get_collect_location_id_one['location_id'];
	}
	
	$collect_location=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where id in (".implode(',',$location_id_arr).") order by find_in_set(id, '".implode(',',$location_id_arr)."')");
	
	foreach($collect_location as $k=>$v){
		$collect_location[$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
		$collect_location[$k]['buy_count']=get_location_buy_count($v['id']);
		$collect_location[$k]['link_id']=$get_collect_location_id[$k]['id'];
	}
	return $collect_location;
	
}

