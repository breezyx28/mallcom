<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\CategoriesRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cat = \App\Models\Category::all();
        return Resp::Success('تم بنجاح', $cat);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoriesRequest $request)
    {
        $validate = (object) $request->validated();

        $cat = new \App\Models\Category();

        foreach ($validate as $key => $value) {
            $cat->$key = $value;
        }

        try {
            $cat->save();
            return Resp::Success('تم', $cat);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $Category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $Category)
    {
        return Resp::Success('تم', $Category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $Category
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, Category $Category)
    {
        $validate = (object) $request->validated();

        $cat = $Category;

        foreach ($validate as $key => $value) {
            $cat->$key = $value;
        }

        try {
            $cat->save();
            return Resp::Success('تم', $cat);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $Category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $Category)
    {
        $cat = $Category->delete();
        return Resp::Success('تم الحذف', $cat);
    }
}
