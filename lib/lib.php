<?php
/**
	* 读取项目注释
	* @author xu
	* @copyright 2018/01/18
*/	
class pregMatch{

	// 读取的项目地址
	public static $readDir;
	// 生成的api文档标题
	public static $apiTitle;
	//需要读取的的文件的后缀名规则
	public static $fileNameExtend = array(".class.php","Action.php");
	//生成的api文件夹名称
	public static $savePathName;
	//生成的api文件路径
	public static $savePathFull;
	// 主页目录树
	public static $indexTree = [];
	// 所有的类和描述
	public static $allClass = [];
	// 所有方法
	public static $allFunction = [];
	//详情页左侧目录树
	public static $leftMenu = "";


    /**
     * 读取某一个文件的注释
     * @access public
     * @param directory  string ？？？
     * @param content  string  读取到的项目文件内容
     * @param files  string 当前读取的文件名称
     * @return void
     */
	public static function create($directory,$content,$files){
		//存档一份当前读取的文件内容，下面读取function时候会被删除
		$contents = $content;
		//输出到生成文件数据样式
		$tmp = file_get_contents('./tpl/function.html');

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
		$html_header = file_get_contents('./tpl/header.html');
		//将去除绝对路径后当前类文件路径插入模板
		$now_class_url_only = str_replace(self::$readDir,"",$directory.'/'.$files);
		$html_header = str_replace('{t_location}',$now_class_url_only,$html_header);
		//模板页面左侧类文件列表数据
		$html_header = str_replace('{t_class_tree}',self::$leftMenu,$html_header);
		//模板页面右侧的文档标题
		$html_header = str_replace('{t_title}',self::$apiTitle,$html_header);
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
	 	$html_footer = file_get_contents('./tpl/footer.html');
	 	$html_footer = str_replace('{all_function_url_json}',json_encode(self::$allFunction),$html_footer);
		$t.=$html_footer;

		file_put_contents(self::$savePathFull.'/'.current(explode('.', $files)).'.html', $t);
	}

    /**
     * 遍历读取文件并传递给create解析
     * @access public
     * @param directory  string  遍历的项目文件路径
     * @return void
     */
	public static function tree($directory)
	{
		//下面文件才会被检索输出api文档
		$mydir = dir($directory);
		// Directory::read — 从目录句柄中读取条目
		while($file = $mydir->read())
		{	
			if((is_dir("$directory/$file")) AND ($file!=".svn")  AND ($file!=".") AND ($file!=".."))
			{
				//递归遍历读取
				self::tree("$directory/$file");
			}
			elseif(($file!=".") AND ($file!="..") AND (substr(strrchr($file, '.'), 1)=='php'))
			{
				//过滤不符合文件名后缀的文件
				foreach (self::$fileNameExtend as $key => $value)
				{
					//文件名与规定的文件名规则匹配才会生成api
					if(strpos($file,$value))
					{	
						// 读取文件内容
						$content = file_get_contents($directory.'/'.$file);
						//将路径、文件内容、文件名称传递过去解析
						self::create($directory,$content,$file);
					}
				}
			}
		}
		// 关闭资源句柄
		$mydir->close();
	}



    /**
     * 获取所有类名、方法、主页目录的用的数组（文件路径-》文件名称）
     * @access public
     * @param directory  string  遍历的项目文件路径
     * @return void
     */
	public static function getSearchArr($directory)
	{	
		//下面文件才会被检索输出api文档
		$mydir = dir($directory);
		
		while($file = $mydir->read())
		{
			if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!=".svn")  AND ($file!=".."))
			{
				//如果是目录则继续往下读取文件
				self::getSearchArr("$directory/$file");
			}
			elseif(($file!=".") AND ($file!="..") AND (substr(strrchr($file, '.'), 1)=='php')) 
			{
				//此时对具体的文件进行内容解析
				foreach (self::$fileNameExtend as $key => $value) {
					//文件名与规定的文件名规则匹配才会生成api
					if(strpos($file,$value))
					{
						$content = file_get_contents($directory.'/'.$file);
						//获取当前类为的类名
						//因为类名都是文件名所以可以直接获取
						$class_name = current(explode('.',$file));
						//匹配获取类的说明,如果没有就赋值为null
						$reg = "/method (.+)[\s\S]+?\*\/[\s\S]+?class[\s]+".$class_name."/";
						if(preg_match_all($reg, $content, $match))
						{
							$class_des = current($match[1]);
						}else{
							$class_des = '';
						}
						//拼接所有的类文件以及类的描述，页面左侧显示						
						array_push(self::$allClass,array('class_name'=>$class_name,'class_des'=>$class_des)); 

						// 获取用于拼接首页目录树的数组
						$thisFilePath = str_replace(self::$readDir,"",$directory);

						array_push(self::$indexTree, [$thisFilePath,$file]);
						
						
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
						$mFunction  = $ms[3];
						if(count($mFunction)>=1){
							foreach ($mFunction as $key => $value)
							{
								$temp = array(
									// 方法名称
									'function'=>current(explode('(',$value)),
									// 类文件href地址
									'class'=>trim($class_name).'.html#'.current(explode('(',$value)),
									// 类名称
									'class_name'=>trim($class_name).'/'.current(explode('(',$value))
								);
								// 所有的方法
								array_push(self::$allFunction, $temp);
							}
						}
					}
				}
			}
		}
		$mydir->close();
	}

    /**
     * 删除文件夹
     * @access public
     * @param pathdir string 需要清空的路径
     * @return void
     */
	public static function deltree($pathdir)
	{
		if(self::isEmptyDir($pathdir))//如果是空的
		{
			// 无操作
		}
		else
		{	
			//否则读这个目录，除了.和..外
			$d=dir($pathdir);
			while($a=$d->read())
			{
				if(is_file($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))
				{
					unlink($pathdir.'/'.$a);
				}
				//如果是文件就直接删除
				if(is_dir($pathdir.'/'.$a) && ($a!='.') && ($a!='..'))
				{
					//如果是目录
					if(!self::isEmptyDir($pathdir.'/'.$a))//是否为空
					{
						//如果不是，调用自身，不过是原来的路径+他下级的目录名
						deltree($pathdir.'/'.$a);
					}
					if(self::isEmptyDir($pathdir.'/'.$a))
					{
						//如果是空就直接删除
						rmdir($pathdir.'/'.$a);
					}
				}
			}
			$d->close();          
		}
	}

    /**
     * 判定文件夹是否为空
     * @access public
     * @param pathdir string 需要清空的路径
     * @return bool(false) 非空。true:空文件夹
     */
	public static function isEmptyDir($pathdir)
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

    /**
     * 复制文件夹
     * @param $source 目标文件夹
     * @param $dest 复制后生成的文件夹
     * @author xu
     * @copyright 2017-12-26
     */
    public static function copydir($source, $dest)
    {
        if (!file_exists($dest))
        {
            if (!@mkdir($dest, 0777)){
                return false;
            }
        }
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) copy($_source, $_dest);
            if (is_dir($_source)) self::copydir($_source, $_dest);
        }
        closedir($handle);
    }
}

