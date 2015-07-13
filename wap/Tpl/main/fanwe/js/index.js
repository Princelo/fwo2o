$(document).ready(function(){
	if(has_location == 0 && navigator.geolocation)
	{
		 var geolocationOptions={timeout:10000,enableHighAccuracy:true,maximumAge:5000};		 
		 navigator.geolocation.getCurrentPosition(getPositionSuccess, getPositionError, geolocationOptions);
	}
});




function getPositionSuccess(p){  
	has_location = 1;//定位成功; 
    m_latitude = p.coords.latitude; //纬度
    m_longitude = p.coords.longitude;
	userxypoint(m_latitude, m_longitude);
}

function getPositionError(error){  
	switch(error.code){  
	    case error.TIMEOUT:  
	        alert("定位连接超时，请重试");  
	        break;  
	    case error.PERMISSION_DENIED:  
	        alert("您拒绝了使用位置共享服务，查询已取消");  
	        break;  
	    default:
	    	alert("定位失败");		       
	}  
}	 
//将坐标返回到服务端;
function userxypoint(latitude,longitude){	 	
		var query = new Object();
		query.m_latitude = latitude;
		query.m_longitude = longitude;
		$.ajax({
			url:geo_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
			}
			,error:function(){					
			}
		});		 		
} 	