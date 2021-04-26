<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use Illuminate\Http\Request;
use App\Helper\ResponseMessage as Resp;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\updateUsersRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginController extends Controller
{

    public function Login(Request $request)
    {

        $credintials = [
            'phone' => $request->phone,
            'password' => $request->password,
        ];

        $token = null;

        if (!$token = JWTAuth::attempt($credintials)) {
            return Resp::Error('خطأ في كلمة السر او رقم الهاتف');
        }

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'message' => 'تم بنجاح',
            'data' => $user,
            'token' => $token,
        ], 200);
    }

    public function profile()
    {
        return Resp::Success('تم', auth()->user());
    }

    public function updateProfile(updateUsersRequest $request)
    {
        $validate = (object) $request->validated();

        $auth = auth()->user();

        $user = \App\Models\User::find($auth->id);

        foreach ($validate as $key => $value) {

            if ($validate->$key == 'thumbnail') {
                // $user->thumbnail = null;
                $user->thumbnail = Str::of($request->file('thumbnail')->storePublicly('Profile'));
            }
            if ($validate->$key == 'password') {
                $user->password = null;
            }

            if ($validate->$key == 'birthDate') {

                $user->birthDate = date('Y-m-d', strtotime($validate->birthDate));
            }

            $user->$key = $value;
        }


        try {
            $user->save();

            if ($user->activity == 0) {
                event(new NotificationEvent($user->id, 'deactivate'));
            }

            return Resp::Success('تم تحديث البيانات بنجاح', $user);
        } catch (\Exception $e) {
            return Resp::Error('حدث خطأ ما', $e);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = auth()->user();

        $validated = (object) $request->validated();

        if (!Hash::check($validated->oldPassword, $user->password)) {
            return Resp::Error('كلمة السر القديمة غير صحيحة');
        }

        $newPassword = Hash::make($validated->newPassword);

        try {

            \App\Models\User::find($user->id)->update(['password' => $newPassword]);

            return Resp::Success('تم تغيير كلمة السر بنجاح', "new pasword is : $validated->newPassword");
        } catch (\Exception $e) {
            return Resp::Error('لم يتم تعين كلمة السر', $e->getMessage());
        }
    }

    // logout process
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return Resp::Success('تم تسجسل الخروج بنجاح');
        } catch (JWTException $exception) {
            return Resp::Error('لا يمكن إجراء عملية تسجيل الخروج الآن ...', $exception);
        }
    }

    protected function createNewToken($token)
    {

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 3600,
            'user' => auth()->user()
        ]);
    }
}
