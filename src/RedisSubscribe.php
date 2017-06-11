<?php
/**
 * Created by PhpStorm.
 * User: pz
 * Date: 2017/6/11
 * Time: 21:10
 */

namespace webSocket;


abstract class RedisSubscribe
{
   protected $data;

   protected $channel;

   function setData($data)
   {
     $this->data = $data;
   }
   
   function reset(){
      $this->data = '';
      $this->channel = '';
   }
   
   abstract function handle();
}