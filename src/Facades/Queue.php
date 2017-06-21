<?php

namespace webSocket\Facades;

use Illuminate\Support\Facades\Facade;

class Queue extends Facade
{
    protected static function getFacadeAccessor() {
        return \webSocket\Contracts\Queue::class;
    }
}