<?php
namespace App\Console\Commands;

use App\Server\Chat\ChatHandle;
use webSocket\Commands\WebSocketCommand;

/**
 * 负责绑定启动命令、绑定webSocket的处理程序。
 * Class ChatWebSocketCommand
 * @package App\Console\Commands
 */
class ChatWebSocketCommand extends WebSocketCommand
{
    /**
     * artisan 启动命令
     * @var string
     */
    protected $signature = 'Chat:Start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'chat Server';

    /**
     * 设置websocket处理程序
     * @return mixed
     */
    protected function getBindClass()
    {
        return ChatHandle::class;
    }

    /**
     * 获取本命令启动的websoket读取的配置文件，默认为websocket.php文件
     * @return string
     */
    protected function getConfig(){
        return 'websocket';
    }


}