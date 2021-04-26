<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function myInvoices()
    {

        $user = auth()->user();

        $invoices = \App\Models\Invoice::with('account')->where('user_id', $user->id)->get();

        return Resp::Success('done', $invoices);
    }
}
