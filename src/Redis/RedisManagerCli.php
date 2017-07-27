<?php

namespace webSocket\Redis;

class RedisManagerCli extends \Illuminate\Redis\RedisManager
{

    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: 'default';
        $pid_name =getmypid()."_".$name;

        if (isset($this->connections[$pid_name])) {
            return $this->connections[$pid_name];
        }

        return $this->connections[$pid_name] = $this->resolve($name);
    }

}