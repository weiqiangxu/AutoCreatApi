<?php
//获取需要生成api的保存路径

$_POST['file_url'] = str_replace('\\','/',$_POST['file_url']);
$GLOBALS['save_api_url'] = explode('/',$_POST['file_url']);
$GLOBALS['save_api_url']  = end($save_api_url);
if(file_exists('./'.$GLOBALS['save_api_url'])){
	deltree('./'.$GLOBALS['save_api_url']);
}else{
	//创建该文件夹
	mkdir('./'.$GLOBALS['save_api_url']);
}

//获取生成需要生成api读取的项目文件
$GLOBALS['file_now_url'] = explode('/',$_POST['file_url']);
array_pop($GLOBALS['file_now_url']); 
$GLOBALS['file_now_url'] = implode('/',$GLOBALS['file_now_url']);
//获取文档标题
$GLOBALS['title'] = trim($_POST['api_title']);

//请输入需要生成API文件的后缀名规则
$GLOBALS['need_create_clas'] = array(".class.php","Action.php");
$GLOBALS['index_tree'] = "";
$GLOBALS['index_tree_array'] = "";


$GLOBALS['all_class'] = array();
//开始运行
$GLOBALS['function_url_tree'] = '';
//获取搜索用的json数据
get_all_class($_POST['file_url']);
$GLOBALS['function_url_tree'] = json_encode($GLOBALS['function_url_tree']);
$GLOBALS['class_tree'] = '';
foreach ($GLOBALS['all_class'] as $key => $value) {
	$GLOBALS['class_tree'].='<div class="class_row"><a href="./'.trim($value['class_name']).'.html">'.$value['class_name'].'</a><span>'.$value['class_des'].'</span></div>'; 
}
//遍历文件生成html文档
tree($_POST['file_url']);

foreach ($GLOBALS['index_tree_array'] as $key => $value){
	$GLOBALS['index_tree'] .= "<ul style='list-style-type:none'>";
	$GLOBALS['index_tree'].="<li><font ><b>$key</b></font></li>";
	$GLOBALS['index_tree'].="<ul style='list-style-type:none'>";
	foreach ($value as $kk => $vv) {
		$link = './'.current(explode('.',$vv)).'.html';
		$GLOBALS['index_tree'].="<li><font>&nbsp;&nbsp;&nbsp;&nbsp;<b>".'--|  <a href="'.$link.'">'.$vv."</a></b></font></li>";
	}
	$GLOBALS['index_tree'].="</ul>";
	$GLOBALS['index_tree'].="</ul><br/>";
}

//生成主页
$index = file_get_contents("./indexsource.html");
$index = str_replace("{t_tree}",$GLOBALS['index_tree'],$index);
$index = str_replace('{t_title}',$GLOBALS['title'],$index);
file_put_contents('./'.$GLOBALS['save_api_url'].'/index.html', $index);

echo "Api 文档生成成功！";

echo "<a href='./".$GLOBALS['save_api_url']."/index.html'/>点击访问Api文档!</a>";



function create($directory,$content,$files){
	//存档一份当前读取的文件内容，下面读取function时候会被删除
	$contents = $content;
	//输出到生成文件数据样式
	$tmp = file_get_contents('./function.html');

	//正则匹配获取数据
	//排除类的注释
	//类名
	$this_class_name = current(explode('.',$files));
	$rege = "/\/\*[\s\S]+?\*\/[\s\S]+?class[\s]+".$this_class_name."/";
	if(preg_match_all($rege,$content,$matches)){
		$content = str_replace(current($matches[0]),'',$content);
	}

	$rege = '/\/\*+([\s\S]+)\*\/([\s\S]+)function[\s]+([a-zA-Z_0-9-]+\(.*\))/iU';
	// $rege = '/\/\*+([\s\S]+)\*\/\n(.*)function[\s]+([a-zA-Z_0-9-]+\(.*\))/iU';
	$function_des = array();
	if(preg_match_all($rege, $content, $ms)){
		foreach($ms[0] as $key => $value){
			//将 */ 后面的数据去除
			$value = explode('*/',$value);
			 array_pop($value);
			 $value = implode(" ",$value);
			 //以* @为分割符取出所有参数拼接到一个数组之中
			 $function_des[$key] = explode('* @',$value);
		}
	}

	//拼接获取类页面的方法列表
	$this_class_all_method = '';
	//ms[3]就是当前读取的和这个类文件的function列表
	foreach ($ms[3] as $key => $value) {
		$this_class_all_method.= '<li><a href="#'.current(explode('(',$value)).'">'.$value.'</a></li>';
	}

	$t = '';
	//输出到生成文件的数据第一行是文件路径
	// $t.=$directory.'/'.$files."\n";
	$html_header = file_get_contents('./header.html');
	//将去除绝对路径后当前类文件路径插入模板
	$now_class_url_only = str_replace($GLOBALS['file_now_url'],"",$directory.'/'.$files);
	$html_header = str_replace('{t_location}',$now_class_url_only,$html_header);
	//模板页面左侧类文件列表数据
	$html_header = str_replace('{t_class_tree}',$GLOBALS['class_tree'],$html_header);
	//模板页面右侧的文档标题
	$html_header = str_replace('{t_title}',$GLOBALS['title'],$html_header);
	//模板页面右上角当前类的方法列表
	$html_header = str_replace('{t_this_class_all_method}',$this_class_all_method,$html_header);

	//正则获取类的说明
	if(preg_match_all("/\/\*+[\s\S]+class[\s]+".$this_class_name."/", $contents, $match))
	{	
		$current_match = current($match);
		$html_header = str_replace('<h3><a href="#class_details">class detail</a></h3><ul></ul>','<h3><a href="#class_details">class detail</a></h3><ul>'.implode('</br> *',explode(' *',current(explode('class',current($current_match))))).'</ul>',$html_header);
	}
	

	//页面的class detail的类说明数据
	$t.= str_replace('{t_class_name}',current(explode('.', $files)),$html_header);

	foreach($function_des as $key => $value){

		//去除第一个数组.值是* 不用作为function的描述
		unset($value[0]);
		//现在得到的数字是:method 发送邮件 url email/send?token=xxx 'param  token param  ema_type这样的，处理成同一种参数对一个数组
		$m_function_des = "";
		$new_function_des = array();
		foreach ($value as $kk => $vv) {
			//获取当前键值
			$now_key = current(explode(" ",$vv));
			$vv = str_replace($now_key,"",$vv);
			$new_function_des[$now_key][] = $vv;
			// $m_function_des.="<tr><td><b>".$now_key."</b></td><td>".$vv."</td></tr>";
		}
		foreach ($new_function_des as $bb => $cc) {
			if(count($cc)>1){
				//此时同一个参数有多行，那么不要同一行显示
				$all_ccc ="";
				foreach ($cc as $bbb => $ccc) {
					$all_ccc .= '<div class="onelow">'.str_replace("\r", '<div class="marb5"></div>', trim($ccc)).'</div>';
				}
				$m_function_des.="<tr><th>".$bb."</th><td>".$all_ccc."</td></tr>";

			}else{
				//此时同一个人参数显示只有一行那么显示在同一行就可以了
				foreach ($cc as $bbb => $ccc) {
					$m_function_des.="<tr><th>".$bb."</th><td>".str_replace("\r", '<div class="marb5"></div>', trim($ccc))."</td></tr>";
				}

			}

		}
		$t_function_name = $ms[3][$key];
		$t_access = $ms[2][$key];
		if(strlen($t_access)>9){
			//此时肯定是正则匹配到了function的前面的一些说明,这里t_access数据格式是 /*dasda*/ public
			$t_access = explode("*/",$t_access);
			$t_access = end($t_access);
			$t_access = trim($t_access);
			//如果*/跟function之间隔了非常多字符，那么肯顶截取到了一部分非funciton说明的文字
			if(strlen($t_access)>40){
				$t_access ="";
			}
			if($t_access == 'public'){
				$t_access="";
			}
		}
		//对输出数据的模板进行数据替换
		//组装锚点
		$t_function_name_url = current(explode("(",$t_function_name));
	  $t.= str_replace(
	    array( '{m_function_des}','{t_function_name}','{t_function_name_url}','<span id="access"></span>'),
	    array( $m_function_des ,$t_function_name,$t_function_name_url,"<span id='access'>".$t_access."</span>"),
	    $tmp
	  );
	}
 	$html_footer = file_get_contents('./footer.html');
 	$html_footer = str_replace('{all_function_url_json}',$GLOBALS['function_url_tree'],$html_footer);
	$t.=$html_footer;

	file_put_contents('./'.$GLOBALS['save_api_url'].'/'.current(explode('.', $files)).'.html', $t);
}




function tree($directory)
{
	//下面文件才会被检索输出api文档
	$mydir = dir($directory);
	while($file = $mydir->read())
	{	
		if((is_dir("$directory/$file")) AND ($file!=".svn")  AND ($file!=".") AND ($file!=".."))
		{
			//如果是目录则继续往下读取文件
			tree("$directory/$file");
		}
		elseif(($file!=".") AND ($file!="..") AND (substr(strrchr($file, '.'), 1)=='php')) {
			//此时对具体的文件进行内容解析
			foreach ($GLOBALS['need_create_clas'] as $key => $value) {
				//文件名与规定的文件名规则匹配才会生成api
				if(strpos($file,$value)){
					$key_directory = str_replace($GLOBALS['file_now_url'],"",$directory);
					$GLOBALS['index_tree_array'][$key_directory][] = $file;
					$content = file_get_contents($directory.'/'.$file);//读取二进制文件时，需要将第二个参数设置成'rb'
					//将路径、文件内容、文件名称传递过去
					create($directory,$content,$file);
				}
			}
		}
	}
	// echo "</ul>\n";
	$GLOBALS['index_tree'].="</ul>\n";
	$mydir->close();
}

function get_all_class($directory)
{	
	//下面文件才会被检索输出api文档
	$mydir = dir($directory);
	while($file = $mydir->read())
	{
		if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!=".svn")  AND ($file!=".."))
		{
			//如果是目录则继续往下读取文件
			get_all_class("$directory/$file");
		}
		elseif(($file!=".") AND ($file!="..") AND (substr(strrchr($file, '.'), 1)=='php')) {
			//此时对具体的文件进行内容解析
		foreach ($GLOBALS['need_create_clas'] as $key => $value) {
					//文件名与规定的文件名规则匹配才会生成api
					if(strpos($file,$value)){
						$content = file_get_contents($directory.'/'.$file);
						//获取当前类为的类名
						//因为类名都是文件名所以可以直接获取
						$class_name = current(explode('.',$file));
						//匹配获取类的说明,如果没有就赋值为null
						$reg = "/method (.+)[\s\S]+?\*\/[\s\S]+?class[\s]+".$class_name."/";
						if(preg_match_all($reg, $content, $match)){
							$class_des = current($match[1]);
						}else{
							$class_des = '';
						}
						//拼接所有的类文件以及类的描述，页面左侧显示
						$GLOBALS['all_class'][] = array('class_name'=>$class_name,'class_des'=>$class_des);

						//这里拼接获取所有的class_name#function_name,作为json数据用于搜索
						//先取出类的注释,应该说删除了/* ... class 类名这一段的所有东西
						$rege = "/\/\*[\s\S]+?\*\/[\s\S]+?class[\s]+".$class_name."/";
						if(preg_match_all($rege,$content,$matches)){
							$content = str_replace(current($matches[0]),'',$content);
						}
						//匹配得到所有的/*  */ function 
						$rege = '/\/\*+([\s\S]+)\*\/([\s\S]+)function[\s]+([a-zA-Z_0-9-]+)\(/iU';
						preg_match_all($rege, $content, $ms);
						//将匹配到的数据进行归类
						$m_function  = $ms[3];
						if(count($m_function)>=1){
							foreach ($m_function as $key => $value) {
								$GLOBALS['function_url_tree'][]=array('function'=>current(explode('(',$value)),
									'class'=>trim($class_name).'.html#'.current(explode('(',$value)),
									'class_name'=>trim($class_name).'/'.current(explode('(',$value)));
							}
						}
				}
			}
		}
	}
	$mydir->close();
}

/**
* @method 清空文件夹下面的所有文件
*/


function deltree($pathdir)
{
	if(is_empty_dir($pathdir))//如果是空的
      {

      }
      else
      {//否则读这个目录，除了.和..外
          $d=dir($pathdir);
          while($a=$d->read())
          {
	          if(is_file($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))
	          	{
	          		unlink($pathdir.'/'.$a);
	          	}
	          //如果是文件就直接删除
	          if(is_dir($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))
	          {//如果是目录
	              if(!is_empty_dir($pathdir.'/'.$a))//是否为空
	              {//如果不是，调用自身，不过是原来的路径+他下级的目录名
	              	deltree($pathdir.'/'.$a);
	              }
	              if(is_empty_dir($pathdir.'/'.$a))
	              {//如果是空就直接删除
	              	rmdir($pathdir.'/'.$a);
	              }
	          }
          }
          $d->close();          
  	  }
}
function is_empty_dir($pathdir)
{
	//判断目录是否为空，我的方法不是很好吧？只是看除了.和..之外有其他东西不是为空
	$d=opendir($pathdir);
	$i=0;
	  while($a=readdir($d))
	  {
	 	 $i++;
	  }
	closedir($d);
	if($i>2){return false;}
	else return true;
}



?>
