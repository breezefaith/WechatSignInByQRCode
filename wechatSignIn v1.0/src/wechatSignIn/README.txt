1、allUserFuction.php 包含修改备注、扫码签到、加入小组等普通小组成员功能的具体实现

2、headmanFunction.php 包含创建小组、获取小组二维码、生成签到二维码、跨屏二维码登录、成员管理、数据报表、判断是否为管理员功能的具体实现

3、function.php 包含生成小组二维码、获取AccessToken、发送http请求等功能的具体实现

4、mysqlFunction.php 包含数据库基本操作包装的数据库连接、查询、更新、删除、插入等函数

5、mysql.php 此为功能测试页，用于测试单个功能能否正确实现

6、phpqrcode.php 利用短链接生成小组二维码必备的文档

7、sendMessage.php 微信公众号群发各类消息函数文件，用于日后发布公告功能

8、time.php 与跨屏二维码页面所在服务器保持时间同步

9、wx_sample_test.php 微信公众平台主文件，包含了wechatCallbackapiTest类，用于实现用户与微信公众平台之间消息的发送接收和处理

10、images文件夹存储的是各小组的二维码

11、qrcode文件夹为微信公众平台内生成动态二维码的具体实现

12、manage文件夹为成员管理页面具体实现

13、group文件夹为数据报表，即组长查看各组员签到情况的页面的具体实现
