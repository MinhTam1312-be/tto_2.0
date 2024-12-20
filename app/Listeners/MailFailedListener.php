<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;

class MailFailedListener
{
    public function handle(MessageFailed $event)
    {
        // Lấy thông tin email và token từ sự kiện
        $email = $event->message->getTo()[0][0]; // Địa chỉ email
        $token = ''; // Bạn có thể lấy token từ dữ liệu trong cơ sở dữ liệu nếu cần

        // Ghi log lỗi
        Log::error('Gửi email thất bại tới: ' . $email);

        // Lấy mã token từ cơ sở dữ liệu
        $resetToken = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($resetToken) {
            // Gửi lại email chứa mã xác thực
            Mail::to($email)->send(new ResetPasswordMail($resetToken->token));
            Log::info('Gửi lại email tới: ' . $email);
        }
    }
}
