<?php
header("Content-Type: text/html; charset=UTF-8");
include("connect.php");
$GroupName=$_GET['group'];
$SignInHeadman=$_GET['headman'];
$member=array();
$persons=mysqli_query($con,"select Name,SignInHeadman from ".$GroupName);	
	if ($persons) {
		$persons_rows=mysqli_num_rows($persons);
		for($index_1=0;$index_1<$persons_rows;$index_1++)
		{	
			$person=mysqli_fetch_array($persons);
			if ($person) {
				$signInHeadman=$person['SignInHeadman'];
				$name=$person['Name'];
				$SignInTimes=0;
				for ($i=0; $i < strlen($signInHeadman); $i++) { 
					if ($signInHeadman[$i]==$SignInHeadman) {
						$SignInTimes++;
					}
				}
				$member[$name]=$SignInTimes;	
			} 
   		}
   	}
   	echo json_encode($member,JSON_UNESCAPED_UNICODE);
?>