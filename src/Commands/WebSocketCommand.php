<?php

namespace webSocket\Commands;

use webSocket\WebSocket;
use webSocket\ServerHandle;
use webSocket\Contracts\Queue;
use Illuminate\Console\Command;

abstract class WebSocketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WebSocket:Start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebSocket Server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('default_socket_timeout', -1);

        $webSocketConfig =  app('config')->get($this->getConfig());
        if(!$webSocketConfig){
            throw new \Exception("请设置webSocket配置!");
        }

        $webSocket = new WebSocket($webSocketConfig,$this->laravel);
        if(!$bindClass = $this->getBindClass()){
            throw new \Exception("请配置需要运行的webSocket类");
        }
        $webSocket->bind(ServerHandle::class, $bindClass);

        $webSocket->handle();
    }

    protected function getConfig(){
        return 'websocket';
    }

    protected function getBindClass(){
        throw new \Exception("请设置 Handle class 名称！");
    }
}
