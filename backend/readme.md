#<center>聊天室后台</center>  

##协议设计
###协议包含四种数据交换类型
* 请求（request）
* 相应（response）
* 广播（broadcast）
* 心跳（heartbeat）  

###相应字段、内容及含义
* 请求  
<table>
	<tr>
		<th>字段</th><th>值</th><th>含义</th>
	<tr>
		<td>type<td>get<br>broadcast<td>get 请求相应<br>broadcast  请求广播到其他连接到服务器的客户端
	<tr>
		<td>source<td>java, c++ ,etc<td>客户端编程语言及版本
	<tr>
		<td>data-type<td>string, json, file, image<td>传输的数据类型，包括字符串，json格式，文件，图片
	<tr>
		<td>data<td>任意字符串<td>传输的数据

</table>

|| delay || 正整型，默认为0 || 相应延时，单位毫秒 ||
		
		
|| keep-alive || 布尔值 || 是否维持连接，若为否则响应之后立即断开连接 ||




##<br/>后台部分
###后台目录讲解：
><b>public.action.php：包含数据库配置信息，数据库封装函数  
>server.action.php：服务器类，含socket封装与功能函数调用  
>socket.action.php：协议解析函数实现  
>function.action.php：功能函数实现  
>testClient.action.php：测试用例，模拟客户端，可以用来中断服务器进程以查看调试信息  
>forgetPw.action.php：验证用页面  

###项目不足之处：
>1. server.action.php 实质上太臃肿了，没有进一步分离功能  
>2. 验证功能做的很粗糙  
>3. Java的JSON封装与PHP迥异，所以解析是单独写的，耦合程度太高  
>4. 图片传递时，JSON封装会使字节流转换为字符流，此问题暂未解决。未避免破坏整体封装性，暂将该功能弃用  

###项目拓展
>1. 理论上理论上可以无限拓展，因代码功能分化比较明确，耦合程度低（除上文所说JSON解析部分）  
>2. 协议设计的时候是包括了心跳检测的，但实际实现中并没有做这一点。要加入这一点并不困难，维护链接的时候检测生命即可，接收心跳的时候刷新生命周期  
>3. 客户端下线的时候可以发送请求，服务器推送名单给所有客户端，即可实现在线状态查看  
>4. 消息记录可以存在数据库中，并设置未读消息提醒，但数据库设计难度大  
