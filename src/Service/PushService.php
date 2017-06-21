<?php


namespace webSocket\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use webSocket\Facades\Queue;

class PushService
{
    /**
     * 存储当前在线的fds
     */
    const Store_fds       = 'redis.set.fds';

    /**
     * 存储当前fds的长度
     */
    const Store_fds_length  = 'redis.key.fds.length';

    /**
     *   清空缓存数据
     */
    static public function clean(){
        if($values  = Redis::sscan(self::Store_fds,1)){
            foreach( $values as $key){
                Redis::srem(self::Store_fds,$key);
            }
        }

        Log::info("clean push all info !");

        Redis::set(self::Store_fds_length,0);
    }

    /**
     * 清理fd下原来的数据
     * @param $fd
     */
    static public function cleanFdChannel($fd){
        Queue::clean(self::getFdChannel($fd));
    }

    /**
     * push
     * @param $fd
     * @param $data
     */
    static public function pushToFdAsync($fd,$data){
        Queue::push(self::getFdChannel($fd),$data);
    }

    /**
     * 发送消息给所有的fd
     * @param $data
     */
    static public function pushToAllAsync($data){
        if($fds = self::getAllFdsFromStore()){
            foreach($fds as $fd){
                self::pushToFdAsync($fd,$data);
            }
        }
    }

    /**
     * 发送消息给所有的fd，不包括$fd
     * @param $fd
     * @param $data
     */
    static public function pushToAllOutMeAsync($fd,$data){
        if($fds = self::getAllFdsFromStore()){
            foreach($fds as $i){
                if($i != $fd){
                    self::pushToFdAsync($i,$data);
                }
            }
        }
    }

    /**
     * 获取从Redis得到的激活的连接数
     * @return mixed
     */
    static public function getAllFdsFromStore(){
        return Redis::smembers(self::Store_fds);
    }

    /**
     * 获取当前用户的channel
     * @param $fd
     * @return string
     */
    static public function getFdChannel($fd){
        return "webSocket.fd.".$fd;
    }

    /**
     * 将fd纳入push服务
     * @param $fd
     */
    static public function login($fd){
        Redis::sadd(PushService::Store_fds,$fd);
        Redis::incr(PushService::Store_fds_length);
    }

    /**
     * fd推出push服务
     * @param $fd
     */
    static public function out($fd){
        Redis::srem(PushService::Store_fds,$fd);
        Redis::decr(PushService::Store_fds_length);
    }

    /**
     * fd是否还是
     * @param $fd
     * @return bool
     */
    static public function isLive($fd){
        return (bool)Redis::sismember(self::Store_fds,$fd);
    }

    /**
     * 反馈需要Push的数据
     * @param $action
     * @param $status
     * @param array $message
     * @return array
     */
    static public function results($action,$status,$message = array()){
        return [
            'action'=>$action,
            'status'=>$status,
            'message'=>$message
        ];
    }

    /**
     * 执行成功消息
     * @param $action
     * @param array $message
     * @return array
     */
    static public function success($action,$message = array()){
        return [
            'action'=>$action,
            'status'=>1,
            'message'=>$message
        ];
    }

    /**
     * 执行失败消息
     * @param $action
     * @param array $message
     * @return array
     */
    static public function error($action,$message = array()){
        return [
            'action'=>$action,
            'status'=>0,
            'message'=>$message
        ];
    }
}