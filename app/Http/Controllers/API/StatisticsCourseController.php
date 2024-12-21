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
    public function getTotalCourseApprovedUnapprovedLecturer()
    {
        try {
            $totalCourse = Course::count();
            $totalCourseApproved = Course::where('status_course', 'active')->count();
            $totalCourseUnapproved = Course::where('status_course', 'inactive')->count();
            $totalCourseLecturer = User::where('role', 'instructor')->count();
            return response()->json([
                'status' => 'success',
                'totalCourse' => $totalCourse,
                'totalCourseApproved' => $totalCourseApproved,
                'totalCourseUnapproved' => $totalCourseUnapproved,
                'totalCourseLecturer' => $totalCourseLecturer,
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
