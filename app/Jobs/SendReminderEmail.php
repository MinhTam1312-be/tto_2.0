<?php
namespace App\Jobs;

use App\Models\Reminder;
use App\Mail\ReminderMail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function handle()
    {
        // Lấy ngày và giờ hiện tại
        $currentDay = Carbon::now()->format('l'); // "Monday", "Tuesday", v.v.
        $currentTime = Carbon::now()->format('H:i:s');

        // Lấy các reminders có `day_of_week` và `time` khớp với thời gian hiện tại
        $reminders = Reminder::where('day_of_week', $currentDay)
                    ->where('time', $currentTime)
                    ->where('del_flag', true)
                    ->get();

        foreach ($reminders as $reminder) {
            $user = $reminder->enrollment->user; // Giả sử có mối quan hệ từ enrollment đến user

            // Gửi mail cho user của enrollment, truyền Reminder vào Mail
            Mail::to($user->email)->send(new ReminderMail($reminder,)); // Truyền $reminder vào constructor
        }
    }
}
