<?php
//include_once("mysqlFunction.php");
function delete_user($openid){//对取消关注的用户删除其全部记录
	$link=sql_connect();
	$sql="select GroupId from Headman where Openid='$openid'";
	//echo $sql;
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$groupId[]=$row['GroupId'];//获取该用户所担任管理员的小组id
	}
	$groupName_h=NULL;
	for($i=0;$i<count($groupId);$i++){//获取其担任管理员的小组名
		$sql="select GroupName from Groups where Id='$groupId[$i]' limit 1";
		$row=mysqli_fetch_assoc(mysqli_query($link,$sql));
		$groupName_h[]=$row['GroupName'];
	}
	/*echo "管理员查询结果：\n";
	var_dump($groupName_h);
	echo "<br/>";*/
	$groupName_m=NULL;
	$sql="select GroupName from Member where Openid='$openid'";
	//echo $sql;
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$groupName_m[]=$row['GroupName'];//获取该用户担任普通成员的小组名
	}
	/*echo "普通成员查询结果：\n";
	var_dump($groupName_m);
	echo "<br/>";
	echo "<br/>";*/
	if(empty($groupName_h)&&empty($groupName_m)){
		echo "不属于任何小组";
		return;
	}
	$groupNum_h=count($groupName_h);//获取其担任管理员的小组个数
	for($i=0;$i<$groupNum_h;$i++){//删除记录
		$sql="delete from $groupName_h[$i] where Openid='$openid'";
		if(!_delete_data($link,$sql)){
			echo mysqli_error($link);
			return;
		}
		$row=mysqli_fetch_assoc(_select_data($link,"select * from $groupName_h[$i]"));
		if(empty($row)){//如果这个小组的表中已经没有了成员则直接删掉该小组的记录
			_drop_table($link,"drop table $groupName_h[$i]");
			$sql="delete from Groups where GroupName='$groupName_h[$i]'";
			if(!_delete_data($link,$sql)){
				echo mysqli_error($link);
				return;
			}
		}
	}
	$sql="delete from Headman where Openid='$openid'";//删除Headman表中的记录
	if(!_delete_data($link,$sql)){
		echo mysqli_error($link);
		return;
	}
	$groupNum_m=count($groupName_m);//获取其担任普通成员的小组个数
	for($i=0;$i<$groupNum_m;$i++){//删除记录
		$sql="delete from $groupName_m[$i] where Openid='$openid'";
		if(!_delete_data($link,$sql)){
			echo mysqli_error($link);
			return;
		}
		$row=mysqli_fetch_assoc(_select_data($link,"select * from $groupName_m[$i]"));
		if(empty($row)){//如果这个小组的表中已经没有了成员则直接删掉该小组的记录
			_drop_table($link,"drop table $groupName_m[$i]");
			$sql="delete from Groups where GroupName='$groupName_m[$i]'";
			if(!_delete_data($link,$sql)){
				echo mysqli_error($link);
				return;
			}
		}
	}
	$sql="delete from Member where Openid='$openid'";//删除Member表中的记录
	if(!_delete_data($link,$sql)){
		echo mysqli_error($link);
		return;
	}
	return "删除成功";
	//$sql="delete from Headman where Openid='$openid'";
}
function signInByScan($object,$parameter){//扫码签到的处理
	$flag=$parameter['flag'];
	date_default_timezone_set('PRC');//设置东八区
	$createStamp=$parameter['createstamp'];//二维码创建时间
	$updateStamp=$parameter['updatestamp'];
	$time=date("Y-m-d H:i:s","$object->CreateTime");
	$groupName=$parameter['group'];//小组名
	$headmanOpenid=$parameter['openid'];//组长的openid
	$openid=$object->FromUserName;//签到人的openid
	if($headmanOpenid==$openid){
		return "sorry，负责人不可给自己签到";
	}
	if(($object->CreateTime-$updateStamp)>5){
		//考虑到实际的网络延迟，虽然二维码刷新时间为2s，但允许请求时间与二维码生成时间的差值为5s而不是2s
		return "二维码已过期,不在场怎么可能成功签到\n若您在场则请重新扫描";
	}
	$link=sql_connect();
	$sql="select Id from {$groupName}  where Openid='{$openid}' limit 1";
	$row=mysqli_fetch_assoc(_select_data($link,$sql));
	if(empty($row)){
		return "sorry,您不是小组 $groupName 成员";
	}
	$sql="select SignInLatestTime from {$groupName} where Openid='{$openid}' limit 1";
	$row=mysqli_fetch_assoc(_select_data($link,$sql));
	$array=json_decode($row['SignInLatestTime'],true);
	if(!empty($array["$createStamp"])){
		mysqli_close($link);
		return "您已经签到过了，不可重复签到";
	}
	$array["$createStamp"]="$object->CreateTime";
	$json=json_encode($array);
	$sql="update {$groupName} set SignInTime=concat(SignInTime,',','{$json}'),SignInLatestTime='{$json}',SignInTimes=SignInTimes+1,
		SignInHeadman=concat(SignInHeadman,',{$headmanOpenid}') where Openid='{$openid}' limit 1";//本小组中组员的数据更新
	if(!_update_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$sql="update Groups set LatestNumber=LatestNumber+1 where GroupName='{$groupName}' limit 1";//当前签到人数+1
	if(!_update_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$sql="select LatestNumber from Groups where GroupName='{$groupName}' limit 1";
	if(!$result=_select_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$row=mysqli_fetch_assoc($result);
	if($row['LatestNumber']==1){//如果有一个人完成签到，则给负责人也签到
		$sql="update {$groupName} set SignInTime=concat(SignInTime,',','{$json}'),SignInLatestTime='{$json}',SignInTimes=SignInTimes+1,
		SignInHeadman=concat(SignInHeadman,',{$headmanOpenid}') where Openid='{$headmanOpenid}' limit 1";//本小组中本签到负责人的数据更新
		if(!_update_data($link,$sql)){
			$error=mysqli_error($link);
			mysqli_close($link);
			return $error;
		}
		$sql="update Groups set LatestNumber=LatestNumber+1 where GroupName='{$groupName}' limit 1";//当前签到人数+1
		if(!_update_data($link,$sql)){
			$error=mysqli_error($link);
			mysqli_close($link);
			return $error;
		}
		mysqli_close($link);
		return "您和负责人签到成功 ".$time;
	}
	mysqli_close($link);
	return "签到成功 ".$time;
}
function signInByScanCrossScreen($object,$parameter){//扫描跨屏二维码签到的处理
	//return $parameter['openid'];
	$content=signInByScan($object,$parameter);
	if(gettype(strpos($content,"签到成功"))=="boolean"){//签到未成功
		return $content;
	}
	$groupName=$parameter['group'];//小组名
	$link=sql_connect();
	$sql="select Name from $groupName where Openid='{$object->FromUserName}' limit 1";
	if(!$result=_select_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$row=mysqli_fetch_assoc($result);
	$content_s=$row['Name'];
	$headmanOpenid=$parameter['openid'];
	$sql="select PageId from Headman where Openid='{$headmanOpenid}' limit 1";
	if(!$result=_select_data($link,$sql)){
		return mysqli_error($link);
	}
	$row=mysqli_fetch_assoc($result);
	if(empty($row)){
		return "未设置pageId";
	}
	$pageId=$row['PageId'];
	$socket="http://websocket.dream.ren:1996/?type=publish&content=$content_s&to=$pageId";
	$result=https_request($socket);
	return $content;
}
function changeName($openid,$name){
	$link=sql_connect();
	$sql="select table_name from information_schema.COLUMNS where table_schema='test' and column_name='Openid'";//查询含有Openid列的所有表
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$sql="update {$row['table_name']} set Name='{$name}' where Openid='{$openid}'";//修改Name值
		if(!_update_data($link,$sql)){
			mysqli_close($link);
			return 0;
		}
	}
	mysqli_close($link);
	return 1;
}
function joinInGroup($openid,$groupId){
	$link=sql_connect();
	///////////////////////////////////////////
	$name="";
	$sql="select table_name from information_schema.COLUMNS where table_schema='test' and column_name='Openid'";//查询含有Openid列的所有表
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$sql="select Name from {$row['table_name']} where Openid='{$openid}' limit 1";//查询指定openid已使用的最新姓名
		if(!$resultName=_select_data($link,$sql)){
			$error=mysqli_error($link);
			mysqli_close($link);
			return "加入小组失败\n".$error;
		}
		$rowName=mysqli_fetch_assoc($resultName);
		$name=$rowName['Name'];
		if($name!="")//$name不为空及查询到最新姓名
			break;
	}
	///////////////////////////////////////////
	$sql="select Id,GroupName from Groups where Id={$groupId}";//查找对应Id的小组名即表名
	$result=_select_data($link,$sql);
	if(!$row=mysqli_fetch_assoc($result)){
		mysqli_close($link);
		return "抱歉,没有这个小组";
	}
	$GroupId=$row['Id'];
	$GroupName=$row['GroupName'];
	$Id=getId($link,"{$GroupName}")+1;
	$sql="insert into {$GroupName}(Id,Openid,Name,Status,SignInTime,SignInHeadman,SignInInfo,SignInTimes,Es)values('{$Id}','{$openid}',
	'$name','1','','','','0','')";
	if(!_insert_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return "加入小组 $GroupName 失败，您已存在于这个小组\n";
	}
	$sql="insert into Member(Openid,Name,GroupName)values('$openid','$name','$GroupName')";
	if(!_insert_data($link,$sql)){
		$error=mysqli_error($link);
		_delete_data($link,"delete from {$GroupName} where Openid='{$openid}'");
		mysqli_close($link);
		return "加入小组 $GroupName 失败\n您已存在于这个小组n";
	}
	mysqli_close($link);
	if($name==""){
		return "加入小组 $GroupName 成功\n您的默认备注为空\n请回复“姓名+您的姓名”修改您在小组的备注\n例：姓名张三";
	}else{
		return "加入小组 $GroupName 成功\n您的默认备注为：$name\n如需修改请回复“姓名+您的姓名”修改您在小组的备注\n例：姓名张三";
	}
}
?>