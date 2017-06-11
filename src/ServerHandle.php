<?php

namespace webSocket;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ServerHandle
{
    const SYS_ERROR = "SysError";

    public $server = false;
        
    static $channels = [
    ];
    
    public function __construct(WebSocket $webSocket)
    {
        $this->server = $webSocket;
    }

    /**
     * 反馈给client的结果
     * @param $action 操作类型
     * @param $status 反馈的结果
     * @param $message 反馈的信息
     * @return array
     */
    public function results($action,$status,$message = array()){
        return [
            'action'=>$action,
            'status'=>$status,
            'message'=>$message
        ];
    }

    /**
     * 调试模式下调试发布与订阅机制
     * @param $channel
     * @param $data
     */
    protected function call($channel,$data){
        foreach(self::$channels as $c => $class){
            if($c == $channel){
                /**
                 * @var $Object RedisSubscribe
                 */
                $Object = new $class($data);
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
            Redis::lpush($channel,is_string($data)?$data:json_encode($data));
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

    public function finish($task_id,$data){

    }

    public function close($fd){
        log:info($fd." is close !");
    }
}