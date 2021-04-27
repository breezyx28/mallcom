<?php

namespace App\Http\Controllers;

use App\Events\InvoiceEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\OrdersRequest;
use App\Models\Invoice;
use App\Models\Order;
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

    public function getMyOrder(Request $request)
    {
        $validate = (object) $request->validate([
            'orderNumber' => 'string|max:191'
        ]);

        try {
            $orders = \App\Models\OrdersNumber::where('orderNumber', $validate->orderNumber)->get();

            $ord = DB::table('orders');

            $res = $ord
                ->join("products", "orders.product_id", "products.id")
                ->join("users", "orders.user_id", "users.id")
                ->select(
                    "users.id as userID",
                    "users.firstName",
                    "users.middleName",
                    "users.lastName",
                    "orders.*",
                    "products.id as productID",
                    "products.name as productName",
                    "products.price as productPrice",
                    "products.discount as productDiscount",
                    "products.addetionalPrice as productAddetionalPrice",
                )
                ->selectRaw('(products.price - (products.price * (products.discount/100))) + products.addetionalPrice as final_price')
                ->where('orders_number_id', $orders[0]->id)->get();


            $invoiceInfo = \App\Models\Invoice::where('orderNumber', $validate->orderNumber)->get();

            return Resp::Success('ok', ["orderInfo" => $res, "userInfo" => auth()->user(), "invoiceInfo" => $invoiceInfo]);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }
}
