<?php
	include_once("mysqlFunction.php");
	$con=sql_connect();
	$openid = "obkI1wf42dssxE0yEZEFVmR7hLn8";
	$GroupName="";
	//echo "<script>alert('"."success"."');</script>";
	$result=mysqli_query($con,"select GroupId from Headman where Openid='$openid'");
	$groups=array();
	if($result){
		$num_rows=mysqli_num_rows($result);
		echo "组名：<br/><select id='group' name='groupname' style='height:25;width:306'>";
		for ($i=0; $i<$num_rows; $i++) {
			$rows=mysqli_fetch_assoc($result);
			$sql="select GroupName from Groups where Id='".$rows['GroupId']."' limit 1";
			$Group=mysqli_query($con,$sql);
			if(mysqli_errno($con)!=0){
				echo mysqli_error($con);
				exit();
			}else{
				//echo "<script>alert('"."success"."');</script>";
			}
			if($Group){
				$group=mysqli_fetch_assoc($Group);
				echo "<option value=".$group['GroupName'].">".$group['GroupName']."</option>"; 
				if ($i==0) {
					$GroupName=$GroupName.$group['GroupName'];
				}
			}
		}
	}
?>