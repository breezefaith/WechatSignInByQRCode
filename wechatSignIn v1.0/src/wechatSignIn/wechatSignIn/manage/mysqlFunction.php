<?php
function https_request($url,$data=null){
	$curl=curl_init();
	curl_setopt($curl, CURLOPT_URL,$url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	if(!empty($data))
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output=curl_exec($curl);
	curl_close($curl);
	return $output;
}
/*判断是否是管理员*/
function isHeadman($link,$openid){//判断是不是某小组的管理员
	$sql="select * from Headman where Openid='{$openid}'";
	$result=mysqli_query($link,$sql);
	$name=0;//赋初值
	while($row=mysqli_fetch_assoc($result)){
		$groupId[]=$row['GroupId'];//获取该管理员所管理的小组的Id
		$name=$row['Name'];//获取管理员用户名
	}
	$groupId[]=$name;//把用户名放到数组最后一位或者第一位返回,如果不是管理员，则$name为数组第一个元素
	if(!$groupId[0]){//$groupId[0]==$name=0,则不是任何小组的管理员
		return 0;
	}
	return $groupId;//不为空则返回用户担任管理员的小组Id构成的数组
}
//确定该管理员管理哪几个小组
function whichGroup($link,$v)
{
	$sql="select GroupName from Groups where Id='{$v}'";
	$result=mysqli_query($link,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$groupname=$row['GroupName'];
	}
		//echo $groupname."<br />";
		return $groupname;//返回小组名称
}
//显示小组成员,极其属性（管理员还是普通成员）
function showCrew($link,$GroupName)
{
  $sql="select * from {$GroupName}";
  $result=mysqli_query($link,$sql);
  while($row=mysqli_fetch_assoc($result)){
    $name[]=$row['Openid'];//获取Openid
		$name[]=$row['Name'];//获取姓名
		if($row['Status']==1)//记录是管理员还是普通成员
		$name[]="--Crew";
	    else
	    $name[]="--Admin";
	}
	return $name;
}
//根据Openid获取用户姓名
function get_Crewname($link,$group_name,$crew_id){
	$sql="select Name from {$group_name} where Openid='{$crew_id}'";
	$result=mysqli_query($link,$sql);
	if($result)
	{
		$row=mysqli_fetch_assoc($result);
        $Crewname=$row['Name'];//获取姓名
        return $Crewname;
	}
	else
		return 0;
}
//获取小组Id
function get_Groupid($link,$group_name){
	$sql="select Id from Groups where GroupName='{$group_name}'";
	$result=mysqli_query($link,$sql);
	if($result)
	{
		$row=mysqli_fetch_assoc($result);
        $GroupId=$row['Id'];//获取小组Id
        return $GroupId;
	}
	else
	  return 0;
}
//获取headman表或member表中最后一行id加一
function get_Thelast_Id($link,$groupname){
	$sql="select * from {$groupname}";
	$result=mysqli_query($link,$sql);
	if($result)
	{
       $Id=0;
        while($row=mysqli_fetch_assoc($result)){
        	$Id=$row['Id'];
        }
        $Id+=1;
        return $Id;
	}
	else
		return 0;
}
//删除headman表中一行
function del_Oneheadman($link,$GroupId,$crew_id)
{
	$sql="delete from Headman where Openid='{$crew_id}' And GroupId='{$GroupId}'";
	if(mysqli_query($link,$sql))
		return 1;
	else 
		return 0;
}
//删除member表中一行
function del_Onecrew($link,$crew_id,$group_name)
{
  $sql="delete from Member where Openid='{$crew_id}' And GroupName='{$group_name}'";
  if(mysqli_query($link,$sql))
    return 1;
  else 
    return 0;
}
//获取用户的Status
/*function get_Status($link,$crew_name,$group_name)
{
	$sql="select * from {$group_name} where Name='{$crew_name}'";
    $result=mysqli_query($link,$sql);
    if($result)
    {
    	$row=mysqli_fetch_assoc($result);
    	$Status=$row['Status'];
        $Status+=1;
        return $Status;
    }
    else
    	return 0;
}*/
//判断用户是不是本组的管理员
function isHeadman_this($link,$crew_id,$group_name)
{
   $sql="select * from {$group_name} where Openid='{$crew_id}'";
   //return $sql;
   $result=mysqli_query($link,$sql);
   if($result)
   {
     $row=mysqli_fetch_assoc($result);
     $Status=$row['Status'];
     return $Status;//返回用户标志
   }
   else  
     return 3; 
}
//更新member表中成员备注
function change_Onecrew($link,$crew_id,$new_name,$group_name)
{
  $sql="update Member set Name='{$new_name}' where Openid='{$crew_id}' And GroupName='{$group_name}'";
  if(mysqli_query($link,$sql))
    return 1;
  else
    return 0;
}
//更新headman表中管理员备注
function chang_Oneheadman($link,$crew_id,$new_name,$group_id)
{
  $sql="update Headman set Name='{$new_name}' where Openid='{$crew_id}' And GroupId='{$group_id}'";
  if(mysqli_query($link,$sql))
    return 1;
  else
    return 0;
}
//根据页面操作指令，更新数据库
function action_ForCrew($link,$group_name,$crew_id,$action,$new_name)
{
   $openid=$_COOKIE['openid'];
   if($action=="Del")//删除成员
   {
	 if($openid==$crew_id)
		  return "sorry,不允许对自己进行操作!";
	 else
     {
		$sql="delete from {$group_name} where Openid='{$crew_id}'";
		if(mysqli_query($link,$sql))//先更新该组组员信息表
   	 	{
           if(isHeadman_this($link,$crew_id,$group_name))//如果是普通成员，再更新成员总表，即member表
           	{
               if(del_Onecrew($link,$crew_id,$group_name))
                return "删除成功!";
               else
                return "删除失败!";
            }
           else//如果用户是管理员，则同时更新管理员表,即Headman
           {
           	  
           	    if(!$GroupId=get_Groupid($link,$group_name))
            	     return "获取本小组Id失败!";
           	  	if(del_Oneheadman($link,$GroupId,$crew_id))
           	     return "删除成功!";
           	    else
           	      return "删除失败!"; 
           }
   	 	}
		else
   	 	return "删除失败!";
	 }
   }
   if($action=="toAdmin")//设为管理员
   {
	   
      if(isHeadman_this($link,$crew_id,$group_name))//如果之前在本组普通成员
      {

        //删除member中一行，更新用户总表
        if(!del_Onecrew($link,$crew_id,$group_name))
          return "删除失败!";
   	    $sql1="update {$group_name} set Status=0 where Openid='{$crew_id}'";//更新小组信息
   	    if(mysqli_query($link,$sql1))
   	  	{
           	if(!$Crewname=get_Crewname($link,$group_name,$crew_id))
           		 return "获取用户姓名失败!";
            if(!$GroupId=get_Groupid($link,$group_name))
            	return "获取本小组Id失败!";
            $groupname="Headman";
            if(!$Id=get_Thelast_Id($link,$groupname))
            	return "获取headman表中Id失败！";
            //在headman表中插入一行,更新管理员信息
           	$sql2="insert into Headman(Id,Openid,Name,GroupId,Status)values(".$Id.",'".$crew_id."','".$Crewname."','".$GroupId."',0)";
           if(mysqli_query($link,$sql2))
              return "设置成功！";
            else
            	return "设置失败!";
   	 	  }
   	   else 
   	 	  return "设置失败!";
      }
      else
        return "该成员已经是管理员!";
   }
   if($action=="toCrew")//设为普通成员
   {
	  if($openid==$crew_id)
		  return "sorry,不允许对自己进行操作!";
	  else
	  {
		if(!$is_manager=isHeadman_this($link,$crew_id,$group_name))//如果之前在本组是管理员
		{
			$sql1="update {$group_name} set Status=1 where Openid='{$crew_id}'";//更新小组信息
			if(mysqli_query($link,$sql1))
			{
				//删除Headman中一行，更新管理员表
				if(!$GroupId=get_Groupid($link,$group_name))
					return "获取本小组Id失败!";
				if(!del_Oneheadman($link,$GroupId,$crew_id))
					return "设置失败!1"; 
				//在member表中插入一行,更新普通成员信息
				if(!$Crewname=get_Crewname($link,$group_name,$crew_id))
					return "获取用户姓名失败!";
				$groupname="Member";
				if(!$Id=get_Thelast_Id($link,$groupname))
					return "获取member表中Id失败!";
				$sql2="insert into Member(Id,Openid,Name,GroupName)values(".$Id.",'".$crew_id."','".$Crewname."','".$group_name."')";
				if(mysqli_query($link,$sql2))
					return "设置成功!";
				else
					return "设置失败!2";
			}
			else 
				return "设置失败!3";
		}
		else
			return "该成员已经是普通成员!";
	  }
   }
   if($action=="changeName")//修改备注
   {
   	 $sql="update {$group_name} set Name='{$new_name}' where Openid='{$crew_id}'";
   	 if(mysqli_query($link,$sql))
   	 	 {
         if(isHeadman_this($link,$crew_id,$group_name))//如果是普通成员，再更新成员总表，即member表
         {
            if(change_Onecrew($link,$crew_id,$new_name,$group_name))
              return "修改成功!";
            else
              return "修改失败!";
         }
         else//如果用户是管理员，则同时更新管理员表,即Headman
         {
            if(!$GroupId=get_Groupid($link,$group_name))//获取小组Id
              return "获取本小组Id失败!";
            if(chang_Oneheadman($link,$crew_id,$new_name,$GroupId))
              return "修改成功!";
            else
              return "修改失败!";
         }
       }
   	 else 
   	 	return "修改失败!";
   }
   return "Sorry,操作不可用!";
}
//数据库连接函数
function sql_connect()
{
    $mysql_host = "127.0.0.1";
    $mysql_host_s = "127.0.0.1";
    $mysql_port = "3306";
    $mysql_user = "root";
    $mysql_password = "zhangzc1996";
    $mysql_database = "test";
    $link =new mysqli($mysql_host_s,$mysql_user,$mysql_password,$mysql_database);
	if(mysqli_connect_errno()){
		echo $err=mysqli_connect_error();
		return $err;
	}
    mysqli_query($link,"SET NAMES 'UTF8'");
    return $link;
}
