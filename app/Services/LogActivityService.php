<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivityService
{
    public static function log($action, $discription, $status = 'success')
    {
        $user = Auth::user(); // Lấy thông tin user đang đăng nhập
        ActivityLog::create([
            'fullname' => $user ? $user->fullname : 'Guest',
            'role' => $user ? $user->role : 'guest',
            'action' => $action,
            'discription' => $discription,
            'status' => $status,
        ]);
    }
}
