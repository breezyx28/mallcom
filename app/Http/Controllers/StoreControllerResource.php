<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\StoresRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stores = \App\Models\Store::with('user')->get();
        return Resp::Success('تم', $stores);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoresRequest $request)
    {
        $validate = (object) $request->validated();

        $store = new \App\Models\Store();

        foreach ($validate as $key => $value) {
            $store->$key = $value;
        }

        $store->thumbnail = Str::of($request->file('thumbnail')->storePublicly('Stores'));

        try {
            $store->save();
            return Resp::Success('تم بنجاح', $store);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $Store
     * @return \Illuminate\Http\Response
     */
    public function show(Store $Store)
    {
        return Resp::Success('تم بنجاح', $Store);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $Store
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStoreRequest $request, Store $Store)
    {
        $validate = (object) $request->validated();

        $store = new \App\Models\Store();

        foreach ($validate as $key => $value) {
            $store->$key = $value;
        }

        $store->thumbnail = Str::of($request->file('thumbnail')->storePublicly('Stores'));

        try {
            $store->save();
            return Resp::Success('تم بنجاح', $store);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $Store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $Store)
    {
        try {
            $Store->delete();
        } catch (\Exception $e) {

            return Resp::Success('تم الحذف بنجاح', $e);
        }
    }
}
