<?php

// 加载类
require("lib/lib.php");

//获取需要生成api的保存路径
pregMatch::$readDir = str_replace('\\','/',$_POST['file_url']);

// 生成的文档文件夹名称
pregMatch::$savePathName = basename($_POST['file_url']);

// 生成文件存储路径
pregMatch::$savePathFull = './'.pregMatch::$savePathName;

// 文档标题
pregMatch::$apiTitle = trim($_POST['api_title']);


if(file_exists(pregMatch::$savePathFull)){
	// api文档已经存在则删除文档
	pregMatch::deltree(pregMatch::$savePathFull);
}else{
	//创建该文件夹
	mkdir('./'.pregMatch::$savePathName);
}

// 遍历文件生成搜索用的json数据
pregMatch::getSearchArr(pregMatch::$readDir);


// 详情页左侧列表
foreach (pregMatch::$allClass as $key => $value) {
	pregMatch::$leftMenu.='<div class="class_row"><a href="./'.trim($value['class_name']).'.html">'.$value['class_name'].'</a><span>'.$value['class_des'].'</span></div>'; 
}


// 处理所有的array(文件路径=》文件名)
$indexTree = array();
if(!empty(pregMatch::$indexTree))
{
	foreach (pregMatch::$indexTree as $key => $value)
	{
		$indexTree[current($value)][] = $value[1];
	}	
}
$tmep = "";
// 拼接主页目录树HTML
foreach ($indexTree as $key => $value)
{
	$tmep.="<ul style='list-style-type:none'>";
	$tmep.="<li><font ><b>".$key."</b></font></li>";
	$tmep.="<ul style='list-style-type:none'>";
	foreach ($value as $kk => $vv)
	{
		$link = './'.current(explode('.',$vv)).'.html';
		$tmep.="<li><font>&nbsp;&nbsp;&nbsp;&nbsp;<b>".'--|  <a href="'.$link.'">'.$vv."</a></b></font></li>";
	}
	$tmep.="</ul>";
	$tmep.="</ul><br/>";
}

//主页模板
$index = file_get_contents("./tpl/indexsource.html");
// 主页目录树
$index = str_replace("{t_tree}",$tmep,$index);
// 主页标题
$index = str_replace('{t_title}',pregMatch::$apiTitle,$index);
// 生成文件
file_put_contents(pregMatch::$savePathFull.'/index.html', $index);

//遍历文件生成Api文档
pregMatch::tree(pregMatch::$readDir);

// 静态资源复制
pregMatch::copydir('./static',pregMatch::$savePathFull.'/static');

// 脚本运行完成提示
echo "Api 文档生成成功！"."<a href='".pregMatch::$savePathFull."/index.html'>点击访问Api文档!</a>";

?>
