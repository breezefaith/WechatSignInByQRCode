<?php
$con=mysqli_connect("localhost","root","zhangzc1996","test");
if(!$con)
{
	print("Error-Could not connect to MySQL");
	exit();			
}
mysqli_query($con,"set names 'utf8'");
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
?>