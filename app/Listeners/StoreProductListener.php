<?php

namespace App\Listeners;

use App\Helper\ResponseMessage as Resp;
use App\Models\Product;
use App\Models\StoreProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class StoreProductListener
{

    public function handle($event)
    {
        $storeProd = new StoreProduct();
        $product = $event->product;

        $storeProd->store_id = $event->storeID;

        DB::beginTransaction();
        try {
            $product->save();
            $storeProd->product_id = $product->id;
            $storeProd->user_id = auth()->user()->id;
            $storeProd->save();
            DB::commit();
            return Resp::Success('تم إضافة المنتج إلى المتجر', $product);
        } catch (\Exception $e) {
            DB::rollback();
            return Resp::Error('حدث خطأ', $e->getMessage());
        }
    }
}
