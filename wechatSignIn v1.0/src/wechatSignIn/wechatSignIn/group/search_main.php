<?php
	header("Content-Type: text/html; charset=UTF-8");
	include("connect.php");
	if(isset($_COOKIE['openid'])){
		$openid=$_COOKIE['openid'];
	}else{
		echo "NO OPENID";
		exit();
	}
	//$openid = "obkI1wf42dssxE0yEZEFVmR7hLn8";
	$GroupName="";	
	$result=mysqli_query($con,"select GroupId from Headman where Openid='$openid'");
	$groups=array();
	if ($result) {
		$num_rows=mysqli_num_rows($result);
		echo "<strong>组名：</strong><br/><select id='group' name='groupname' style='height:25;width:306'>" ;
		for ($i=0; $i <$num_rows ; $i++) {
			$rows=mysqli_fetch_array($result);
			$Group=mysqli_query($con,"select GroupName from Groups where id='".$rows['GroupId']."'");
			if ($Group) {
				$group=mysqli_fetch_array($Group);
				echo "<option value=".$group['GroupName'].">".$group['GroupName']."</option>"; 
				if ($i==0) {
					$GroupName=$GroupName.$group['GroupName'];
				}
			}
		
		}
	}
	echo "</select><br/><br/>";
	$result=mysqli_query($con,"select Time from Groups where GroupName='".$GroupName."'");
	$LatestTime=0000000000;
	$OldestTime=9999999999;
	$_signInTime='';
	if ($result) {
		while($rows=mysqli_fetch_array($result))
		{	
			$signInTime=$rows['Time'];
			for ($index=1; $index < strlen($signInTime); $index++) 
			{ 
				if ($signInTime[$index]!=',')
				{
					$_signInTime=$_signInTime.$signInTime[$index];
				}
				if ($signInTime[$index]==','||$index==strlen($signInTime)-1) 
				{   
					if ($LatestTime<=$_signInTime) {
						$LatestTime=$_signInTime;
					}
					if ($OldestTime>=$_signInTime) {
						$OldestTime=$_signInTime;
					}
					$_signInTime='';
				}                  	
					  
			}
		}
	}
	echo "<strong>时间：</strong><br/><select class='time' id='years' name='time_y_b' style='height:25'>" ;
	for($index=date("Y",$OldestTime);$index<=date("Y",$LatestTime);$index++)
	{	
		echo "<option value=".$index.">".$index."</option>"; 
	}
	echo "</select><font size='2px'>年</font>";
	echo "<select id='months' class='months' name='time_m_b' style='height:25'>" ;
	if (date("Y",$LatestTime)==date("Y",$OldestTime)) {
		for($index=date("m",$OldestTime);$index<=date("m",$LatestTime);$index++)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}

	}
	if (date("Y",$LatestTime)!=date("Y",$OldestTime)) {
		for($index=date("m",$OldestTime);$index<=12;$index++)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}		
	}
	echo "</select><font size='2px'>月</font>";
	echo "<select id='days' class='time' name='time_d_b' style='height:25'>" ;
	if (date("m",$LatestTime)==date("m",$OldestTime)&&date("Y",$LatestTime)==date("Y",$OldestTime)) {
		for($index=date("d",$OldestTime);$index<=date("d",$LatestTime);$index++)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}

	}
	if (date("m",$LatestTime)!=date("m",$OldestTime)||date("Y",$LatestTime)!=date("Y",$OldestTime)) {
		for($index=date("d",$OldestTime);$index<=31;$index++)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}		
	}
	echo "</select><font size='2px'>日</font>";
	echo "<h5 style='margin:10px'>到</h5><select id='_years' class='time' name='time_y_a' style='height:25'>" ;
	for($index=date("Y",$LatestTime);$index>=date("Y",$OldestTime);$index--)
	{	
		echo "<option value=".$index.">".$index."</option>"; 
	}
	echo "</select><font size='2px'>年</font>";
	echo "<select id='_months' class='months' name='time_m_a' style='height:25'>" ;
	if (date("Y",$LatestTime)==date("Y",$OldestTime)) {
		for($index=date("m",$LatestTime);$index>=date("m",$OldestTime);$index--)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}
	}
	if (date("Y",$LatestTime)!=date("Y",$OldestTime)) {
		for($index=date("m",$LatestTime);$index>=1;$index--)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}		
	}
	echo "</select><font size='2px'>月</font>";
	echo "<select id='_days' class='time' name='time_d_a' style='height:25'>" ;
	if (date("m",$LatestTime)==date("m",$OldestTime)&&date("Y",$LatestTime)==date("Y",$OldestTime)) {
		for($index=date("d",$LatestTime);$index>=date("d",$OldestTime);$index--)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}

	}
	if (date("m",$LatestTime)!=date("m",$OldestTime)||date("Y",$LatestTime)!=date("Y",$OldestTime)) {
		for($index=date("d",$LatestTime);$index>=1;$index--)
		{	
			echo "<option value=".$index.">".$index."</option>"; 
		}		
	}
	echo "</select><font size='2px'>日</font><br/><br/>";
	echo "<strong>管理员：</strong><br/><select id='members' name='headman' style='height:25;width:307'>" ;
	$persons=mysqli_query($con,"select Id,Name from ".$GroupName." where Status='0'");
	if ($persons){
		$rows=mysqli_num_rows($persons);
		for ($i=0; $i <$rows ; $i++) { 	
			$person=mysqli_fetch_array($persons);
			$name=$person['Name'];
			$id=$person['Id'];
			echo "<option value=".$id.">".$name."</option>";
		}	 
	}
	echo "</select><br/>";
	echo "<center><input type='button' id='Submit' style='height:35;width:107;background-color:#02FCF9' value='获取' /></center>";
?> 
