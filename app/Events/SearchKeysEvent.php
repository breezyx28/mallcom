<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SearchKeysEvent
{
    public $key;
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct($key)
    {
        $this->$key = $key;
    }
}
