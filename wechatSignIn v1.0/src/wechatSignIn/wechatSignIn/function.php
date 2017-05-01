<?php
	//require_once '.\https_request.php';
	//functionTest();
	//echo getNewAccessToken()."<br/>";
	//功能可用性测试
	function functionTest()
	{
		echo "缓存token：<br/>";
		echo getAccessToken();
		echo "<br/>新token：<br/>";
		echo getNewAccessToken();
		echo "<br/>生成并获取二维码ID：<br/>";
		echo getQrId('1111');
		echo "<br/>可传递参数的二维码内容：<br/>";
		echo getLimitUrl('1');
		//echo "<br/>发送消息XML检查：<br/>";
	}
	//默认调用AccessToken全局接口，增加一小时缓存机制;本函数没有验证Token真实有效性，非本函数获取Token会使本函数Token失效！
	function getAccessToken()
	{
		$array=file('AccessToken.txt');
		if((time()-$array[0])<3600&&$array[1])
		{
			return $array[1];
		}	
		else
		{
			if(!($fp = fopen(dirname(__FILE__).'/AccessToken.txt', 'w')))
				return "open file failed.<br/>";
			$newaccess=getNewAccessToken();
			fwrite($fp, time()."\r\n");//unix系统使用\n；windows系统下\r\n 
			fwrite($fp, $newaccess);
			fclose($fp);
			return $newaccess;
		}
	}
	//该功能请勿随意调用(会导致上面getAccessToken得到的Token失效！哎，算了，还是兼容上面的缓存机制吧)
	function getNewAccessToken()
	{
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxf7e1cc2a8ecc076b&secret=b22e67ac6eb47249597f84b13fa8dcea";
		//Change appid and secret to yours.
		$jsoninfo=https_request($url);
		$jsoninfo=json_decode($jsoninfo,true);
		if(!($fp = fopen(dirname(__FILE__).'/AccessToken.txt', 'w')))
				return "open file failed.<br/>";
		fwrite($fp, time()."\r\n");//unix系统使用\n；windows系统下\r\n 
		fwrite($fp, $jsoninfo["access_token"]);
		fclose($fp);
		return $jsoninfo["access_token"];
	}
	//发送图片消息，$imageid是微信素材内的MediaId
	function zly_sendImage($from,$to,$imageid)
	{
		if($imageid&&$from&&$to)
		{
			$imageTemplate = "<xml>
        		<ToUserName><![CDATA[%s]]></ToUserName>
        		<FromUserName><![CDATA[%s]]></FromUserName>
        		<CreateTime>%s</CreateTime>
        		<MsgType><![CDATA[image]]></MsgType>
        		<Image>
        		<MediaId><![CDATA[%s]]></MediaId>
        		</Image>
        		</xml>";
    		$time = time();
    		$resultStr = sprintf($imageTemplate, $to, $from, $time, $imageid);
    		echo $resultStr;
		}
		
	}
	//生成二维码并上传到微信素材库获取媒体ID
	function getQrId($qr)
	{
		if($qr)
		{
			$access_token=getAccessToken();
			include_once 'phpqrcode.php'; 
			$filename=md5($qr).'.png';
			QRcode::png($qr, 'images/qr/'.$filename, 'L', 15, 1);//生成并保存二维码
			$type = "image";//image video thumb voice 
			$filepath = dirname(__FILE__).'/images/qr/'.$filename;
    		$cfile = curl_file_create($filepath);   //use the CURLFile Class 替换@的使用方法。
    		$filedata = array('media'=>$cfile);
    		$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type";
			$result = https_request($url, $filedata);
			$result=json_decode($result,true);
			return $result["media_id"];
			//var_dump($result);
		}
		
	}
	//获取可传递参数的二维码内容（有效期已是最大30天）
	function getTempUrl($scene_id)//$scene_id为不大于32位的非0整型
	{
		if(is_numeric($scene_id))
		{
			$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".getAccessToken();
			$postjson='{"expire_seconds": 2592000, 
				"action_name": "QR_SCENE", 
				"action_info": {
					"scene": {
						"scene_id": '.$scene_id.'
						}}}';
			$info=https_request($url,$postjson);
			$info=json_decode($info,true);
			return $info["url"];
		}
		
	}
	//获取可传递参数的永久二维码内容(额度100000个)
	function getLimitUrl($scene_id)//$scene_id最大值为100000（$scene_id参数只支持1--100000）
	{
		if($scene_id>0&&$scene_id<100000)
		{
			$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".getAccessToken();
			$postjson='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
			$info=https_request($url,$postjson);
			$info=json_decode($info,true);
			return $info["url"];
		}
		
	}
//其他功能所需的网络请求支持
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