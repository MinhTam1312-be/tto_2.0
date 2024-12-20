<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderEmail;
use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Mail\ReminderEmail;
use App\Mail\ReminderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $dayOfWeek = $now->format('l');
        $time = $now->format('H:i:s');

        $reminders = Reminder::where('day_of_week', $dayOfWeek)
            ->where('time', $time)
            ->where('del_flag', true)
            ->get();
        foreach ($reminders as $reminder) {
            $fullname = $reminder->enrollment->user->fullname;
            Mail::to($reminder->enrollment->user->email) // Giả định Enrollment có quan hệ với User
                ->send(new ReminderMail($reminder, $fullname));
            Log::info('Reminder email sent to: ' . $reminder->enrollment->user->email);
        }

        return 0;
    }
}
