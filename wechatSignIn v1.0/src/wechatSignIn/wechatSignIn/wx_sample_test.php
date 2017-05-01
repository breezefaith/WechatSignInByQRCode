<?php
include_once("function.php");
include_once("mysqlFunction.php");
include_once("sendMessage.php");
include_once("headmanFunction.php");
include_once("allUserFunction.php");

define("TOKEN", "lengyeyefeng");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}
class wechatCallbackapiTest
{
    public function valid(){
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function responseMsg(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fp=fopen("post.txt","a+") or die("Unable to open file!");
			fwrite($fp,$postStr."\n\n");
			fclose($fp);
            $RX_TYPE=trim($postObj->MsgType);
            switch($RX_TYPE)
            {
            case "text":
                $result=$this->receiveText($postObj);
                break;
            case "image":
                $result=$this->receiveImage($postObj);
                break;
            case "voice":
                $result=$this->receiveText($postObj);
                break;
            case "video":
                $result=$this->receiveVideo($postObj);
                break;
            case "location":
                $result=$this->receiveLocation($postObj);
                break;
            case "voice":
                $result=$this->receiveLocation($postObj);
                break;
            case "link":
                $result=$this->receiveLink($postObj);
                break;
            case "event":
                $result=$this->receiveEvent($postObj);
                break;
            default:
                $result="Unknown type:";    
                break;
            }
            echo $result;
        }else{
            echo "";
            exit;
        }
    }
    private function receiveText($object)
    {
        if(isset($object->Recognition)){
            $keyword=trim($object->Recognition);
            $mediaId=trim($object->MediaId);
        }else
        	$keyword=trim($object->Content);
        if($keyword=="文本"){
            $content="这是一个文本消息";
			$access_token=getAccessToken();
            $result=$this->transmitText($object,$content);
        }else if(trim(substr($keyword,0,6))=="创建"){
            $groupName=trim(substr($keyword,6,strlen($keyword)));
            if($groupName==""){
                $content="请输入组名\n格式：创建计算机学院青年志愿者协会";
				$result=$this->transmitText($object,$content);
				return $result;
            }
            $content=createGroup($object->FromUserName,$groupName);
			$result=$this->transmitText($object,$content);
        }else if(trim(substr($keyword,0,6))=="姓名"){
			$entity=trim(substr($keyword,6,strlen($keyword)));
            if($entity!=""){
				if(!changeName($object->FromUserName,$entity)){
					$content="修改备注失败";
				}else{
					$content="修改备注成功";
				}
			}else{
				$content="请输入姓名";
			}
			$result=$this->transmitText($object,$content);
		}else if(trim(substr($keyword,0,9))=="签到码"){
			$groupName=substr($keyword,9,strlen($keyword));
			$content=createSignInQRUrl($object->FromUserName,$groupName);
			if($content!="sorry，您不是该小组的管理员"){
				$content="<a href='{$content}'>点此打开签到二维码页面</a>";
			}
			$result=$this->transmitText($object,$content);	
		}else if(trim(substr($keyword,0,9))=="二维码"){
			$groupName=substr($keyword,9,strlen($keyword));
			$mediaId=getGroupQRCodeMediaId($groupName);
			$access_token=getAccessToken();
			sendImage($object->FromUserName,$access_token,$mediaId);
			$result=$this->transmitText($object,$groupName."\n未关注用户扫码点关注后即可加入小组");
		}else if(trim(substr($keyword,0,6))=="跨屏"){
			$entity=trim(substr($keyword,6,strlen($keyword)));
            if($entity!=""){
				$content=crossScreenQRCode($object->FromUserName,$entity);
			}else{
				$content="请输入小组名";
			}
			$result=$this->transmitText($object,$content);
		}else if(trim(substr($keyword,0,6))=="加入"){
			$entity=trim(substr($keyword,6,strlen($keyword)));
            if($entity!=""){
				$link=sql_connect();
				$row=mysqli_fetch_assoc(mysqli_query($link,"select Id from Groups where GroupName='$entity' limit 1"));
				$content=joinInGroup($object->FromUserName,$row['Id']);
			}else{
				$content="请输入小组名";
			}
			$result=$this->transmitText($object,$content);
		}else{
            $content="无效指令\n".date("Y-m-d H:i:s",time());
            $result=$this->transmitText($object,$content);
        }
        return $result;
    }
    private function receiveImage($object){
        $content=array("MediaId"=>$object->MediaId);
        $result=$this->transmitText($object,$content['MediaId']);
        //$result=$this->transmitImage($object,$content);
        return $result;
    }
    private function receiveVoice($object){
        $content="Your voice's id:\n".$object->MediaId;
        $result=$this->transmitVoice($object,$content);
        return $result;
    }
    private function receiveVideo($object){
        $content="Your video's id:\n".$object->MediaId;
        $result=$this->transmitVideo($object,$content);
        return $result;
    }
    private function receiveLocation($object){
        $content="Your location:\n".$object->Location_X.", ".$object->Location_Y."\n".$object->Scale."\n".$object->Label;
        $result=$this->transmitText($object,$content);
        return $result;
    }
    private function receiveLink($object){
        $content="Your link:\n".$object->Title."\n".$object->Description."\n".$object->Url;
        $result=$this->transmitText($object,$content);
        return $result;
    }
    private function receiveEvent($object)
    {
        $content="";
        switch ($object->Event){
            case 'subscribe':
				$content="欢迎关注quatarks by 秋风有信，凉笙无歌";
				if(!empty($object->EventKey)){//扫码关注并加入小组或进行签到
					$entity=trim(substr($object->EventKey,8,strlen($object->EventKey)));
					$flag=substr($entity,0,1);
					$groupId=substr($entity,1,strlen($entity));
					if($flag=="1"){
						$content=joinInGroup($object->FromUserName,$groupId);//扫码加入小组
					}else{
						$content=$content."\nEventKey:$object->EventKey\nflag=$flag\ngroupId=$groupId";
					}
				}
                $result=$this->transmitText($object,$content);
                break;
            case 'CLICK':
				switch($object->EventKey){
					case 'v1_CreateCode':
						if(!$groupId=isHeadman($object->FromUserName)){
							$content="sorry，您不是任何小组的管理员,不能生成签到二维码";
							$result=$this->transmitText($object,$content);
						}else{
							if(count($groupId)=="1"){//若返回的数组只有1个元素则直接生成签到二维码
								$content=createSignInQRUrlByGroupId($object->FromUserName,$groupId[0]);
								if(gettype(strpos($content,'http://'))!=boolean){
									$content="<a href='{$content}'>点此打开签到二维码页面</a>";
									$content=$content."\n\n您也可以在其他显示屏访问webqr.dream.ren,然后用本平台的“扫一扫签到”获得跨屏显示二维码";
								}
								$result=$this->transmitText($object,$content);
							}else{//若返回的数组含有多个元素则需发文字消息进行判断
								$content="由于您在多个小组担任管理员，请回复“签到码+组名”,例 签到码计算机学院青年志愿者协会";
								$content=$content."\n\n您也可以在其他显示屏访问webqr.dream.ren,然后用本平台的“扫一扫签到”获得跨屏显示二维码";
								$result=$this->transmitText($object,$content);	
							}
						}
						break;
					case 'v1_SignIn':
						$content="There will be your times of signing in.";
						$result=$this->transmitText($object,$content);
						break;
					case 'v1_ChangeName':
						$content="请回复“姓名+您的姓名”修改您在小组的备注 例：姓名张三";
						$result=$this->transmitText($object,$content);
						break;
					case 'v1_CreateGroup':
						$content="请回复“创建+组名”,例 创建计算机学院青年志愿者协会";
						$result=$this->transmitText($object,$content);
						break;
					case 'v1_GroupQRCode':
						$groupInfo=getGroupInfo($object->FromUserName);//根据openid获取她所担任管理员的小组的信息
						$count=count($groupInfo);
						if($count==0){
							$content="sorry,您不是任何小组的管理员";
						}else if($count==1){
							$access_token=getAccessToken();
							$mediaId=getQrId($groupInfo[0]['Groups_Url']);
							sendImage($object->FromUserName,$access_token,$mediaId);
							$content=$groupInfo[0]['Groups_GroupName']."\n未关注用户扫码点关注后即可加入小组";
						}else{
							$groupName="";
							for($i=0;$i<count($groupInfo);$i++){
								$groupName=$groupName.($i+1).".".$groupInfo[$i]['Groups_GroupName']."\n";
							}
							//$content=print_r($groupInfo,1);
							$content=$groupName."以上均为您担任管理员的小组，请手动输入“二维码+小组名”获取指定小组的二维码，例：二维码九班";
						}
						$result=$this->transmitText($object,$content);
						break;
					default:
						$result=$this->transmitText($object,$content);
						break;
				}
                break;
			case 'scancode_waitmsg':
				switch($object->EventKey){
					case 'v1_ScanSignIn':
						$url=$object->ScanCodeInfo->ScanResult;//获取扫码结果
						$urlInfo=parse_url($url);
						parse_str($urlInfo['query'],$parameter);
						if(isset($parameter['login'])){
							setPageId($object->FromUserName,$parameter['login']);
							$content="请输入跨屏+小组名创建跨屏签到二维码，例： 跨屏九班\n\n请您务必先扫描二维码登录后再发送该消息，否则创建的二维码为无效二维码";
							$result=$this->transmitText($object,$content);
						}else{
							//$content=$urlInfo['query'];
							if($parameter['flag']==3){
								$content=signInByScanCrossScreen($object,$parameter);
							}else if($parameter['flag']=="2"){
								//$content=urldecode(base64_decode($parameter['group']));
								$content=signInByScan($object,$parameter);
							}else{
								$content="签到失败\nflag错误";
							}
							$result=$this->transmitText($object,$content);
						}
						break;
					default:
						$result=$this->transmitText($object,"waitmsg NULL");
						break;
				}
				break;
            case 'unsubscribe':
				//取消关注的用户将其在数据库中的记录全部删除
				delete_user($object->FromUserName);
            	break;
			case 'SCAN':
				$entity=$object->EventKey;
				$flag=substr($entity,0,1);//拆解场景值，第一位为1表示小组永久二维码，为2表示小组签到临时二维码
				$groupId=substr($entity,1,strlen($entity));//按照小组永久二维码的组装格式，第一位为1，剩下的数字为小组Id
				if($flag=="1"){
					$content=joinInGroup($object->FromUserName,$groupId);//扫码加入小组
				}else{
					$content=$content."\nEventKey:$object->EventKey\nflag=$flag\ngroupId=$groupId";
				}
				$result=$this->transmitText($object,$content);
				break;
            default:
                $content="NULL";
                $result=$this->transmitText($object,$content);
                break;
        }
        return $result;
    }
    private function transmitText($obj,$con){//组装文本消息
        $time = time();
        $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                     </xml>";
        $result = sprintf($textTpl, $obj->FromUserName, $obj->ToUserName, $time, "text", $con);  
        return $result;
    }   
    private function transmitImage($object,$imageArray){//组装图片消息
        $itemTpl="<Image>
                    <MediaId><![CDATA[%s]]</MediaId>
                  </Image>";
        $item_str=sprintf($itemTpl,$imageArray['MediaId']);
        $textTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>;
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    $item_str 
                  </xml>";
        $result=sprintf($textTpl,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }
    private function transmitVoice($object,$voiceArray){//组装语音消息
        $itemTpl="<Voice>
                    <MediaId><![CDATA[%s]]></MediaId>
                  </Voice>";
        $item_str=sprintf($itemTpl,$voidArray['MediaId']);
        $textTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>;
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[voice]]></MsgType>
                    $item_str
                  </xml>";
        $result=sprintf($textTpl,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }
    private function transmitVideo($object,$videoArray){//组装视频消息
        $itemTpl="<Video>
                    <MediaId><![CDATA[%s]]></MediaId>
                    <ThumbMediaId><!CDATA[%s]]></ThumbMediaId>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                  </Video>";
        $item_str=sprintf($itemTpl,$videoArray['MediaId'],$videoArray['ThumbMediaId'],$videoArray['Title'],$videoArray['Description']);
        $textTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>;
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[voice]]></MsgType>
                    $item_str
                  </xml>";
        $result=sprintf($textTpl,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }
    private function transmitNews($object, $newsArray){//组装图文消息
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "<item>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                    </item>";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>
                    $item_str
                    </Articles>
                </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }
    private function transmitMusic($object,$musicArray){//组装音乐消息
        $itemTpl="<Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                  </Music>";
        $item_str=sprintf($itemTpl,$musicArray['Title'],$musicArray['Description'],$musicArray['MusicUrl'],$musicUrl['HQMusicUrl']);
        $textTpl="<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>;
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[music]]></MsgType>
                    $item_str
                  </xml>";
        $result=sprintf($textTpl,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }
}
?>
