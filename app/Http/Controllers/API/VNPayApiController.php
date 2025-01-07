<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\payMent;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\VNPayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class VNPayApiController extends Controller
{

    public function vnpayReturn(Request $request)
    {
        // dd($request->all());
        $vnp_HashSecret = env('VNP_HASHSECRET'); // Chuỗi bí mật

        $course_id = $request->get('course_id'); // Lấy course_id
        $course = Course::find($course_id);
        $user_id = $request->get('user_id'); // Lấy user_id

        $vnp_SecureHash = $request->get('vnp_SecureHash'); // Lấy mã hash từ phản hồi của VNPay
        $vnp_Amount = $request->get('vnp_Amount');
        $inputData = $request->except('vnp_SecureHash', 'user_id', 'course_id');
        ksort($inputData);

        // Tạo chuỗi dữ liệu hash
        $hashdata = http_build_query($inputData);

        // Tạo mã hash
        $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            $vnp_Amount100 = $vnp_Amount / 100;

            // Kiểm tra Enrollment hiện có
            $checkEnrollment = Enrollment::where('course_id', $course->id)
                ->where('user_id', $user_id)
                ->first();
            $user = User::find($user_id);
            switch ($request->vnp_ResponseCode) {
                case '00': // Giao dịch thành công
                    if (!$checkEnrollment) {
                        // Nếu chưa có Enrollment, tạo Enrollment mới
                        $checkEnrollment = Enrollment::create([
                            'user_id' => $user_id,
                            'course_id' => $course->id,
                            'status_course' => 'in_progress',
                            'enroll' => true,
                            'del_flag' => true,
                        ]);
                    }
                    $checkEnrollment->update(['enroll' => true]);
                    // Xử lý Transaction
                    $transaction = Transaction::updateOrCreate(
                        ['enrollment_id' => $checkEnrollment->id],
                        [
                            'amount' => $vnp_Amount100,
                            'payment_method' => 'VnPay',
                            'status' => 'completed',
                            'payment_discription' => 'Thanh toán khóa học: ' . $course->name_course,
                            'user_id' => $user_id,
                            'del_flag' => true,
                            'updated_at' => now(),
                        ]
                    );

                    // Gửi email thông báo

                    if ($user) {
                        Mail::to($user->email)->send(
                            new payMent($user->fullname, $course->name_course, $vnp_Amount100, now())
                        );
                    }

                    // Chuẩn bị dữ liệu cho session
                    $data = [
                        'fullname' => $user->fullname,
                        'nameCourse' => $course->name_course,
                        'priceCourse' => $vnp_Amount100,
                        'slugCourse' => $course->slug_course,
                        'status' => 'success',
                        'message' => 'Thanh toán thành công',
                    ];
                    session(['paymentData' => $data]);

                    return redirect('/payment');
                    break;

                case '24': // Khách hàng đã hủy giao dịch
                    $status = 'canceled';
                    break;

                case '99': // Lỗi không xác định
                default:
                    $status = 'failed';
                    break;
            }

            // Xử lý cho các trạng thái còn lại (canceled, failed)
            if (!$checkEnrollment) {
                $checkEnrollment = Enrollment::create([
                    'user_id' => $user_id,
                    'course_id' => $course->id,
                    'status_course' => 'in_progress',
                    'enroll' => true,
                    'del_flag' => true,
                ]);
            }

            Transaction::updateOrCreate(
                ['enrollment_id' => $checkEnrollment->id],
                [
                    'amount' => $vnp_Amount100,
                    'payment_method' => 'VnPay',
                    'status' => $status,
                    'payment_discription' => 'Thanh toán khóa học: ' . $course->name_course,
                    'user_id' => $user_id,
                    'del_flag' => true,
                    'updated_at' => now(),
                ]
            );

            $statusMessage = $this->getResponseMessage($request->vnp_ResponseCode);
            session(['paymentData' => [
                'fullname' => $user->fullname,
                'nameCourse' => $course->name_course,
                'priceCourse' => $vnp_Amount100,
                'status' => $status,
                'message' => $statusMessage,
            ]]);

            return redirect('/payment');
        } else {
            // Mã hash không hợp lệ
            return response()->json(['message' => 'Thanh toán không hợp lệ'], 404);
        }
    }
    private function getResponseMessage($code)
    {
        $messages = [
            '07' => 'Giao dịch bị nghi ngờ, liên hệ ngân hàng để kiểm tra.',
            '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking.',
            '10' => 'Xác thực thông tin sai quá 3 lần.',
            '11' => 'Hết hạn chờ thanh toán, vui lòng thử lại.',
            '12' => 'Thẻ/Tài khoản bị khóa.',
            '13' => 'Sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Khách hàng đã hủy giao dịch.',
            '51' => 'Tài khoản không đủ số dư.',
            '65' => 'Vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định, vui lòng thử lại.',
        ];

        return $messages[$code] ?? 'Lỗi không xác định.';
    }
    public function getVNPay(Request $request, $course_id, $course_price)
    {
        // $user_id = "01JFJAD8RQZ6KAEPP7JWP63Z44";
        $user_id = auth('api')->user()->id;
        $vnp_Returnurll = route('vnpay.return', ['course_id' => $course_id, 'user_id' => $user_id]);
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = "https://www.tto.sh/home";
        $vnp_TmnCode = env('VNP_TMNCODE'); //Mã website tại VNPAY 
        $vnp_HashSecret = env('VNP_HASHSECRET'); //Chuỗi bí mật

        $vnp_TxnRef = $vnp_TxnRef = 'ORD' . Carbon::now()->format('YmdHis') . rand(1000, 9999); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = 'Thanh toán khóa học';
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $course_price * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = request()->ip();
        $vnp_ExpireDate = Carbon::now()->addMinutes(5)->format('YmdHis');
        $vnp_SecureHash = $request->get('vnp_SecureHash');
        // $vnp_BankCode = '';
        //Billing

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
            "vnp_ReturnUrl" => $vnp_Returnurll
            // "vnp_Bill_Mobile" => $vnp_Bill_Mobile,
            // "vnp_Bill_Email" => $vnp_Bill_Email,
            // "vnp_Bill_FirstName" => $vnp_Bill_FirstName,
            // "vnp_Bill_LastName" => $vnp_Bill_LastName,
            // "vnp_Bill_Address" => $vnp_Bill_Address,
            // "vnp_Bill_City" => $vnp_Bill_City,
            // "vnp_Bill_Country" => $vnp_Bill_Country,
            // "vnp_Inv_Phone" => $vnp_Inv_Phone,
            // "vnp_Inv_Email" => $vnp_Inv_Email,
            // "vnp_Inv_Customer" => $vnp_Inv_Customer,
            // "vnp_Inv_Address" => $vnp_Inv_Address,
            // "vnp_Inv_Company" => $vnp_Inv_Company,
            // "vnp_Inv_Taxcode" => $vnp_Inv_Taxcode,
            // "vnp_Inv_Type" => $vnp_Inv_Type
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        );

        if (isset($request->redirect)) {
            header('Location: ' . $vnp_Url);
            die();
            // dd($query);
        } else {
            echo json_encode($returnData);
        }
    }
    public function momoReturn()
    {
        // Handle the IPN callback
        // dd($_GET); 
        // $secureHash = hash_hmac('sha512', $hashdata, $serectkey);
        if (isset($_GET["partnerCode"])) {
            $course_id = $_GET['course_id'];
            $course = Course::find($course_id);
            $user_id = $_GET['user_id'];
            // $orderId = $_GET["orderId"];
            $message = $_GET["message"];
            $transId = $_GET["transId"];
            $orderInfo = $_GET["orderInfo"] . $course->name_course;
            $amount = $_GET["amount"];
            $resultCode = $_GET["resultCode"];
            $responseTime = $_GET["responseTime"];
            $requestId = $_GET["requestId"];
            $payType = $_GET["payType"];
            $orderType = $_GET["orderType"];
            $extraData = $_GET["extraData"];
            if ($resultCode == 0) {
                $module_id = Module::where('course_id', $course_id)->pluck('id')->first();
                // Create the Enrollment entry
                $existingEnrollment = Enrollment::create([
                    'user_id' => $user_id, // Use user_id from the authenticated user
                    'module_id' => $module_id,
                    'status_course' => 'in_progress', // Default value for course status
                    'enroll' => 1, // Mark as enrolled
                    'del_flag' => true
                ]);
                // Create the Transaction entry
                Transaction::create([

                    'amount' => $amount, // VNPay returns amount * 100, divide by 100 for actual amount
                    'payment_method' => 'Momo',
                    'status' => 'completed',
                    'payment_discription' => 'Thanh toán khóa học' . $course->name_course,
                    'enrollment_id' => $existingEnrollment->id, // Use the newly created enrollment ID
                    'user_id' => $user_id, // User ID
                    'del_flag' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                return  redirect(env('VNP_RETURNURL'));
            } else if ($resultCode == 1003) {
                $module_id = Module::where('course_id', $course_id)->pluck('id')->first();
                // Create the Enrollment entry
                $existingEnrollment = Enrollment::create([
                    'user_id' => $user_id, // Use user_id from the authenticated user
                    'module_id' => $module_id,
                    'status_course' => 'in_progress', // Default value for course status
                    'enroll' => 0, // Mark as enrolled
                    'del_flag' => true
                ]);
                // Create the Transaction entry
                Transaction::create([

                    'amount' => $amount, // VNPay returns amount * 100, divide by 100 for actual amount
                    'payment_method' => 'Momo',
                    'status' => 'canceled',
                    'payment_discription' => 'Thanh toán khóa học' . $course->name_course,
                    'enrollment_id' => $existingEnrollment->id, // Use the newly created enrollment ID
                    'user_id' => $user_id, // User ID
                    'del_flag' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                return  redirect(env('VNP_RETURNURL'));
            } else {
                $module_id = Module::where('course_id', $course_id)->pluck('id')->first();
                // Create the Enrollment entry
                $existingEnrollment = Enrollment::create([
                    'user_id' => $user_id, // Use user_id from the authenticated user
                    'module_id' => $module_id,
                    'status_course' => 'in_progress', // Default value for course status
                    'enroll' => 0, // Mark as enrolled
                ]);
                // Create the Transaction entry
                Transaction::create([

                    'amount' => $amount, // VNPay returns amount * 100, divide by 100 for actual amount
                    'payment_method' => 'Momo',
                    'status' => 'failed',
                    'payment_discription' => 'Thanh toán khóa học' . $course->name_course,
                    'enrollment_id' => $existingEnrollment->id, // Use the newly created enrollment ID
                    'user_id' => $user_id, // User ID
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                return  redirect(env('VNP_RETURNURL'));
            }
        }
    }
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    public function getMomo(Request $request, $course_id, $course_price)
    {
        $user_id = auth('api')->user()->id;
        // $user_id = "01JFJAD8RQZ6KAEPP7JWP63Z44";
        if (!$user_id) {
            return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
        }
        $endpoint = env('endpoint');
        $partnerCode =  env('partnerCode');
        $accessKey = env('accessKey');
        $secretKey = env('secretKey');
        $orderInfo = "Thanh toán qua MoMo";
        $amount = 10000;
        $orderId = 'ORD' . Carbon::now()->format('YmdHis') . rand(1000, 9999);
        $redirectUrl = route('momo.return', ['course_id' => $course_id, 'user_id' => $user_id]);
        $ipnUrl = route('momo.return', ['course_id' => $course_id, 'user_id' => $user_id]);
        $extraData = "null";


        if (empty($request->payUrl)) {
            $partnerCode = $partnerCode;
            $accessKey = $accessKey;
            $serectkey = $secretKey;
            $orderId = $orderId; // Mã đơn hàng
            $orderInfo = $orderInfo;
            $amount = $amount;
            $ipnUrl = $ipnUrl;
            $redirectUrl = $redirectUrl;
            $extraData = $extraData;

            $requestId = time() . "";
            $requestType = "payWithATM";
            // $requestType = "captureWallet";
            // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
            //before sign HMAC SHA256 signature
            $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;

            $signature = hash_hmac("sha256", $rawHash, $serectkey);
            $data = array(
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                "storeId" => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            );
            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);  // decode json
            // echo dd($jsonResult);
            // return redirect($jsonResult['payUrl']);
            return response()->json([
                'data' => $jsonResult['payUrl']
            ], 200);
            // echo dd($jsonResult['payUrl']);
        } else {
            return response()->json([
                'messenge' => 'Không nhận được nút post'
            ], 200);
        }
    }
}
