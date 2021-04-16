<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function topProduct(Request $request)
    {
        $validate = (object) $request->validate([
            'top' => ['required', Rule::in(['sells', 'rate', 'discount'])],
            'limit' => 'integer'
        ]);

        $orders = new \App\Models\Order();
        $products = new \App\Models\Product();

        // products of this week
        $prods = $orders
            ->whereDate('created_at', '>', Carbon::now()->subDays(7))
            ->get('product_id');

        // all products id
        $plk = $prods->pluck('product_id');

        // loop through
        $arr = [];
        foreach ($plk as $key => $value) {
            array_push($arr[$value], $prods->where('product_id', $value)->count());
        }

        return json_encode($prods);
        $top = [
            'sells' => function () use ($orders, $validate) {
                return $orders::count('product_id')
                    ->orderBy('product_id', 'desc')
                    ->take(isset($validate->limit) ? $validate->limit : 10)
                    ->get();
            },
            'rate' => function () use ($products, $validate) {
                return $products->where('status', 1)->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('updated_at')->get();
            },
            'discount' => (function () use ($products, $validate) {
                return $products->where('status', 1)->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('price', 'desc')->get();
            }),
        ][$validate->top];

        return json_encode($orders::count('product_id'));

        return Resp::Success('تم', $top);
    }

    public function todayProducts(Request $request)
    {

        $validate = (object) $request->validate([
            'column' => 'string|max:191',
            'value' => 'string|max:191',
            'limit' => 'required|integer'
        ]);

        if (isset($validate->column) || isset($validate->value)) {
            $data = \App\Models\Product::with('category', 'store')->where($validate->column, $validate->value)->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('updated_at', 'desc')->get();
            return Resp::Success('تم', $data);
        } else {
            $data = \App\Models\Product::with('category', 'store')->where('status', 1)->limit($validate->limit)->orderBy('updated_at', 'desc')->get();
            return Resp::Success('تم', $data);
        }
    }

    public function getProducts(Request $request)
    {
        $validate = (object) $request->validate([
            'productsIDs' => 'required|array'
        ]);

        try {
            //code...
            $all = \App\Models\Product::with(['category', 'store.store', 'product_photos', 'additional_description', 'product_sizes'])->find($validate->productsIDs);
            return Resp::Success('تم', $all);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }

    public function productsWith(Request $request)
    {
        $validate = (object) $request->validate([
            'category' => 'string',
            'subCategory' => 'string',
        ]);

        if (isset($validate->category) || isset($validate->subCategory)) {

            $filtered = \App\Models\Product::with(['category' => function ($query) use ($validate) {
                $query->where('name', $validate->category)->orWhere('subCategory', $validate->subCategory);
            }, 'store.store', 'rate', 'product_photos', 'additional_description', 'product_sizes'])->get();

            return Resp::Success('تم', $filtered);
        }

        try {
            //code...
            $all = \App\Models\Product::with('category', 'store.store', 'rate', 'product_photos', 'additional_description', 'product_sizes')->get();
            return Resp::Success('تم', $all);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }
}
