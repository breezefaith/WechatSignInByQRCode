<?php
header("Content-Type: text/html; charset=UTF-8");
include_once 'mysqlFunction.php';
/*连接数据库*/
$link=sql_connect();
if(isset($_POST['groupname']))
{
	$Gname=$_POST['groupname'];
	$Name=showCrew($link,$Gname);
	$count=count($Name);
	for($i=0;$i<$count;$i++)//选项的value设置为用户的Openid
		echo '<option style="font-size:20px;" value="'.$Name[$i].'">'.$Name[++$i].$Name[++$i].'</option>';
}
if(isset($_POST['group_name'])&&isset($_POST['crew_id'])&&isset($_POST['action']))
{
	$group_name=$_POST['group_name'];
	$crew_id=$_POST['crew_id'];
    //$crew_id=trim($crew_id);
	$action=$_POST['action'];
	$new_name="";
	if(isset($_POST['new_name']))
		$new_name=$_POST['new_name'];//获取修改后的备注
	//$result="cc".$crew_id;
	$result=action_ForCrew($link,$group_name,$crew_id,$action,$new_name);//交给成员处理函数根据相应动作去更新数据库
	echo $result;
}
mysqli_close($link);
?>
