<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use Illuminate\Http\Request;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\UsersRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(UsersRequest $request)
    {
        $validate = (object) $request->validated();

        $user = new \App\Models\User();

        foreach ($validate as $key => $value) {

            if ($validate->$key == 'thumbnail') {
                $user->thumbnail = null;
            }
            if ($validate->$key == 'password') {
                $user->password = null;
            }

            $user->$key = $value;
        }

        $user->password = Hash::make($request->password);
        $user->thumbnail = Str::of($request->file('thumbnail')->store('public/Profile'))->substr(7);
        $user->role_id = 3;

        try {
            $user->save();

            event(new NotificationEvent($user->id, 'welcome'));
            event(new NotificationEvent($user->id, 'verify'));

            return Resp::Success('تم إنشاء مستخدم بنجاح', $user);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e);
        }
    }
}
