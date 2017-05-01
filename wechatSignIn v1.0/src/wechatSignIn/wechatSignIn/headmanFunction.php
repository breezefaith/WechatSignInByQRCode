<?php
//include_once("mysqlFunction.php");
//$openid="obkI1wf42dssxE0yEZEFVmR7hLn8";
//print_r(getGroupInfo($openid));
function crossScreenQRCode($openid,$groupName){//创建跨屏二维码
	$url=createSignInQRUrl($openid,$groupName);//获取组装好的url参数
	if($url=="sorry，您不是该小组的管理员"){
		return $url;
	}
	$link=sql_connect();//获取pageId
	$sql="select PageId from Headman where Openid='$openid' limit 1";
	if(!$result=_select_data($link,$sql)){
		return mysqli_error($link);
	}
	$row=mysqli_fetch_assoc($result);
	if(empty($row)){
		return "未设置pageId";
	}
	$pageId=$row['PageId'];
	if($pageId==0||$pageId==NULL){
		return "请您先扫描二维码登录";
	}
	$urlInfo=parse_url($url);
	//return print_r($urlInfo,1);
	$parameter=$urlInfo['query'];
	
	/*$flag=$_GET['flag'];
	$groupName=$_GET['group'];
	$openid=$_GET['openid'];
	$createStamp=$_GET['createstamp'];*/
	
	//$content=base64_encode($urlInfo['query']);
	$parameter=substr_replace($parameter,'3',5,1);//将flag值改为3
	//return $parameter;
	$content=base64_encode($parameter);
	$socket="http://websocket.dream.ren:1996/?type=publish&content=$content&to=$pageId";//向socket服务器发送消息
	$result=https_request($socket);
	if($reslut!="ok"){
		return "请您确保此前已进行扫码登录";
	}
	return "跨屏登录成功";
}
function setPageId($openid,$pageId){
	if(!isHeadman($openid)){
		return "您不是任何小组的管理员";
	}
	$link=sql_connect();
	$sql="update Headman set PageId='$pageId' where Openid='$openid'";
	if(!_update_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return "设置pageId失败\n".$error;
	}
	return "请输入跨屏+小组名创建跨屏签到二维码，例： 跨屏九班"."\n";
}
function getGroupInfo($openid){//获取该用户所在小组的小组名、小组URL、小组id、小组二维码media_id
	$link=sql_connect();
	$sql="select GroupId from Headman where Openid='{$openid}'";
	$result=_select_data($link,$sql);
	$i=0;
	while($row=mysqli_fetch_assoc($result)){//将查找到的信息存于Group数组
		$Group[$i]['Groups_Id']=$row['GroupId'];
		$i++;
	}
	for($i=0;$i<count($Group);$i++){
		$Groups_Id=$Group[$i]["Groups_Id"];
		$sql="select GroupName,Url from Groups where Id='{$Groups_Id}'";
		$result=_select_data($link,$sql);
		$row=mysqli_fetch_assoc($result);
		$Group[$i]['Groups_GroupName']=$row['GroupName'];//将查找到的信息存于Group数组
		$Group[$i]['Groups_Url']=$row['Url'];
	}
	$link=mysqli_close();
	return $Group;
}
function getGroupQRCodeMediaId($groupName){//获取该用户所在小组的小组名、小组URL、小组id、小组二维码media_id
	$link=sql_connect();
	$sql="select Url from Groups where GroupName='{$groupName}' limit 1";
	if(!$result=_select_data($link,$sql))
		return 0;
	$row=mysqli_fetch_assoc($result);
	$mediaId=getQrId($row['Url']);
	$link=mysqli_close();
	return $mediaId;
}
function createGroup($openid,$groupName){
	if(is_numeric($groupName)){
		return "小组名不能为纯数字";
	}
    $link=sql_connect();//连接数据库
	$sql="select table_name from information_schema.COLUMNS where table_schema='test' and column_name='Openid'";//查询含有Openid列的所有表
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$sql="select Name from {$row['table_name']} where Openid='{$openid}' limit 1";//查询指定openid已使用的最新姓名
		if(!$resultName=_select_data($link,$sql)){
			$error=mysqli_error($link);
			mysqli_close($link);
			return "创建 $groupName 失败\n".$error;
		}
		$rowName=mysqli_fetch_assoc($resultName);
		$name=$rowName['Name'];
		if($name!="")//$name不为空及查询到最新姓名
			break;
	}
	$Headman_Id=getId($link,"Headman")+1;//获取表Headman最后一行行号
	$Groups_Id=getId($link,"Groups")+1;//获取表Groups最后一行行号
	$Groups_Url=getLimitUrl("1".$Groups_Id);//生成小组二维码的Url,1为加入小组url的标记
	//return $Groups_Url;
	$sql="insert into Groups(Id,GroupName,Time,LatestTime,Times,Headman,Number,Url)values('{$Groups_Id}','{$groupName}',
		'','0','0','','','{$Groups_Url}')";
    if(!_insert_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
        //return "小组$groupName已存在\n".$error;
		return "小组 $groupName 已存在\n";
	}
    $sql="insert into Headman(Id,Openid,Name,GroupId,Status)values('{$Headman_Id}','{$openid}',
        '{$name}','{$Groups_Id}','0')";//向Headman中插入数据
	if(!_insert_data($link,$sql)){
		$error=mysqli_error($link);
		$sql="delete from Groups order by Id desc limit 1";//出错则把已成功插入的数据删除
		_delete_data($link,$sql);
		mysqli_close($link);
        //return "$groupName创建失败\n".$error;
		return "$groupName 创建失败\n";
	}
    $sql="create table $groupName(Id integer not null,Openid varchar(255) null,Name varchar(255) null,Status integer null,
        SignInTime text null,SignInLatestTime varchar(255) null,SignInHeadman text null,SignInInfo text null,SignInTimes integer not null,Es text null,primary key(Id),unique key(Openid))";
    if(!_create_table($link,$sql)){
		$error=mysqli_error($link);
		$sql="delete from Groups order by Id desc limit 1";//出错则把已成功插入的数据删除
		_delete_data($link,$sql);
		$sql="delete from Headman order by Id desc limit 1";
		_delete_data($link,$sql);
		mysqli_close($link);
        //return "小组 $groupName 已存在\n".$error;
		return "小组 $groupName 已存在\n";
	}
    $sql="insert into $groupName(Id,Openid,Name,Status,SignInTime,SignInHeadman,SignInInfo,SignInTimes,Es)values('1','{$openid}',
        '{$name}','0','','','','0','')";
    if(!_insert_data($link,$sql)){
        $error=mysqli_error($link);
		$sql="delete from Groups order by Id desc limit 1";//出错则把已成功插入的数据删除
		_delete_data($link,$sql);
		$sql="delete from Headman order by Id desc limit 1";
		_delete_data($link,$sql);
		$sql="drop table {$groupName}";
		_drop_table($link,$sql);
		mysqli_close($link);
		//return "Inserting data into $groupName failed\n".$error;
		return "$groupName 创建失败\n";
	}
	mysqli_close($link);
	if($name==""){
		return "小组 $groupName 创建成功\n您的默认备注为空\n请回复“姓名+您的姓名”修改您在小组的备注\n例：姓名张三";
	}else{
		return "小组 $groupName 创建成功\n您的默认备注为：$name\n如需修改请回复“姓名+您的姓名”修改您在小组的备注\n例：姓名张三";
	}
}
function createSignInQRUrlByGroupId($openid,$groupId){//通过小组Id生成签到二维码
	$link=sql_connect();
	$row=mysqli_fetch_assoc(_select_data($link,"select GroupName from Groups where Id='{$groupId}' limit 1"));
	//小组Id唯一则小组名也唯一
	$result=createSignInQRUrl($openid,$row['GroupName']);//调用根据小组名获取二维码函数
	return $result;
}
function createSignInQRUrl($openid,$groupName){//根据小组名获取签到二维码
	$link=sql_connect();
	$sql="select Name from {$groupName} where Openid='{$openid}' and  Status='0'";//小组名即表名的设定让查询很方便
	$result=_select_data($link,$sql);//附带查询Status是为了确保用户是本小组的管理员
	if(!$row=mysqli_fetch_assoc($result)){
		mysqli_close($link);
		return "sorry，您不是该小组的管理员";
	}
	$row=mysqli_fetch_assoc(_select_data($link,"select Times from Groups where GroupName='{$groupName}' limit 1"));//获取生成二维码次数
	$times=$row['Times']+1;
	$createStamp=time();//此为生成二维码的时间戳
	$sql="select Id from {$groupName} where Openid='{$openid}'";//获取管理员在小组内的编号Id
	$row=mysqli_fetch_assoc(_select_data($link,$sql));
	$inGroupId=$row['Id'];
	$row=mysqli_fetch_assoc(_select_data($link,"select LatestNumber from Groups where GroupName='{$groupName}' limit 1"));//获取上一次的签到人数
	if(!isset($row['LatestNumber'])){//如果为空即此为第一次生成二维码
		$sql="update Groups set Time=concat(Time,',','{$createStamp}'),Times='{$times}',LatestTime='{$createStamp}',Number='',
			LatestNumber='0',Headman=concat(Headman,',','{$inGroupId}') where GroupName='{$groupName}'";
	}else if($row['LatestNumber']==0){//如果上次签到人数为零，则作废上一次记录
		$sql="update Groups set Time=left(Time,length(Time)-11),Times=Times-1,Headman=left(Headman,length(Headman)-length(substring_index(Headman,',','-1'))),Number=left(Number,length(Number)-length(substring_index(Number,',','-1'))) where GroupName='$groupName'";
		if(!_update_data($link,$sql)){
			$error=mysqli_error($link);
			mysqli_close($link);
			return $error;
		}
		$sql="update Groups set Time=concat(Time,',','{$createStamp}'),Times=Times+1,LatestTime='{$createStamp}',Number=concat(Number,',','{$row['LatestNumber']}'),
			LatestNumber='0',Headman=concat(Headman,',','{$inGroupId}') where GroupName='{$groupName}'";
	}else{
		$sql="update Groups set Time=concat(Time,',','{$createStamp}'),Times=Times+1,LatestTime='{$createStamp}',Number=concat(Number,',','{$row['LatestNumber']}'),
			LatestNumber='0',Headman=concat(Headman,',','{$inGroupId}') where GroupName='{$groupName}'";
	}
	if(!_update_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$array=array("$createStamp"=>"");
	$json=json_encode($array);
	$sql="update {$groupName} set SignInLatestTime='{$json}'";
	if(!_update_data($link,$sql)){
		$error=mysqli_error($link);
		mysqli_close($link);
		return $error;
	}
	$array=array("$createStamp"=>"$createStamp");
	$json=json_encode($array);
	/*$sql="update {$groupName} set SignInTime=concat(SignInTime,',','{$json}'),SignInLatestTime='{$json}',SignInTimes=SignInTimes+1,
		SignInHeadman='{$openid}' where Openid='{$openid}' limit 1";//生成二维码即给管理员自己签到
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
	}*/
	mysqli_close($link);
	
	$qr_parameter="flag="."2"."&createstamp=".$createStamp."&group=".urlencode($groupName)."&openid=".$openid;//获取组装好的url参数qr_parameter
	//$qr_parameter=urlencode($qr_parameter);
	//return $qr_parameter;
	$tempUrl="http://zzc.dream.ren/wechat/signin/qrcode/index.php?".$qr_parameter;
	//$tempUrl=urlencode($tempUrl);
	return $tempUrl;
}
function isHeadman($openid){//判断是不是某小组的管理员
	$link=sql_connect();//连接数据库
	$sql="select GroupId from Headman where Openid='{$openid}'";
	$result=_select_data($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$groupId[]=$row['GroupId'];
	}
	if(!$groupId){//为空则不是任何小组的管理员
		mysqli_close($link);
		return 0;
	}
	mysqli_close($link);
	return $groupId;//不为空则返回用户担任管理员的小组Id构成的数组
}
?>