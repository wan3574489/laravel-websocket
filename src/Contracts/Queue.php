<?php

namespace webSocket\Contracts;

use Illuminate\Support\Facades\Redis;

class Queue
{
    /**
     * 发布服务
     * @param $channel
     * @param $data
     */
     public function push($channel,$data){
        Redis::lpush($channel,is_string($data)?$data:json_encode($data));
    }

    /**
     * 阻塞获取最后一个元素
     * @param $channel
     * @return mixed
     */
     public function brpop($channel){
        while(true){
            if($value = Redis::brpop($channel, 10)){
                return $value;
            }
        }
    }

    /**
     * 阻塞获取第一个元素
     * @param $channel
     * @return mixed
     */
     public function blpop($channel){
        while(true){
            if($value = Redis::blpop($channel, 10)){
                return $value;
            }
        }
    }

    /**
     * 非阻塞获取第一个元素
     * @param $channel
     * @return mixed
     */
    public function lpop($channel){
        return Redis::lpop($channel);
    }

    /**
     * 清空队列
     * @param $channel
     */
    public function clean($channel){
        Redis::ltrim($channel,1,0);
    }
}