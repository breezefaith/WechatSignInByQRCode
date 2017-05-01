<html>
<head>

	<title>查询页面</title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
	<style>
	body {
		text-align: center; 
	}  
	</style>
	<?php
	include_once("connect.php");
	if(isset($_COOKIE['openid'])){
		$openid=$_COOKIE['openid'];
	}else{
		if(isset($_GET['code'])){
		$code=$_GET['code'];
		}else{
			echo "NO CODE";
			exit();
		}
		$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxf7e1cc2a8ecc076b&secret=b22e67ac6eb47249597f84b13fa8dcea&code=$code&grant_type=authorization_code";
		$result=https_request($url);
		$result=json_decode($result,true);
		$openid=$result["openid"];
		setcookie("openid",$openid);
	}
	?>
	<script type="text/javascript" src="query.min.js"></script> 
	<script type="text/javascript" src="group.js"> </script> 
</head>
<body>
	<?php
	//echo "<script>alert('$openid');</script>";
	//exit();
	?>
	<div id="control" >
	</div>
	<center>
	<div id="container">
	</div>
</center>
</body>
</html>