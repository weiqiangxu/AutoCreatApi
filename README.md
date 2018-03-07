# PHP_DOC

1. 根据PHP注释自动检索项目文件生成Api文档.

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
