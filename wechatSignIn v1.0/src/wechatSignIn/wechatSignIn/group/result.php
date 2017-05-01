<?php
header("Content-Type: text/html; charset=UTF-8");
include("connect.php");
$GroupName=$_POST['name'];
$SignInHeadman_1=$_POST['headman'];
$time_before=strtotime($_POST['time_before']);
$time_after=strtotime($_POST['time_after']);
$SignInHeadman='';
$_signInTime='';
$_signInHeadman='';
$man=array();

$result=mysqli_query($con,"select Openid from `".$GroupName."` where `Id`='".$SignInHeadman_1."'");
if ($result) 
{
  while($rows=mysqli_fetch_array($result)) {
        $SignInHeadman=$rows['Openid'];
        }
}
$persons=mysqli_query($con,"select Name,SignInTime,SignInLatestTime,SignInHeadman from ".$GroupName);  
if ($persons) 
{
  echo '<table border="1px" border-style="solid" style="font-size:12px;" width="306">';
  $result=mysqli_query($con,"select LatestTime from Groups where GroupName='".$GroupName."'");
      if ($result) {
      while ( $rows=mysqli_fetch_array($result)){

        echo "<tr><th>最后签到:</th><td colspan='3'>".date("Y-m-d",$rows['LatestTime'])."</td></tr><br/>";
        }
    }
    echo '<tr><th>名称</th><th>总签到次数</th><th>指定管理员签到次数</th><th>最后签到时间</th></tr>';
  while ($person=mysqli_fetch_array($persons)) 
  { 
      $name=$person['Name']; 
      $signInHeadman=$person['SignInHeadman'];
      $signInTime=$person['SignInTime'];   
      $signInLatestTime=$person['SignInLatestTime']; 
      $signInTimes=0;
      $_signInTimes=0;
      for ($index=1; $index < strlen($signInHeadman); $index++) 
      { 
          if ($signInHeadman[$index]!=',')
          {
            $_signInHeadman=$_signInHeadman.$signInHeadman[$index];
          }
          if ($signInHeadman[$index]==','||$index==strlen($signInHeadman)-1) 
          {
            $man[]=$_signInHeadman;
            $_signInHeadman='';
          }
      }   
   
      for ($index=1; $index < strlen($signInTime); $index++) 
      { 
          if ($signInTime[$index]!=',')
          {
            $_signInTime=$_signInTime.$signInTime[$index];
          }
          if ($signInTime[$index]==','||$index==strlen($signInTime)-1) 
          {
                  $arr=json_decode($_signInTime,true);
                  foreach ((array)$arr as $key => $value) 
                  {
                     if ($value>=$time_before&&$value<=$time_after) { 
                      if (current($man)==$SignInHeadman) {
                        $_signInTimes++;
                      }
                      $signInTimes++;
                    }
                      next($man);
                  }
                  $_signInTime='';
          }
      }
       $arr=json_decode($signInLatestTime,true);
       foreach ((array)$arr as $key => $value) 
       {
          $_signInLatestTime=date("Y-m-d",$value);
       }             
       unset($man);
       $man=array();

      echo "<tr><td>".$name."</td><td>".$signInTimes."</td><td>".$_signInTimes."</td><td>".$_signInLatestTime."</td></tr>";
  }
    echo "</table>";
  }

?>