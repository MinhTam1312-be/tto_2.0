<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsCourseController extends Controller
{
    // Tổng học viên chưa hoàn thành, và đã hoàn thành, tổng khóa học, tổng người đăng ký của giảng viên (Đã sửa)
    public function statisticalProgressClient()
    {
        try {
            $user_id = auth('api')->user()->id;

            // Đếm số lượng enrollment có status_course là 'in_progress'
            $inProgressCount = Enrollment::whereHas('course', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->where('status_course', 'in_progress')
                ->where('enroll', true)
                ->count();

            // Đếm số lượng enrollment có status_course là 'completed'
            $completedCount = Enrollment::whereHas('course', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->where('status_course', 'completed')
                ->where('enroll', true)
                ->count();

            // Đếm ra số lượng khóa học của giảng viên
            $totalCourse = Course::where('user_id', $user_id)->count();

            // Đếm ra tổng người dùng đăng ký khóa học của giảng viên
            $completedCount = Enrollment::whereHas('course', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->where('enroll', true)
                ->count();

            return response()->json([
                'status' => 'success',
                'in_progress' => $inProgressCount,
                'completed' => $completedCount,
                'total_course' => $totalCourse,
                'enroll_user' => $completedCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê tổng người dùng đã đăng ký và hoàn thành khóa họchọc
    public function getTotalUserProgress()
    {
        try {
            $countUserCompleteCourse = Enrollment::where('status_course', 'completed')
                ->where('del_flag', true)
                ->where('enroll', true)
                ->count();

            $countUserInProgressCourse = Enrollment::where('status_course', 'in_progress')
                ->where('del_flag', true)
                ->where('enroll', true)
                ->count();

            $countUserFailedCourse = Enrollment::where('status_course', 'failed')
                ->where('del_flag', true)
                ->where('enroll', true)
                ->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy ra dữ liệu thành công.',
                'UserCompleteCourse' => $countUserCompleteCourse,
                'UserInProgressCourse' => $countUserInProgressCourse,
                'UserFailedCourse' => $countUserFailedCourse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê tổng người dùng đã đăng ký và hoàn thành khóa học
    public function getTotalUser($client = null)
    {
        try {
            if ($client === 'client') {
                $countUser = User::where('role', 'client')->count();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy tổng số người dùng client thành công.',
                    'total_clients' => $countUser,
                ], 200);
            } else {
                $countAdmin = User::where('role', 'admin')->count();
                $countAccountant = User::where('role', 'accountant')->count();
                $countMarketing = User::where('role', 'marketing')->count();
                $countInstructor = User::where('role', 'instructor')->count();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy tổng số người dùng các role khác thành công.',
                    'total_admins' => $countAdmin,
                    'total_accountants' => $countAccountant,
                    'total_marketing' => $countMarketing,
                    'total_instructors' => $countInstructor,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


}
