# laravel-websocket

基于 laravel + swoole +redis的WebSocket开发库。适合开发中小型webSocket程序。

无需关注webSocket的细节，专注业务。

可同步、异步处理请求，发送数据。

## 安装方法
1. 安装laravel.

2. 添加websocket库: composer require lactone/laravel-websocket

3. 添加laravel command类，继承 webSocket\Commands\WebSocketCommand.

## Demo
在目录/demo/laravel中是一个简单的Demo程序。

### 使用方法

1. 将Demo目录中的文件覆盖到laravel程序中。
2. 配置redis。
3. 打开app/config/app.php,将默认的Redis服务提供器修改为websocket的Redis服务提供器，将Illuminate\Redis\RedisServiceProvider替换为\webSocket\Redis\RedisServiceProvider
4. 打开app/config/websocket.php 文件，修改websocket的监听ip、端口和工作进程数量。
5. 命令行执行:**php artisan Chat:Start** 开启websocket服务。

### 文件说明

    App\Console\Commands\ChatWebSocketCommand.php 负责绑定启动命令、设置websocket的配置和绑定webSocket处理程序。
    
    App\Server\Chat\ChatHandle.php 负责处理ws业务。当用户发送消息:{type:$type,data:$data}给服务器的时候，系统会自动调用message_$type方法。

    public/js/push.js 客户端发送消息程序

## 基本概念

当客户端与服务端建立webSocket连接后，服务端会生成一个fd,该fd表示当前链接，通过该fd服务端可以发送消息给客户端。
    
### 数据交互

    客户端发送给服务端结构如下：{type:type,data:data}
    其中如果type为login,服务端接受程序信息后会调用message_login方法，处理信息。
    
### 常用API
   
#### webSocket\Service\PushService

    PushService::pushToAllAsync($data) 发送信息给所有客户端
    PushService::pushToFdAsync($fd,$data) 发送消息给某个客户端
    PushService::pushToAllOutMeAsync($fd,$data) 发送消息给除开fd的其他客户端
    PushService::getAllFdsFromStore() 获取所有在线的链接标识
    PushService::isLive($fd) 客户端是否在线
    PushService::success($action,$message = array()) 反馈成功的数据结构
    PushService::error($action,$message = array()) 反馈失败的数据结构

    