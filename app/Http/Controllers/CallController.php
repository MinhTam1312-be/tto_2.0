<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StringeeService;
use Illuminate\Support\Facades\Http;


class CallController extends Controller
{
    protected $stringeeService;

    public function __construct(StringeeService $stringeeService)
    {
        $this->stringeeService = $stringeeService;
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
        ]);

        $phoneNumber = $request->input('phone_number');
        $verificationCode = rand(100000, 999999); // Tạo mã xác nhận ngẫu nhiên

        try {
            $message = "Mã xác nhận của bạn là: $verificationCode";
            $this->stringeeService->sendSms($phoneNumber, $message);

            return back()->with('success', 'Đã gửi mã xác nhận thành công!',);
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi gửi SMS: ' . $e->getMessage());
        }
    }
    public function sendVerification()
    {
        $APIKey = "5EA283F956B2ADD2450191DEE1C674";
        $SecretKey = "066C387792B133765CAEDDC504AF7A";
        $YourPhone = "+84349121202";
        $brandname = 'Baotrixemay';
        $Code = 'Cam on quy khach da su dung dich vu cua chung toi. Chuc quy khach mot ngay tot lanh!';

        // URL API với dấu '&' giữa các tham số
        $otp = "https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_get?SmsType=2&ApiKey=$APIKey&SecretKey=$SecretKey&Phone=$YourPhone&Content=$Code&Brandname=$brandname";
        // $otp = "https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_get?SmsType=2&ApiKey=5EA283F956B2ADD2450191DEE1C674&SecretKey=066C387792B133765CAEDDC504AF7A&Phone=+84349121202&Content=123456&Brandname=Baotrixemay";

        $curl = curl_init($otp);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        $obj = json_decode($result, true);
        if ($obj['CodeResult'] == 100) {
            echo "<br>";
            echo "CodeResult:" . $obj['CodeResult'];
            echo "<br>";
            echo "SMSID:" . $obj['SMSID'];
            echo "<br>";
        } else {
            echo "ErrorMessage:" . $obj['ErrorMessage'];
        }
    }
    public function sendSms(Request $request)
    {
        // $APIKey = "5EA283F956B2ADD2450191DEE1C674";
        // $SecretKey = "066C387792B133765CAEDDC504AF7A";
        $APIKey = "450C1598B800BBB088DA06D9874E9D";
        $SecretKey = "BF3B92F8CFFF6DF57739C268BB5E27";
        $brandname = 'Baotrixemay';
        $Code = 'Cam on quy khach da su dung dich vu cua chung toi. Chuc quy khach mot ngay tot lanh!';
        $YourPhone = $request->input('phone');

        // Gọi API
        $response = Http::get("https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_get", [
            'SmsType' => 2,
            'ApiKey' => $APIKey,
            'SecretKey' => $SecretKey,
            'Phone' => $YourPhone,
            'Content' => $Code,
            'Brandname' => $brandname,
        ]);

        // Xử lý phản hồi từ API
        if ($response->successful()) {
            return back()->with('success', 'SMS đã được gửi thành công!');
        } else {
            return back()->with('error', 'Gửi SMS thất bại, vui lòng thử lại.');
        }
    }

    public function callback(Request $request)
    {
        // Xử lý kết quả callback từ eSMS
        $data = $request->all();

        // Lưu kết quả vào cơ sở dữ liệu hoặc thực hiện các hành động khác
        // Ví dụ:
        if ($data['CodeResult'] == 100) {
            // Success: Làm gì đó
        } else {
            // Error: Làm gì đó
        }

        return response()->json(['status' => 'OK']);
    }
}
