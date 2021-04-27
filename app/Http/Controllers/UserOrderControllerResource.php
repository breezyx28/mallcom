<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\InvoiceEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\OrdersRequest;
use Carbon\Carbon;
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
        $orders = DB::table('orders')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->join('states', 'orders.state_id', '=', 'states.id')
            ->join('orders_numbers', 'orders.orders_number_id', '=', 'orders_numbers.id')
            ->select('orders.*', 'products.*', 'states.name as stateName', 'states.city', 'orders_numbers.orderNumber')
            ->where('orders.user_id', auth()->user()->id)
            ->get();
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

        $totals = [];
        $actualTotals = [];

        foreach ($validate['orders'] as $key => $value) {

            $validate['orders'][$key]['orders_number_id'] = $ordersNumber->id;
            $validate['orders'][$key]['status'] = 'available';
            $validate['orders'][$key]['user_id'] = $user->id;
            $validate['orders'][$key]['created_at'] = Carbon::now();
            $validate['orders'][$key]['updated_at'] = Carbon::now();

            array_push($totals, ((\App\Models\Product::find($value['product_id'])->final_price) * $value['amount']));
            array_push($actualTotals, ((\App\Models\Product::find($value['product_id'])->price) * $value['amount']));
        }

        // find all products prices using there id's
        $totalPrice = collect($totals)->sum();
        $actualTotalPrice = collect($actualTotals)->sum();

        // $totalPrice = $totals->sum('final_price');

        try {

            $order->insert($validate['orders']);

            $data = event(new InvoiceEvent($invoiceNumber, $orderNumber, $totalPrice, $accountNumber, $actualTotalPrice))[0]->original;

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
