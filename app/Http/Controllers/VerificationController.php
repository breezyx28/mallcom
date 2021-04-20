<?php

namespace App\Http\Controllers;

use App\Helper\ResponseMessage as Resp;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    private $code, $phone;
    protected const api_key = 'bWFsbGNvbS5pdDIwMjFAZ21haWwuY29tOmVMVmwjMiZURlk=';

    public function __construct($code)
    {
        $this->code = $code;
    }

    private function cleanPhone($phone)
    {
        $error = false;

        if (!strlen($phone) == 10) {
            $error = true;
        }
        if ($phone[0] == '0') {
            $phone = substr_replace($phone, "249", 0, 1);
        }
        if (!is_numeric($phone)) {
            $error = true;
        }
        if (!$error) {
            return $phone;
        } else {
            return $error;
        }
    }

    public function sendCode($phone)
    {
        $this->phone = $this->cleanPhone($phone);

        $http = Http::get('https://mazinhost.com/smsv1/sms/api', [
            'action' => 'send-sms',
            'api_key' => self::api_key,
            'to' => $this->phone,
            'from' => 'MallCom',
            'sms' => "Code Verification $this->code",
        ]);

        $resp =  $http->json();

        if (isset($resp['balance']) && $resp['balance'] < 1) {

            return Resp::Error('SMS Error', 'الرجاء التواصل مع إدارة النظام');
        }

        if ($resp['code'] != "ok") {

            return Resp::Error('SMS Error', 'خطأ في عملية ارسال رمز التأكيد');
        }

        return Resp::Success('SMS Sent Successfuly', 'تم إرسال رمز التأكيد إلى ' . $phone,);
    }

    public function verifyAccount(Request $request)
    {
        $validate = (object) $request->validate([
            'id' => 'required|integer',
            'code' => 'required|numberic'
        ]);

        try {
            //code...
            \App\Models\Verification::where(['user_id' => $validate->id, 'code' => $validate->code])->update(['verified' => 1]);
        } catch (\Throwable $th) {
            //throw $th;
            return Resp::Error('Error while verifying', $th->getMessage());
        }
    }
}
