<?php
function getId($link,$tableName){
	//$link=sql_connect();
	$result=mysqli_query($link,"select * from {$tableName}");
	$rownum=mysqli_num_rows($result);
	mysqli_data_seek($result,$rownum-1);
	$row=mysqli_fetch_assoc($result);
	//var_dump($row);
	//print "<br/>";
	return $row['Id'];
}
function sql_connect()//数据库连接函数
{
    $mysql_host = "localhost";
    $mysql_host_s = "localhost";
    $mysql_port = "3306";
    $mysql_user = "signin";
    $mysql_password = "wechatsignin";
    $mysql_database = "test";
    $link =new mysqli($mysql_host_s,$mysql_user,$mysql_password,$mysql_database);
	if(mysqli_connect_errno()){
		echo $err=mysqli_connect_error();
		return $err;
	}
    mysqli_query($link,"SET NAMES 'UTF8'");
    return $link;
}
//创建一个数据库表
function _create_table($link,$sql){
    if(!mysqli_query($link,$sql))
		return 0;
	//echo "创建表成功！<br/>";
    return 1;
}
//删除一个数据库表
function _drop_table($link,$sql){
	if(!mysqli_query($link,$sql))
		return 0;
	//echo "删除表成功！<br/>";
    return 1;
}
//删除数据
function _delete_data($link,$sql){
    if(!mysqli_query($link,$sql)){
		//echo mysqli_error($link);
		return 0;    //删除失败
    }else{
        if(mysqli_affected_rows()>0){
			//echo "delete success!<br/>";
            return 1;    //删除成功
        }else{
			//echo "delete success but no row affected!<br/>";
            return 2;    //没有行受到影响
        }
    }
}
//插入数据
function _insert_data($link,$sql){
	if(!$link->query($sql)){
		//echo $link->error;
		return 0;
	}else{
		//echo "insert success!<br/>";
		return 1;
	}
	/*if(!mysqli_query($link,$sql)){
		echo mysqli_error($link);
	}else{
		echo "insert success!<br/>";
		return 1;
	}*/
	/*mysqli_query($link,$sql);
	if(!mysqli_errno($link)){
		echo "insert success!<br/>";
		return 1;
	}else{
		echo mysqli_error($link);
	}*/
}
//修改数据
function _update_data($link,$sql){
    if(!mysqli_query($link,$sql)){
		return 0;    //更新数据失败
    }else{
        if(mysqli_affected_rows()>0){
            return 1;    //更新成功;
        }else{
			return 2;    //没有行受到影响
        }
    }
}
//检索数据
function _select_data($link,$sql){
    if(!($result = mysqli_query($link,$sql)))
		return 0;
    return $result;
}
?>