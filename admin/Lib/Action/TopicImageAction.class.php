<?php
// +----------------------------------------------------------------------
// | Fanweo2o商业系统 最新版V3.03.3285  。
// +----------------------------------------------------------------------
// | 购买本程序，请联系旺旺名：zengchengshu
// +----------------------------------------------------------------------
// | 淘宝购买地址：http://cnlichuan.taobao.com
// +----------------------------------------------------------------------

class TopicImageAction extends CommonAction{
	public function delete()
	{
		$id = intval($_REQUEST['id']);
		$tid = intval($_REQUEST['tid']);
		$data = M("TopicImage")->getById($id);
		if(!$data)$this->ajaxReturn(l("IMAGE_NOT_EXIST"),"",0);
			
		$info = $data['topic_id'].l("TOPIC_IMAGE");
//		@unlink(APP_ROOT_PATH.$data['path']);
//		@unlink(APP_ROOT_PATH.$data['o_path']);
		
		$list =M("TopicImage")->where("id=".$id)->delete();
		if ($list!==false) {
			$Model = new Model();
			$img_array=$Model->query("select path,o_path,width,height,id,name from ".DB_PREFIX."topic_image where topic_id=".$tid);
			$count_img=count($img_array);
			if($count_img>0){
				$is_img = 1;
			}else{
				$is_img = 0;
			}
	
			$img_cache= serialize($img_array);			
			$Model->query("update ".DB_PREFIX."topic set image_list = '".$img_cache."' ,has_img=".$is_img." ,image_count=".$count_img." where id = ".$tid);				
			
			save_log($info.l("DELETE_SUCCESS"),0);
			$this->ajaxReturn("","",1);
		}else{
			save_log($info.l("删除图片失败"),0);
			$this->error (l("DELETE_FAILED"),$ajax);
		}

	}
	
}
?>