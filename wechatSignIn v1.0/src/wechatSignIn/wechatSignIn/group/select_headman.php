<?php
include("connect.php");
$GroupName=$_POST['name'];
$group_mem=array();
$persons=mysqli_query($con,"select Id,Name from ".$GroupName." where Status='0'");	
	if ($persons) {
		$persons_rows=mysqli_num_rows($persons);
			for($index_1=0;$index_1<$persons_rows;$index_1++)
			{	
				$person=mysqli_fetch_array($persons);
					if ($person) {
						$name=$person['Name'];
						$id=$person['Id'];
						$group_mem[$name]=$id;
					} 
    		}
   	}
   	echo json_encode($group_mem,JSON_UNESCAPED_UNICODE);
?>