<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function myInvoices()
    {

        $user = auth()->user();

        $invoices = \App\Models\Invoice::with('account')->where('user_id', $user->id)->get();

        return Resp::Success('done', $invoices);
    }

    public function storeInvoices()
    {

        $user = auth()->user();

        // get order Model
        $order = new Order();

        // get only my store product form order model
        $myProducts = $order::whereHas('product.store', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->get();

        // $schema = [
        //     'productName' => '',
        //     'productPrice' => '',
        //     'productName' => '',
        //     'productName' => '',
        //     'productName' => '',
        //     'productName' => '',
        //     'productName' => '',
        // ];

        return Resp::Success('done', $myProducts);
    }
}
