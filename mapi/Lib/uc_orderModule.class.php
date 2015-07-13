<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_orderModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的抽奖
	 * 输入：
	 * page:int 当前的页数
	 * pay_status:int 支付状态 0未支付 1已支付
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * item:array 订单列表
	 * Array(
	 *    Array
                (
                    [id] => 52 int 订单ID
                    [order_sn] => 2015050405530018 string 订单编号
                    [order_status] => 0 int 订单状态 0:未结单 1:结单(将出现删除订单按钮)
                    [pay_status] => 0 int 支付状态 0:未支付(出现取消订单按钮) 1:已支付
                    [create_time] => 2015-05-04 17:53:00  string 下单时间
                    [pay_amount] => 0 float 已付金额
                    [total_price] => 16.9 float 应付金额
                    [c] => 1 int 商品总量
                    [deal_order_item] => Array
                        (
                            [0] => Array
                                (
                                    [id] => 112 int 订单表中的商品ID
                                    [deal_id] => 22 int 商品ID，用于跳到商品页
                                    [deal_icon] => http://192.168.1.41/o2onew/public/attachment/201502/26/11/54ee909199d43_244x148.jpg 122x74 string 商品图
                                    [name] => 仅售14.9元！价值66元的雨含浴室防滑垫1张，透明材质，环保无毒，两色可选，带吸盘，选择它给您的家人多一份关爱 string 商品全名
                                    [sub_name] => 雨含浴室防滑垫  string 商品短名
                                    [number] => 1 int 购买数量
                                    [unit_price] => 14.9 float 单价
                                    [total_price] => 14.9 float 总价
                                    [dp_id] => int 点评ID ，ID大于0表示已点评
                                    [consume_count] => int 消费数 大于0表示可以点评
                                )

                        )

                    [status] => 未支付 string 订单状态
                )
          )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * page_title:string 页面标题
	 */
	public function index()
	{
		$root = array();		
		/*参数初始化*/
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			
		$pay_status = intval($GLOBALS['request']['pay_status']);
		$condition = " do.pay_status = 2 ";
		if($pay_status==0)
			$condition = " do.pay_status <> 2 ";
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;	
		}
		else
		{
			$root['user_login_status'] = $user_login_status;	
			
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
    		require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
			
			$sql = "select do.* from ".$order_table_name." as do where do.is_delete = 0 and ".
			" do.user_id = ".$user_id." and do.type = 0 and ".$condition."  order by do.create_time desc limit ".$limit;		
			$sql_count = "select count(*) from ".$order_table_name." as do where do.is_delete = 0 and ".
			" do.user_id = ".$user_id." and do.type = 0 and ".$condition;
			
			$list = $GLOBALS['db']->getAll($sql);		
			$count = $GLOBALS['db']->getOne($sql_count);
		
				
				
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
				$order_item = array();
				$order_item['id'] = $v['id'];
				$order_item['order_sn'] = $v['order_sn'];
				$order_item['order_status'] = $v['order_status'];
				$order_item['pay_status'] = $v['pay_status'];
				$order_item['create_time'] = to_date($v['create_time']);
				$order_item['pay_amount'] = round($v['pay_amount'],2);
				$order_item['total_price'] = round($v['total_price'],2);
				if($v['deal_order_item'])
				{
					$list[$k]['deal_order_item'] = unserialize($v['deal_order_item']);				
				}
				else
				{
					$order_id = $v['id'];
					update_order_cache($order_id);
					$list[$k]['deal_order_item'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
				}
				$c = 0;
				foreach($list[$k]['deal_order_item'] as $key=>$row)
				{
					$c+=intval($row['number']);
				}
				$order_item['c'] = $c;
				foreach($list[$k]['deal_order_item'] as $kk=>$vv)
				{
					$deal_item = array();	
					$deal_item['id'] = $vv['id'];
					$deal_item['deal_id'] = $vv['deal_id'];
					$deal_item['deal_icon'] = get_abs_img_root(get_spec_image($vv['deal_icon'],122,74,1));
					$deal_item['name'] = $vv['name'];
					$deal_item['sub_name'] = $vv['sub_name'];
					$deal_item['number'] = $vv['number'];
					$deal_item['unit_price'] = round($vv['unit_price'],2);
					$deal_item['total_price'] = round($vv['total_price'],2);
					$deal_item['consume_count'] = intval($vv['consume_count']);
					$deal_item['dp_id'] = intval($vv['dp_id']);
					
					$order_item['deal_order_item'][$kk] = $deal_item;
				}
				
				//开始处理订单状态
				$order_status = "";				
				if($v['order_status'] == 1) //结单的订单显示说明
				$order_status = "订单已完结";
				else
				{
					if($v['pay_status'] != 2)
					{
						$order_status = "未支付";
					}
					else
					{
						$order_status = "已支付";
					}
				}				
				$order_item['status'] = $order_status;
				//订单状态
				
				$data[$k] = $order_item;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['pay_status'] = $pay_status;
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		
		if($pay_status==0)
			$root['page_title'].="未付款订单";
		else
			$root['page_title'].="我的订单";
		output($root);
	}	
	
	
	/**
	 * 取消删除订单接口
	 * 
	 * 输入
	 * id: int 订单ID
	 * 
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 */
	public function cancel()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			 $root['user_login_status'] = $user_login_status;
		}
		else
		{
			$root['user_login_status'] = $user_login_status;
			$id = intval($GLOBALS['request']['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_delete = 1 where (order_status = 1 or pay_status = 0) and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
				if($GLOBALS['db']->affected_rows())
				{
					require_once APP_ROOT_PATH."system/model/deal_order.php";
					//开始退已付的款
					if($order_info['pay_status']==0&&$order_info['pay_amount']>0)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_amount = 0,ecv_id = 0,ecv_money=0,account_money = 0 where id = ".$order_info['id']);
						require_once APP_ROOT_PATH."system/model/user.php";
						if($order_info['account_money']>0)
						{
							modify_account(array("money"=>$order_info['account_money']), $order_info['user_id'],"取消订单，退回余额支付 ");
							order_log("用户取消订单，退回余额支付 ".$order_info['account_money']." 元", $order_info['id']);
						}
						if($order_info['ecv_id'])
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count - 1 where id = ".$order_info['ecv_id']);
							order_log("用户取消订单，代金券退回 ", $order_info['id']);
						}
	
					}
					over_order($order_info['id']);
					
					output($root,1,"订单删除成功");
				}
				else
				{
					output($root,0,"订单删除失败");
				}
			}
			else
			{
				output($root,0,"订单不存在");
			}
		}
	}
	
}
?>