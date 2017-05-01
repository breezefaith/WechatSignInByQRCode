<?php
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
//echo $result["openid"];
function https_request($url,$data=null)
{
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