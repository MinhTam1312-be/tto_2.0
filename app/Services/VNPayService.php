<?php

namespace App\Services;

use Carbon\Carbon;

class VNPayService
{
    protected $vnp_TmnCode = "5QCLADQ0"; // Mã website sandbox
    protected $vnp_HashSecret = "0BCLJJV7R4H26UD2RYJA2MK6X8FJJ1Y3"; // Chuỗi bí mật sandbox
    protected $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL môi trường sandbox
    protected $vnp_Returnurl = "http://localhost:8000/return-vnpay"; // URL trả về

    public function createPaymentUrl($course_id, $amount, $payerName)
    {
        $vnp_TxnRef = $course_id;
        $vnp_OrderInfo = 'Đăng kí khóa học ' . $course_id . ' bởi ' . $payerName;
        $vnp_Amount = $amount * 100; // Số tiền cần thanh toán (VNĐ)
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();
        $expireDate = Carbon::now()->addMinutes(2)->format('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'), // Phải theo định dạng YmdHis
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $this->vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expireDate // Định dạng YmdHis
        ];
        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
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

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        }

        return $vnp_Url;
    }

    public function validateResponse($data)
    {
        $vnp_SecureHash = $data['vnp_SecureHash'];
        unset($data['vnp_SecureHash']);
        ksort($data);
        $hashData = http_build_query($data);
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            return true;
        }
        return false;
    }
}
