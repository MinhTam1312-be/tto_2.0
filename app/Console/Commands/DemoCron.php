<?php

namespace App\Console\Commands;

use App\Mail\ReminderMail;
use App\Models\Activity_History;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Reminder;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {

        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->format('l'); // Lấy tên thứ hiện tại
        $time = $now->format('H:i'); // Chỉ lấy giờ và phút

        $reminders = \App\Models\Reminder::where('day_of_week', $dayOfWeek)
            ->whereTime('time', '=', $time) // So sánh giờ và phút
            ->where('del_flag', true)
            ->get();

        foreach ($reminders as $reminder) {
            $user = $reminder->enrollment->user;
            $course = $reminder->enrollment->module->course;
            $fullname = $user->fullname;
            $email = $user->email;

            Mail::to($email)->send(new \App\Mail\ReminderMail($course, $fullname));
            Log::info('Reminder email sent to: ' . $email);
        }

        return 0;
    }
}
