<?php
date_default_timezone_set("PRC");
$callback=$_GET['callback'];
$arr['time']=round(microtime(true)*1000);
echo $callback.'('.json_encode($arr).')';
?>