<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Http\Requests\updateUsersRequest;
use App\Http\Requests\UsersRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserControllerResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all = \App\Models\User::all();

        return Resp::Success('تم بنجاح', $all);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UsersRequest $request)
    {
        $validate = (object) $request->validated();

        $user = new \App\Models\User();

        foreach ($validate as $key => $value) {

            if ($validate->$key == 'thumbnail') {
                $user->thumbnail = null;
            }
            if ($validate->$key == 'password') {
                $user->password = Hash::make($validate->$key);
            }

            $user->$key = $value;
        }

        $user->thumbnail = Str::of($request->file('thumbnail')->store('public/Profile'))->substr(7);

        $user->role_id = 3;

        try {
            $user->save();
            return Resp::Success('تم إنشاء مستخدم بنجاح', $user);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $User
     * @return \Illuminate\Http\Response
     */
    public function show(User $User)
    {
        return Resp::Success('تم بنجاح', $User);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $User
     * @return \Illuminate\Http\Response
     */
    public function update(AdminUpdateUserRequest $request, User $User)
    {
        $validate = (object) $request->validated();

        foreach ($validate as $key => $value) {
            $User->$key = $value;
        }

        try {
            $User->save();
            return Resp::Success('تم تحديث البيانات بنجاح', $User);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $User
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $User)
    {
        try {
            $User->delete();
            return Resp::Success('تم الحذف', $User);
        } catch (\Throwable $th) {
            return Resp::Error('حدث خطأ ما', $th);
        }
    }
}
