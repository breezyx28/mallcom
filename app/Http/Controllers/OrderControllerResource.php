<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Models\Order;
use Illuminate\Http\Request;
use UpdateOrderRequest;

class OrderControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::with('user', 'product', 'orderNumber', 'state')->get();
        return Resp::Success('تم', $orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $Order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $Order)
    {
        $orders = $Order::with('user', 'product', 'orderNumber', 'state')->get();
        return Resp::Success('تم', $orders);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $Order
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrderRequest $request, Order $Order)
    {
        $validate = (object) $request->validated();

        $Order->status = $validate->status;

        try {
            $Order->save();
            return Resp::Success('تم التحديث بنجاح', $Order);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $Order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $Order)
    {
        //
    }
}
