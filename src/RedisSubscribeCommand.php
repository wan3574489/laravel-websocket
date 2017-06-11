<?php

namespace webSocket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

abstract class RedisSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RedisSubscribe:Command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RedisSubscribe:Command';

    protected $channel = '';

    protected $handler = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if(!$this->channel){
            throw new \Exception("not channel!");
        }

        if(!$this->handler){
            throw new \Exception("not handler!");
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->laravel->bind(RedisSubscribe::class,$this->handler);
        $instance = $this->laravel->make(RedisSubscribe::class);

        while(true) {
            $value = Redis::brpop($this->channel, 0);

            $channel = $value[0];
            $data    = $value[1];

            $instance->reset();
            $instance->setData($channel,$data);
            $instance->handle();
        }
    }
}