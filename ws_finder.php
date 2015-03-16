<html>
<head>
	<title>webshell检测工具</title>
	<meta charset="utf8"/>
</head>
<p>注意啦：首先请将本文件放在web根目录</p>

<form method="post" action="">
<input type="hidden" name="find" id="find" value="1"/>
<input type="submit" value="submit"/>
</form>

<?php
/*
*Author:Ericcc
*Time:2015.3.16
*At HDUISA
*/

@$find = $_POST["find"];
#这个数组是大马的敏感函数，更新的时候可以直接更新数组内容，注意敏感函数只写名字，不写括号
#$arr = array("1111","222");
#小马的敏感函数，注意同上
#$arr_ass = array("1111","2222");
function listDir($dir){
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while(($file = readdir($dh))){
				if(is_dir($dir."/".$file) && $file != "." && $file != ".."){
					echo $dir."/".$file;
					listDir($dir."/".$file);	
				}
				elseif((string)$file[strlen($file)-1]=="p"&&(string)$file[strlen($file)-2]=="h" && $file!="WebSell_finder.php"){

					$result = checkFile($dir."/".$file);
					if($result != "safe"){
						echo "<br/>".$dir."/".$file."可能是".$result."<br/>";
					}

				}else{
					continue;
				}
			}
		}

	}
}
#这个函数检查特定文件是否是webshell
function checkFile($thisFile){
	$file = fopen($thisFile,"r");
	#下一个变量是危险指数，危险指数是一个文件中检测出的敏感函数的个数，当一个文件的危险指数大于3时，被认为可能是大马
	$dangIndex = 0;
	#下一个变量是次危险指数，目的是检测是否有小马;
	$assIndex = 0;
	while(!feof($file)){
		$string = fgets($file);
		if(checkShell($string)==1){
			$dangIndex = $dangIndex+1;

		if(checkAssShell($string)==1)
			$assIndex++;
		}
	//	var_dump($dangIndex);
	}
	#返回这个文件的检测结果
	if($dangIndex>3){
		return "webShell";
	}elseif($assIndex>=1){
		return "smallSehll";
	}else{
		return "safe";
	}
	
	fclose($file);
}
#这个函数检测一个字符串中是否包含敏感php函数
function checkShell($string){
	foreach(array("command","system","fsockopen","disk_free_space","disk_total_space","php_uname") as &$sth){
		if(strstr($string,$sth)!=false){
			return 1;
		}
	}
	return 0;
}
#这个函数检测一个字符串中是否包含小马的敏感函数
function checkAssShell($string){
	foreach(array("eval") as $sth){
		if(strstr($string,$sth)!=false){
//			echo (int)strstr($string,$sth);
	//		echo $string."---------".$sth;
	//		echo strpos($string,$sth);
                	return 1;
        	}
        }
        return 0;
}
if($find==1){
	listDir(getcwd());
}
