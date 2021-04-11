<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\ProductSizesRequest;
use App\Models\ProductSizes;
use Illuminate\Http\Request;

class ProductSizesControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(ProductSizesRequest $request)
    {
        $validate = (object) $request->validated();

        $prodSizes = new \App\Models\ProductSizes();

        foreach ($validate as $key => $value) {
            // if ($validate->sizes_array) {
            //     $validate->sizes_array = json_encode($validate->sizes_array);
            // }
            $prodSizes->$key = $value;
        }

        try {
            $prodSizes->save();
            return Resp::Success('تم', $prodSizes);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductSizes  $productSizes
     * @return \Illuminate\Http\Response
     */
    public function show(ProductSizes $productSizes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductSizes  $productSizes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductSizes $productSizes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductSizes  $productSizes
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductSizes $productSizes)
    {
        //
    }
}
