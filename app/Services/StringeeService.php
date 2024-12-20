<?php

namespace App\Services;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class StringeeService
{
    protected $client;
    protected $projectId;
    protected $secretKey;

    public function __construct()
    {
        // Đảm bảo base_uri là URL cơ bản và endpoint phải được thêm vào trong mỗi yêu cầu
        $this->client = new Client(['base_uri' => 'https://api.stringeex.com/v1/sms/send']);
        $this->projectId = env('STRINGEE_PROJECT_ID');
        $this->secretKey = env('STRINGEE_SECRET_KEY');
    }

    // Tạo JWT token cho X-STRINGEE-AUTH
    protected function generateJwtToken()
    {
        $payload = [
            'jti' => uniqid(),
            'iss' => $this->projectId,
            'exp' => time() + 3600, // Token hết hạn trong 1 giờ
            'rest_api' => true      // Thêm quyền truy cập vào REST API
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // Gửi mã xác nhận SMS
    public function sendSms($phoneNumber, $message)
    {
        // Tạo token từ phương thức generateJwtToken
        $token = $this->generateJwtToken();
        $response = $this->client->post('sms/send', [  // Cập nhật endpoint chính xác là /send
            'headers' => [
                'X-STRINGEE-AUTH' => $token,  // Sử dụng token đã tạo
            ],
            'json' => [
                'to' => $phoneNumber,
                'content' => $message,
                'senderId' => $this->projectId,
            ],
        ]);
        
        // Kiểm tra mã trạng thái HTTP
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Không thể gửi SMS. Lỗi: ' . $response->getBody()->getContents());
        }

        // In ra toàn bộ nội dung phản hồi để kiểm tra
        $responseBody = $response->getBody()->getContents();
        error_log('Response Body: ' . $responseBody);  // In nội dung phản hồi ra log

        // Kiểm tra xem phản hồi có phải JSON hợp lệ không
        $decodedResponse = json_decode($responseBody);

        // Kiểm tra lỗi JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Lỗi khi giải mã JSON: ' . json_last_error_msg() . '. Phản hồi nhận được: ' . $responseBody);
        }

        return $decodedResponse;
    }
}

