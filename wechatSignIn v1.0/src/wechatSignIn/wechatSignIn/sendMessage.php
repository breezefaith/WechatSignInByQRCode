<?php
	//header("Content-type: text/html; charset=utf-8");
	//include_once("https_request.php");
	//include_once("getAccessToken.php");
	include_once("function.php");

	/*$appid="wx48f95875b93b06fa";
	$appsecret="29890defab35edd0bed093af30dcc87e";
	$appid_test="wxf7e1cc2a8ecc076b";
	$appsecret_test="b22e67ac6eb47249597f84b13fa8dcea";
	$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid_test&secret=$appsecret_test";*/
	$access_token=getAccessToken();
	$openid='obkI1wf42dssxE0yEZEFVmR7hLn8';
	//sendText($openid,$access_token,time());
	
	function sendText($openid,$access_token,$content){
		$text='{
			"touser":"'.$openid.'",
			"msgtype":"text",
			"text": { 
				"content":"'.$content.'"
			}
		}';
		//echo $text."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url, $text);
		//var_dump($result);
	}
	function sendImage($openid,$access_token,$MediaId){
		$image = '{
			"touser":"'.$openid.'",
			"msgtype":"image",
			"image":
			{
			  "media_id":"'.$MediaId.'"
			}
		}';
		//echo $image."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url,$image);
		var_dump($result);
	}
	function sendVoice($openid,$access_token,$MediaId){
		$voice='{
			"touser":"'.$openid.'",
			"msgtype":"voice",
			"voice":
			{
				"media_id":"'.$MediaId.'"
			}
		}';
		//echo $voice."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url,$voice);
		//var_dump($result);
	}
	function sendVideo($openid,$access_token,$info){
		$video='{
			"touser":"'.$openid.'",
			"msgtype":"video",
			"video":
			{
			  "media_id":"'.$info['MediaId'].'",
			  "thumb_media_id":"'.$info['ThumbMediaId'].'",
			  "title":"'.$info['Title'].'",
			  "description":"'.$info['Description'].'"
			}
		}';
		//echo $video."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url, $video);
		//var_dump($result);
	}
	function sendMusic($openid,$access_token,$info){
		$music='{
			"touser":"'.$openid.'",
			"msgtype":"music",
			"music":
			{
			  "title":"'.$info['Title'].'",
			  "description":"'.$info['Description'].'",
			  "musicurl":"'.$info['MusicUrl'].'",
			  "hqmusicurl":"'.$info['MusicHQUrl'].'",
			  "thumb_media_id":"'.$info['ThumbMediaId'].'" 
			}
		}';
		//echo $music."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url, $music);
		//var_dump($result);
	}
	function sendNews($openid,$access_token,$info){
		$news='{
			"touser":"'.$openid.'",
			"msgtype":"news",
			"news":{
				"articles": [
				 {
					 "title":"'.$info['Title'].'",
					 "description":"'.$info['Description'].'",
					 "url":"'.$info['Url'].'",
					 "picurl":"'.$info['PicUrl'].'"
				 }
				]
			}
		}';
		//echo $news."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url, $news);
		//var_dump($result);
	}
	function sendCard($openid,$access_token,$info){
		$card='{
		  "touser":"'.$openid.'", 
		  "msgtype":"wxcard",
		  "wxcard":{              
				   "card_id":"'.$info['CardId'].'",
				   "card_ext":"'.$info['CardExt'].'"           
					}
		}';
		//echo $card."<br/>";
		$url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
		$result = https_request($url, $card);
		//var_dump($result);
	}
?>