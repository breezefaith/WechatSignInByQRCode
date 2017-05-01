<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
    <title>动态防拍安全签到二维码</title>
    <script type="text/javascript" src="jquery-1.8.0.min.js"></script>
    <script type="text/javascript" src="jquery.qrcode.min.js"></script>
	<style>
	body {
	text-align: center;  /* 页面元素居中 */
	} 
	a{text-decoration : none} 
	</style>

</head>
<body>
		<?php
		include_once("../mysqlFunction.php");
		$link=sql_connect();
		$flag=2;
		$groupName="九班";
		$openid="obkI1wf42dssxE0yEZEFVmR7hLn8";
		date_default_timezone_set('PRC');
		//$stamp=time();
		if(empty($_GET)){
			echo "Error!";
			exit;
		}else{
			$flag=$_GET['flag'];
			$groupName=urldecode($_GET['group']);
			//echo "<script>alert($groupName);</script>";
			$openid=$_GET['openid'];
			$createStamp=$_GET['createstamp'];
		}
		$sql="select Name from {$groupName} where Openid='{$openid}' limit 1";
		if(!$result=_select_data($link,$sql)){
			echo "mysql error".mysqli_error($link);
			exit();
		}
		if(!$row=mysqli_fetch_assoc($result)){
			echo "No data!";
			exit();
		}
		$headmanName=$row['Name'];
		echo "小组：".$groupName."<br/>";
		echo "管理员：".$headmanName."<br/>";
		echo "生成时间：".date("Y-m-d H:i:s",$createStamp)."<br/>";
	?>
	<br/>
<div id="code"></div>

<script type="text/javascript"  charset="UTF-8">
	var flag='<?php echo $flag; ?>';
	var groupName='<?php echo $groupName; ?>';
	var openid='<?php echo $openid; ?>';
	var serverTime=Number("<?php echo time(); ?>");
	var createTime=Number("<?php echo $createStamp; ?>");
	var localTime=parseInt((new Date()).getTime()/1000);
	var timeSub=localTime-serverTime;//存储本地时间与服务器时间的时间差
	//console.log(timeSub);
	var str=null;
	str="flag="+flag+"&createstamp="+createTime+"&updatestamp="+serverTime+"&group="+groupName+"&openid="+openid;
	//console.log(str);
	$('#code').qrcode("http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?"+str); //任意字符串 
	setInterval("qrcode(flag,groupName,openid,str)",2000);//1000为1秒钟
	function qrcode(flag,group,openid,str){
		$('#code').empty();
		var stamp=parseInt((new Date()).getTime()/1000)-timeSub;//利用时间差计算出服务器时间
		str="flag="+flag+"&createstamp="+createTime+"&updatestamp="+stamp+"&group="+group+"&openid="+openid;
		//console.log(str);
		//alert(str);
		str=encodeURI(str);
		$('#code').qrcode("http://weixin.qq.com/r/uDqehu-EO7JEravl92_q?"+str); //任意字符串 
	}
</script>
<br/>
<p>
注：请用微信公众号内的“扫一扫”按钮进行扫码
</p>
</body>
</html>