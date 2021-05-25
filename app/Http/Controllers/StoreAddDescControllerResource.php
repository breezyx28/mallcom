<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\AdditionalDescriptionsRequest;
use App\Http\Requests\UpdateAdditionalDescriptionRequest;
use App\Models\AdditionalDescription;
use Illuminate\Http\Request;

class StoreAddDescControllerResource extends Controller
{
    public function products()
    {
        return \App\Models\StoreProduct::where('user_id', auth()->user()->id)->pluck('product_id')->toArray();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $all = AdditionalDescription::with('product')->whereIn('product_id', $this->products)->get();
        return Resp::Success('تم', $all);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdditionalDescriptionsRequest $request)
    {
        $validate = (object) $request->validated();

        if (!in_array($validate->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        $addDesc =  new AdditionalDescription();

        foreach ($validate as $key => $value) {
            $addDesc->$key = $value;
        }

        try {
            $addDesc->save();
            return Resp::Success('تم إنشاء المواصفات بنجاح', $addDesc);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('حدث خطأ ما', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AdditionalDescription  $additionalDescription
     * @return \Illuminate\Http\Response
     */
    public function show(AdditionalDescription $additionalDescription)
    {
        if (!in_array($additionalDescription->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        return Resp::Success('تم', $additionalDescription);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AdditionalDescription  $additionalDescription
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdditionalDescriptionRequest $request, AdditionalDescription $additionalDescription)
    {
        $validate = $request->validated();

        if (!in_array($additionalDescription->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        foreach ($validate as $key => $value) {
            $additionalDescription->$key = $value;
        }

        try {
            $additionalDescription->save();
            return Resp::Success('تم التحديث بنجاح', $additionalDescription);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AdditionalDescription  $additionalDescription
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdditionalDescription $additionalDescription)
    {
        if (!in_array($additionalDescription->product_id, $this->products)) {
            return Resp::Error('لا تملك هذا المنتج', null);
        }

        $additionalDescription->delete();

        return Resp::Success('تم الحذف', $additionalDescription);
    }
}
