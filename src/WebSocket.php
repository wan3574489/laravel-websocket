<?php

namespace webSocket;

use Illuminate\Container\Container;

class WebSocket  extends Container
{

    protected $fds = [];

    private $server;

    private $handler;

    private $app;

    function __construct($config,$app)
    {

        $this->app = $app;

        $this->instance(WebSocket::class,$this);

        $this->server = new \swoole_websocket_server($config['host'],$config['port']);

        $this->server->set(array(
            'task_worker_num'     => $config['task_worker_num']
        ));

        $this->server->on("open",array($this,"onOpen"));
        $this->server->on("message",array($this,"onMessage"));
        $this->server->on("Task",array($this,"onTask"));
        $this->server->on("Finish",array($this,"onFinish"));
        $this->server->on("close",array($this,"onClose"));
    }

    function handle(){
        $this->handler = $this->make(ServerHandle::class);

        $this->server->start();
    }

    /**
     * 添加任务
     * @param $fd
     * @param $key
     * @param array $params
     */
    public function addTask($fd,$key,$params = []){
        $this->server->task(json_encode([
            'task'=>$key,
            'fd'  =>$fd,
            'data'=>$params
        ]));
    }

    /**
     * 当用户上线
     * @param $server
     * @param $request
     */
    public function onOpen( $server , $request){
        $this->fds[$request->fd] = 1;
        if($pushMsg = $this->handler->open($request->fd)){
            $this->push( $request->fd ,null, $pushMsg );
        }
    }

    /**
     * 但任务发送给服务器
     * @param $server
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask($server , $task_id , $from_id , $data){
        $data = json_decode($data,true);
        if($data['task'] == '__message__'){
            $pushMsg = $this->handler->message($data['fd'],$data['data']);
        }else{
            $pushMsg = $this->handler->task($data['task'],$data['fd'],$data['data']);
        }

        if($pushMsg){
            $this->push( $data['fd'] ,$task_id, $pushMsg );
        }
    }

    /**
     * 当信息发送过来
     * @param $server
     * @param $frame
     */
    public function onMessage($server , $frame ){
        $data = json_decode( $frame->data , true );
        $this->addTask($frame->fd,'__message__',$data);
    }

    /**
     * 操作完成
     * @param $server
     * @param $task_id
     * @param $data
     * @return mixed
     */
    public function onFinish($server , $task_id , $data){
        return $this->handler->finish($task_id,$data);
    }

    /**
     * 当系统结束
     * @param $server
     * @param $fd
     * @return mixed
     */
    public function onClose($server , $fd){
        return $this->handler->close($fd);
    }

    /**
     * 添加反馈的信息
     * @param $fd
     * @param null $task_id
     * @param $data
     */
    public function push($fd,$task_id = null,$data){
        $isLiveClient = false;
        foreach($this->server->connections as $i){
            if($fd == $i){
                $isLiveClient = true;
                break;
            }
        }

        if($isLiveClient){
            try{
                $this->server->push( $fd , json_encode($data) );
            }catch (\ErrorException $e){
                Log::warning($fd." push data error!");
            }
        }
    }
}