<?php

namespace App\Http\Controllers;

use App\Events\InvoiceEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\OrdersRequest;
use App\Models\Invoice;
use App\Models\OrdersNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{

    public function myOrders(Request $request)
    {
        $validate = $request->validate([
            'status' => ['required', 'string|max:191', Rule::in(['accepted', 'rejected', 'delivered'])]
        ]);

        $orders = \App\Models\Order::with('product', 'state', 'orderNumber')
            ->where(['user_id', $this->user->id, 'status' => $validate['status']])
            ->get();

        return Resp::Success('تم', $orders);
    }

    public function getMyOrde(Request $request)
    {

        $validate = (object) $request->validate([
            'orderNumber' => 'string|max:191'
        ]);

        try {

            $orders = \App\Models\Order::where('orderNumber', $validate->orderNumber)->get();

            return Resp::Success('ok', $orders);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }
}
