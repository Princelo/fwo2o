$(document).ready(function(){	
	load_ofc("sale_line_data_chart",sale_line_data_url,"100%",350);
	load_ofc("sale_refund_data_chart",sale_refund_data_url,"100%",350);
	
	var html = '<div id="version_tip">最新版本 - http://www.fanwe.com<br>软件已授权</div>';
	$("#version_tip").html(html);	
	
});