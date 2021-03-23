<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\InvoiceEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\OrdersRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserOrderControllerResource extends Controller
{

    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = \App\Models\Order::with('product', 'state', 'orderNumber')->where('user_id', $this->user->id)->get();
        return Resp::Success('تم', $orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrdersRequest $request)
    {
        // validate inputs
        $validate = $request->validated();

        $user = auth()->user();

        // generate uuid for order
        $orderNumber = (strtotime("now")) . (@DB::table('orders_numbers')->orderBy('id', 'DESC')->first()->id + 1);
        $invoiceNumber = (strtotime("now")) . (@DB::table('invoices')->orderBy('id', 'DESC')->first()->id + 1);
        $accountNumber = $validate['account_id'];

        $ordersNumber = new \App\Models\OrdersNumber();
        $order = new \App\Models\Order();

        $ordersNumber->orderNumber = $orderNumber;

        DB::beginTransaction();

        if (!$ordersNumber->save()) {
            return Resp::Error('حدث خطأ في حفظ رقم الطلبات');
        }

        $productsIDs = [];

        foreach ($validate['orders'] as $key => $value) {

            $validate['orders'][$key]['orders_number_id'] = $ordersNumber->id;
            $validate['orders'][$key]['status'] = 'available';
            $validate['orders'][$key]['user_id'] = $user->id;

            array_push($productsIDs, $value['product_id']);
        }

        // find all products id
        $products = collect(\App\Models\Product::find($productsIDs));

        $totalPrice = $products->sum('final_price');

        try {

            $order->insert($validate['orders']);

            $data = event(new InvoiceEvent($invoiceNumber, $orderNumber, $totalPrice, $accountNumber))[0]->original;

            DB::commit();
            return Resp::Success('تم بنجاح', $data);
        } catch (\Exception $e) {
            DB::rollback();
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        if ($order->user_id == auth()->user()->id) {
            return Resp::Success('تم', $order);
        }
        return Resp::Error('لا تملك هذا الطلب');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
