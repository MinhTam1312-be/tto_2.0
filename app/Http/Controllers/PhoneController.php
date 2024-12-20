<?php

namespace App\Http\Controllers;

use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhoneController extends Controller
{
    // public function storePhoneNumber(Request $request)
    // {
    //     //run validation on data sent in
    //     $validatedData = $request->validate([
    //         'phonenumber' => 'required|string|max:15|unique:users,phonenumber,' . '01JBW2Y6QMX6WHNKT1E6RCMQW6',
    //     ]);

    //     // Tìm người dùng theo ID
    //     $user = User::findOrFail('01JBW2Y6QMX6WHNKT1E6RCMQW6');

    //     // Cập nhật số điện thoại
    //     $user->phonenumber = $validatedData['phonenumber'];

    //     // Lưu thay đổi vào cơ sở dữ liệu
    //     $user->save();

    //     $this->sendMessage('User registration successful!!', $request->phone_number);
    //     return back()->with(['success' => "{$request->phone_number} registered"]);
    // }
    // public function show()
    // {
    //     $users = User::all(); //query db with model
    //     return view('phone/phonenumber', compact("users")); //return view with data
    // }
    // public function sendCustomMessage(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'users' => 'required|array',
    //         'body' => 'required',
    //     ]);
    //     $recipients = $validatedData["users"];
    //     // iterate over the array of recipients and send a twilio request for each
    //     foreach ($recipients as $recipient) {
    //         $this->sendMessage($validatedData["body"], $recipient);
    //     }
    //     return back()->with(['success' => "Messages on their way!"]);
    // }
    public function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_PHONE_NUMBER");
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $recipients,
            [ 'from' => $twilio_number, 'body' => $message]
        );
    }
    public function SMS(Request $request)
    {
        // Kiểm tra số điện thoại và nội dung tin nhắn
        $request->validate([
            'phone_number' => 'required|string',
        ]);
    
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_PHONE_NUMBER'); // Số Twilio hợp lệ của bạn
    
        // Tạo mã token ngẫu nhiên (6 ký tự)
        $generatedToken = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
        $client = new Client($sid, $token);
    
        try {
            // Gửi tin nhắn với mã token
            $message = $client->messages->create(
                $request->phone_number, // Số điện thoại người nhận
                [
                    'from' => $from, // Đảm bảo đây là số Twilio hợp lệ
                    'body' => 'Mã xác thực của bạn là: ' . $generatedToken, // Nội dung tin nhắn chứa mã token
                ]
            );
    
            // Lấy Message SID của tin nhắn vừa gửi
            $messageSid = $message->sid;
    
            // Kiểm tra trạng thái tin nhắn
            $messageStatus = $this->checkMessageStatus($messageSid, $client);
    
            return view('phone/phonenumber', compact('messageStatus', 'messageSid', 'generatedToken'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function checkMessageStatus($messageSid, $client)
    {
        // Lấy tin nhắn từ Twilio bằng Message SID
        $message = $client->messages($messageSid)->fetch();

        // Trả về trạng thái của tin nhắn
        return $message->status;
    }
    // Hiển thị form gửi SMS
    public function showForm()
    {
        return view('phone/phonenumber');
    }
}
