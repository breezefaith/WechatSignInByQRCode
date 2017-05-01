<html>
<head>
	<meta http-equiv="Content-Type" content="text/html"; charset="utf-8" />
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
	<title>管理页面</title>
<style>
body{
	text-align: center;  /* 页面元素居中 */
}
div#main{
	margin:0 auto;
	height: 490px;
	width: 303px;
	padding: 10px;
	border: 2px solid ;
    border-color:#000000;
}
div#group{
	margin: 0 auto;
	height: 108px;
	float: center;
	font-size: 20px;
}
div#crew{
	margin: 0 auto;
	height: 50px;
}
div#foot{
	margin: 0 auto;
	height: 50px;
}
.table{
	margin: 0 auto;
}
</style>
<script src="query.min.js"></script>
<script>
</script>
</head>
<body>
<div id="main">
<?php
include_once 'mysqlFunction.php';
header("Content-Type: text/html; charset=UTF-8");
/*连接数据库*/
$link=sql_connect();
/*获取OpenId*/
if(isset($_COOKIE['openid'])){
	$openid=$_COOKIE['openid'];
}else{
	if(isset($_GET['code'])){
	$code=$_GET['code'];
	//echo $code."<br/>";
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
//$openid="obkI1wSoiYpk1fxSddoiRQqCB_wA";
/*如果不是管理员*/
if(!$groupId=isHeadman($link,$openid))
{
	echo '<script language="javascript">';
	echo "alert('sorry,骚年你没有管理权限!');";
	echo '</script>';
	mysqli_close($link);
}
else
	{
		$count=count($groupId);//数组长度
		echo '<div id="group">';
        echo "<h3 align='center'>欢迎你:".$groupId[$count-1].'</h3>'; 
		for($i=0;$i<$count-1;$i++)
            $groupname[]=whichGroup($link,$groupId[$i]);//得到管理员所管理的小组名
		echo '<table><tr><td style="font-size: 17px">请选择小组:</td><td>';
	    echo '<select style="height:30px;width:140px;font-size: 20px;" id="Selector">';
		foreach ($groupname as $val) //循环生成下拉选项
			echo '<option style="font-size:20px;">'.$val.'</option>';
		echo '</select></td></tr></table>';
		echo '</div>';
		echo '<div id="crew">';
		echo '<table><tr><td style="font-size: 17px">请选择成员:</td><td>';
        echo '<select style="height:30px;width:140px;font-size: 20px;" id="SelCrew">';
        echo '</select></td></tr></table>';
        echo '</div>';
		echo '<div id="foot">';
		echo '<table><tr><td style="font-size: 17px">请选择操作:</td><td>';
	    echo '<select style="height:30px;width:140px;font-size: 20px;" id="SelectDone">';
	    echo '<option style="font-size:20px;" value="Del">删除</option>';
	    echo '<option style="font-size:20px;" value="toAdmin">设为管理员</option>';
	    echo '<option style="font-size:20px;" value="toCrew">设为普通用户</option>';
	    echo '<option style="font-size:20px;" value="changeName">修改备注</option>';
	    echo '</select></td><td><input type="button" style="font-size:18px;background-color:#02FCF9" id="toDo" value="执行"></td></tr>';
	    echo '</table></div>';
	    echo '<div id="Input"></div>';
	    mysqli_close($link);
    }
?>
</div>
</body>
</html>