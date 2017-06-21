<?php

namespace webSocket;

use webSocket\Service\PushService;
use webSocket\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ServerHandle
{
    /**
     * 系统错误提示
     */
    const SYS_ERROR         = "SysError";

    /**
     * swoole websocket实例
     * @var bool|WebSocket
     */
    protected $server = false;

    /**
     * 用户监听是否有新数据时间间隔(毫秒)
     * @var int
     */
    protected $interval   = 200;

    /**
     * 测试环境下监听频道与处理程序之间的关系
     * @var array
     */
    protected $channels = [];
    
    public function __construct(WebSocket $webSocket)
    {
        PushService::clean();

        $this->server = $webSocket;
    }



    /**
     * 反馈给client的结果
     * @param $action 
     * @param $status
     * @param $message
     * @return array
     */
    public function results($action,$status,$message = array()){
        return PushService::results($action,$status,$message);
    }

    /**
     * 添加任务到Task进程
     * @param $fd
     * @param $key
     * @param array $params
     */
    public function pushTask($fd, $key, $params = []){
        return $this->server->addTask($fd,$key,$params);
    }


    /**
     * 发送消息给某fd
     * @param $fd
     * @param $data
     */
    public function pushToFd($fd,$data){
        $this->server->push($fd,null,$data);
    }

    /**
     * 发送消息给所有的fd
     * @param $data
     */
    public function pushToAll($data){
        $this->server->pushAll($data);
    }

    /**
     * 发送消息给所有的fd，不包括$fd
     * @param $fd
     * @param $data
     */
    public function pushToAllOutMe($fd,$data){
        $this->server->pushToAllOutMe($fd,$data);
    }

    /**
     * 调试模式下调试发布与订阅机制
     * @param $channel
     * @param $data
     */
    protected function call($channel,$data){
        foreach($this->channels as $c => $class){
            if($c == $channel){
                Log::info("{$c} channel call!");

                /**
                 * @var $Object RedisSubscribe
                 */
                $Object = new $class($data);
                $Object->setData($channel,$data);
                $Object->handle();

            }
        }
    }

    /**
     * 发布消息
     * @param $channel
     * @param string $data
     */
    protected function publish($channel,$data = ''){
        if(env('APP_ENV') =='local'){
            $this->call($channel,$data);
        }else{
            Queue::push($channel,$data);
        }
    }

    /**
     * message分发
     * @param $fd
     * @param $data
     * @return mixed
     */
    public function message($fd,$data){
        if(isset($data['type'])){
            $key = "message_".$data['type'];
            if(method_exists($this,$key)){
                return $this->$key($fd,$data);
            }
        }

        Log::error("message no run ",$data);
    }

    /**
     * 任务分发
     * @param $task
     * @param $fd
     * @param $data
     * @return array
     */
    public function task($task,$fd,$data){
        $key = "task_".$task;
        if(method_exists($this,$key)){
            return $this->$task($fd,$data);
        }else{
            return $this->results(self::SYS_ERROR,0,'无处理程序');
        }
    }

    /**
     * 数据监听
     * @param $fd
     */
    public function timer($fd){
        $the = $this;

        \swoole_timer_after($this->interval,function () use ($fd,$the){
            try{
               // Log::info("{$fd}开始消费！");

                while($value = Queue::lpop(PushService::getFdChannel($fd))){
                    $this->pushToFd( $fd, $value );
                }

                if(PushService::isLive($fd)){
                    $the->timer($fd);
                }

            }catch (\Exception $e){

                Log::info("timer error:".$e->getMessage());

                $this->triggerClose($fd);
            }
        });

    }

    /**
     * 链接打开之前执行的操作
     * @param $fd
     */
    public function openBefore($fd){
        PushService::cleanFdChannel($fd);
    }

    /**
     * 打开链接
     * @param $fd
     */
    public function open($fd){

        $this->timer($fd);
    }

    /**
     * 打开链接之后
     * @param $fd
     */
    public function openAfter($fd){
        Log::info(" {$fd} is open! ");

        PushService::login($fd);

        $length = Redis::get(PushService::Store_fds_length);
        Log::info("当前 fds length {$length}");
    }

    /**
     * 任务完成
     * @param $task_id
     * @param $data
     */
    public function finish($task_id,$data){

    }

    /**
     * 链接关闭之前
     * @param $fd
     */
    public function closeBefore($fd){

    }

    /**
     * 链接关闭
     * @param $fd
     */
    public function close($fd){

    }

    /**
     * 关闭链接之后
     * @param $fd
     */
    public function closeAfter($fd){
        log:info($fd." is close !");

        PushService::out($fd);
    }

    /**
     * 触发关闭事件
     * @param $fd
     */
    public function triggerClose($fd){
        $this->openBefore($fd);

        $this->close($fd);

        $this->closeAfter($fd);
    }
}