
###### 接口功能
> 获取用户基本信息
###### URL
> [http://www.api.com/index.php/user/get?id=1&token=12332](#)
###### 返回数据格式
> JSON
###### HTTP请求方式
> GET
###### 请求参数
<table cellspacing=0 cellpadding=0 style="border-collapse:collapse;">
    <tr>
        <th>参数</th>
		<th>是否必须</th>
		<th>说明</th>
    </tr>
	<tr>
	    <td>id</td>
		<td>是</td>
		<td>数字格式用户ID</td>
	</tr>
	<tr>
	    <td>token</td>
		<td>是</td>
		<td>用户登录凭证,16位字符串</td>
	</tr>
</table>

###### 返回字段
>	成功：	{"status":true,"data"{"id":1,"name":"jack","nickname":"monkey","age":18}}

>	错误： {"status":false,"data":"错误原因"}