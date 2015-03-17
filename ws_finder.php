<html>
<head>
	<title>webshell检测工具</title>
	<meta charset="utf-8"/>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<div class="container" align="center">
<h1>注意啦：首先请将本文件放在web根目录</h1>
<a align="center" class="btn btn-lg btn-success" href="?find=1&level=3" role="button"><h4 align="center">以中级安全等级开始检验(推荐)</h4></a><hr/>

<a align="center" class="btn btn-lg btn-success" href="?find=1&level=1" role="button"><h4 align="center">以顶级安全等级开始检验(误报率高、不推荐)</h4></a><hr/>

<a align="center" class="btn btn-lg btn-success" href="?find=1&level=5" role="button"><h4 align="center">以低级安全等级开始检验(可能放过木马、不推荐)</h4></a><hr/>
</div>

<?php
/*
*Author:Ericcc
*Time:2015.3.16
*At HDUISA
*/

#这个数组是大马的敏感函数，更新的时候可以直接更新数组内容，注意敏感函数只写名字，不写括号
$arr = array("php_sapi_name","get_current_user","readdir","filesize","filetype","command","system","fsockopen","disk_free_space","disk_total_space","php_uname");
#这个数组是小马敏感函数，注意事项同上
$arr_ass = array("eval");

@$find = $_REQUEST["find"];
@$level = $_REQUEST["level"];
function listDir($dir,$level,$arr,$arr_ass){
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while(($file = readdir($dh))){
				if(is_dir($dir."/".$file) && $file != "." && $file != ".."){
					listDir($dir."/".$file,$level,$arr,$arr_ass);	
				}
				elseif((string)$file[strlen($file)-1]=="p"&&(string)$file[strlen($file)-2]=="h" && $file!="ws_finder.php"){

					$result = checkFile($dir."/".$file,$level,$arr,$arr_ass);
					if($result != "safe"){
						echo "<h2>发现威胁</h2>";
						echo "<br/>"."<code>".$dir."/".$file."可能是".$result."<br/></code><hr/>";
					}

				}else{
					continue;
				}
			}
		}

	}
}
#这个函数检查特定文件是否是webshell
function checkFile($thisFile,$level,$arr,$arr_ass){
	$file = fopen($thisFile,"r");
	#下一个变量是危险指数，危险指数是一个文件中检测出的敏感函数的个数，当一个文件的危险指数大于3时，被认为可能是大马
	$dangIndex = 0;
	#下一个变量是次危险指数，目的是检测是否有小马;
	$assIndex = 0;
	while(!feof($file)){
		$string = fgets($file);
		if(checkShell($string,$arr)==1){
			$dangIndex = $dangIndex+1;

		if(checkAssShell($string,$arr_ass)==1)
			$assIndex++;
		}
	}
	#返回这个文件的检测结果
	if($dangIndex>$level){
		return "webShell";
	}elseif($assIndex>=1){
		return "smallSehll";
	}else{
		return "safe";
	}
	
	fclose($file);
}



#这个函数检测一个字符串中是否包含敏感php函数
function checkShell($string,$arr){
	#这个数组是大马的敏感函数，更新的时候可以直接更新数组内容，注意敏感函数只写名字，不写括号
	#$arr = array("php_sapi_name","get_current_user","readdir","filesize","filetype","command","system","fsockopen","disk_free_space","disk_total_space","php_uname");

	foreach($arr as &$sth){
		if(strstr($string,$sth)!=false){
			return 1;
		}
	}
	return 0;
}



#这个函数检测一个字符串中是否包含小马的敏感函数
function checkAssShell($string,$arr_ass){
	#小马的敏感函数，注意同上
	#$arr_ass = array("eval");

	foreach($arr_ass as $sth){
		if(strstr($string,$sth)!=false){
                	return 1;
        	}
        }
        return 0;
}

#如果用户传递了参数过来，则开始递归调用
if($find==1){
	echo '<div class="container">';
	listDir(getcwd(),$level,$arr,$arr_ass);
	echo '</div>';
}
