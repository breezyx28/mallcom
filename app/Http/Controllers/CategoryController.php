<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categoryList(Request $request)
    {
        $validate = (object) $request->validate([
            'groupBy' => 'required|string'
        ], [
            'groupBy' => 'هذا الحقل متوفر'
        ]);

        $data = \App\Models\Category::all()->groupBy($validate->groupBy);

        return Resp::Success('ok', $data);
    }

    public function getCategories()
    {
    }
}
