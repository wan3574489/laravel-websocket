<?php

namespace App\Server\Chat;

use webSocket\Service\PushService;
use webSocket\ServerHandle;
use Illuminate\Support\Facades\Log;

class ChatHandle extends ServerHandle
{

    /**
     * 对客户端发送的登录消息进行处理
     * @param $fd
     * @param $data
     * @return array
     */
    public  function message_login($fd,$data){
        PushService::pushToAllOutMeAsync($fd,$this->results("login",1,[
            'message' => "{$fd} 用户登录成功!"
        ]));
    }

    /**
     * 异步消息请求处理
     * @param $fd
     * @param $data
     */
    public function message_Async($fd,$data){

        PushService::pushToAllOutMeAsync($fd,$this->results("async",1,[
            'message' => "Async 消息发送成功"
        ]));

        /*PushService::pushToAllAsync($this->results("async1",1,[
            'message' => "Async 任务发送成功1"
        ]));*/

    }

    /**
     * 客户端发送订阅事件
     * @param $fd
     * @param $data
     */
    public function message_publish($fd,$data){
        $data = is_string($data)?$data:json_encode($data);

        $this->publish("packet.rob",[
            'fd'=>$fd,
            'data'=>$data
        ]);
    }

    /**
     * 对客户端的其它消息发送信息
     * @param $fd
     * @param $data
     * @return array
     */
    public function message_chat($fd,$data){
        return $this->results("chat",1,"发送成功");
    }


    /**
     * 用户关闭了连接
     * @param $fd
     */
    public function close($fd){
        log:info($fd." is close !");

    }

}