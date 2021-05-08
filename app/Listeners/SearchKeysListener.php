<?php

namespace App\Listeners;

use App\Helper\ResponseMessage as Resp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SearchKeysListener
{

    public function handle($event)
    {
        $searchKey = new \App\Models\SearchKeys();

        $searchKey->key_word = $event->key;

        try {
            $searchKey->save();
        } catch (\Throwable $th) {
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }
}
