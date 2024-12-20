<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class MailSentListener
{
    public function handle(MessageSent $event)
    {
        // Lấy địa chỉ email từ sự kiện
        $email = $event->message->getTo()[0][0]; // Lấy địa chỉ email
        $token = ''; // Mặc định là rỗng

        // Lấy mã token từ nội dung email
        preg_match('/Mã xác thực: (\w+)/', $event->message->getBody(), $matches);
        if (isset($matches[1])) {
            $token = $matches[1];
        }

        // Lưu vào bảng password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );
    }
}