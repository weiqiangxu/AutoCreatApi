#  PHP_DOC

1. 使用方法：将项目放在网站根目录下，浏览器访问填写需要读取的项目文件路径和生成的文档名称点击生成即可。

2.  示例代码：

#
	/**
	  * @method B2C主库表基本操作
	  * @author soul
	  * @copyright 2016/3/31
	  */
	class B2cBase
	{
		protected $B2CDb = null;
		public function __construct($B2CDb = null)
		{	
			$this->B2CDb = $B2CDb==null ?  new LibDb (mvc::$cfg['DB']['B2C']): $B2CDb;
		}
	
		/**
		  * @method 获取某个表的主键下一个自增ID
		  * @param  $Table 表明 BC_NUMBER
		  * @author soul
		  * @copyright 2016/3/30
		  * @return 
				成功 ["status"=>true, "data"=> $AutoId] 
				失败 ["status"=>false, "data"=> 错误信息] 
		  */
		public function getTabelAutoId($Table)
		{
			//code
		}
	}

#

3. 生成文档图示：

![](https://raw.githubusercontent.com/weiqiangxu/php_doc/master/static/image.png)


4. 程序解析：逻辑很简单，就是读取项目文件夹，遍历所有符合文件类型要求的文件然后正则匹配获取注释以及类库名和方法名，然后读取HTML模板，渲染数据并输出保存文件以及生成类库-方法的目录树。不采用[phpDocumentor](https://www.phpdoc.org/)工具生成文档是因为公司代码注释风格并不严格符合其注释标准，为了文档可以适应自定义注释风格只能自己写正则匹配数据，但是文档的模板是才有该工具的模板，运用抓取工具获取到的静态模板，这个工具的好处在于，你的注释风格可以非常的自定义而仅仅需要更改一部分的正则表达式，各位朋友有任何问题欢迎微信或者QQ我哦！