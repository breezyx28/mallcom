<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\ProductsPhotosRequest;
use App\Http\Requests\UpdateProductPhotosRequest;
use App\Models\ProductsPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreProdPhotControllerResource extends Controller
{
    private $products;
    public function __construct()
    {
        $this->products = \App\Models\StoreProduct::where('user_id', auth()->user()->id)->pluck('product_id')->toArray();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $photos = \App\Models\ProductsPhoto::whereIn('product_id', $this->products)->get();

        return Resp::Success('تم', $photos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductsPhotosRequest $request)
    {
        $prodPho = new ProductsPhoto();
        $validate = (object) $request->validated();

        if (!in_array($validate->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        $error = false;
        $images = [];

        foreach ($request['photo'] as $key => $value) {

            $prodPho->photo = Str::of($request->file('photo.' . $key)->store('public/Product'))->substr(7);

            $prodPho->product_id = $validate->product_id;

            try {
                //code...
                $prodPho->save();
                array_push($images, $request->file('photo.' . $key)->getClientOriginalName());
                $prodPho = new ProductsPhoto();
            } catch (\Throwable $th) {
                Log::info('Issue :', ['problem' => $th->getMessage()]);
                $error = true;
                return 0;
            }
        }

        if ($error) {
            return Resp::Error('حدث خطأ اثناء إضافة الصور',  $images);
        } else {
            return Resp::Success('تمت إضافة الصور بنجاح', $images);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductsPhoto  $productsPhoto
     * @return \Illuminate\Http\Response
     */
    public function show(ProductsPhoto $productsPhoto)
    {
        if (!in_array($productsPhoto->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        return Resp::Success('تم', $productsPhoto);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductsPhoto  $productsPhoto
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductPhotosRequest $request, ProductsPhoto $productsPhoto)
    {
        $validate = (object) $request->validated();

        if (!in_array($validate->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        $productsPhoto->photo = Str::of($request->file('photo')->store('public/Product'))->substr(7);

        try {
            $productsPhoto->save();
            return Resp::Success('تم بنجاح', $productsPhoto);
        } catch (\Throwable $th) {
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductsPhoto  $productsPhoto
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductsPhoto $productsPhoto)
    {
        if (!in_array($productsPhoto->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        $productsPhoto->delete();
        return Resp::Success('تم الحذف', $productsPhoto);
    }
}
