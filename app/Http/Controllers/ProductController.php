<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Models\Product;
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

        $orders = DB::table('orders');
        $products = new \App\Models\Product();

        // // products of this week
        // $prods = $orders
        //     ->whereDate('created_at', '>', Carbon::now()->subDays(7))
        //     ->get('product_id');

        // // all products id
        // $plk = $prods->pluck('product_id');

        // // loop through
        // $arr = [];
        // foreach ($plk as $key => $value) {
        //     array_push($arr[$value], $prods->where('product_id', $value)->count());
        // }

        $top = [
            'sells' => function () use ($orders, $validate) {
                return $orders
                    ->select('product_id')->groupBy('product_id')
                    ->take(isset($validate->limit) ? $validate->limit : 10)
                    ->pluck('product_id');
            },
            'rate' => $products->whereHas('rate', function ($q) use ($validate) {
                $q->select('rate')->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('rate');
            })->get(),
            'discount' => (function () use ($products, $validate) {
                return $products->where('status', 1)->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('price', 'desc')->get();
            }),
        ][$validate->top];

        return Resp::Success('تم', $top);
    }

    public function todayProducts(Request $request)
    {
        $validate = (object) $request->validate([
            'column' => 'string|max:191',
            'exp' => 'string|min:0,max:5',
            'value' => 'string|max:191',
            'limit' => 'required|integer'
        ]);

        if (isset($validate->column) || isset($validate->value)) {

            $switch = [
                '0' => '=',
                '1' => '>',
                '2' => '<',
                '3' => '>=',
                '4' => '<=',
                '5' => 'like'
            ][$validate->exp];

            $data = \App\Models\Product::with('category', 'store')->where($validate->column, $switch, $validate->value)->limit(isset($validate->limit) ? $validate->limit : 10)->orderBy('updated_at', 'desc')->get();
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

        if (isset($validate->category) && !isset($validate->subCategory)) {
            $filtered = \App\Models\Product::whereHas('category', function ($query) use ($validate) {
                $query->where('name', $validate->category);
            })->with('category', 'store.store', 'rate', 'product_photos', 'additional_description', 'product_sizes')->get();

            return Resp::Success('تم', $filtered);
        }

        if (isset($validate->subCategory) && !isset($validate->category)) {
            $filtered = \App\Models\Product::whereHas('category', function ($query) use ($validate) {
                $query->where('subCategory', '=', $validate->subCategory);
            })->with(['category', 'store.store', 'rate', 'product_photos', 'additional_description', 'product_sizes'])->get();

            return Resp::Success('تم', $filtered);
        }

        if (isset($validate->subCategory) && isset($validate->category)) {

            try {
                //code...
                $all = \App\Models\Product::whereHas('category', function ($q) use ($validate) {
                    $q->where(['name' => $validate->category, 'subCategory' => $validate->subCategory]);
                })
                    ->get();
                $all->load('category', 'store.store', 'rate', 'product_photos', 'additional_description', 'product_sizes');
                return Resp::Success('تم', $all);
            } catch (\Throwable $th) {
                //throw $th;
                return Resp::Error('حدث خطأ ما', $th->getMessage());
            }
        }

        if (!isset($validate->subCategory) && !isset($validate->category)) {

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

    public function randomProducts()
    {
        $data = Product::inRandomOrder()->limit(3)->get();

        return Resp::Success('ok', $data);
    }

    public function suggestions()
    {
        $cat = new \App\Models\Category();
        $prod = new Product();

        // get random sub categories
        $randomCat = $cat::inRandomOrder()->limit(5)->get()->groupBy('name');

        $arr = [];
        foreach ($randomCat as $key => $value) {

            // bring products of this category Limit 100
            $catProd = $prod::whereHas('category', function ($q) use ($key) {
                $q->where('name', $key);
            })->limit(100)->get();

            //the category
            $catName = $key;
            // three images
            $threeImgs = [];
            // return Resp::Success('ok', $catProd->random(3));
            $randImgs = $catProd->isNotEmpty() ? $catProd->random(3) : [];
            foreach ($randImgs as $key => $value) {
                $threeImgs[] = $value->photo;
            }

            // important product (top rated)
            // $max = $catProd->max('rate.*.rate');
            $topProd = $catProd->sortByDesc('rate.*.rate')->values()->all();

            // convert $topProd to collection
            $col = collect($topProd)->slice(1, 3)->values();
            $arr[] = [
                'watchAll' => $catName,
                'imgs' => $threeImgs,
                'topProduct' => $col->map(function ($item, $key) {
                    return [
                        'id' => $item->id,
                        'photo' => $item->photo,
                    ];
                })
            ];
        }


        return Resp::Success('تم', $arr);
    }
}
