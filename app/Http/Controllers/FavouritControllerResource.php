<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\FavouritsRequest;
use App\Models\Favourit;
use Illuminate\Http\Request;

class FavouritControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fav = \App\Models\Favourit::with('product')->where('user_id', auth()->user()->id)->get();
        return Resp::Success('تم بنجاح', $fav);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FavouritsRequest $request)
    {
        $validate = (object) $request->validated();

        $user = auth()->user()->id;

        $fav = new \App\Models\Favourit();

        $fav->product_id = $validate->product_id;
        $fav->user_id = $user;

        try {
            $fav->save();
            return Resp::Success('تم بنجاح', $fav);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Favourit  $Favourit
     * @return \Illuminate\Http\Response
     */
    public function show(Favourit $Favourit)
    {
        $fav = $Favourit->where('user_id', auth()->user()->id, 'id', $Favourit->id)->get();
        return Resp::Success('تم بنجاح', $fav);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Favourit  $Favourit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Favourit $Favourit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Favourit  $Favourit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Favourit $Favourit)
    {
        $fav = $Favourit->where(['user_id' => auth()->user()->id, 'id' => $Favourit->id])->delete();

        return Resp::Success('تم الحذف بنجاح', $Favourit);
    }
}
