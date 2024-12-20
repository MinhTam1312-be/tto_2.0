<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Cloudinary\Transformation\Transition;
use Illuminate\Http\Request;

class StatisticsAdminController extends Controller
{
    public function getTotalCourseCartNowStaffRevenue()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            // Tổng khóa họchọc
            $totalCourse = Course::count();
            // Đơn hàng hôm nay 
            $totalCourseNow = Transaction::where('status', 'completed')
                ->where('del_flag', true)
                ->whereDate('created_at', now()->toDateString()) // Điều kiện ngày hôm nay
                ->count();
            // Số nhân viên 
            $totalCourseLecturer = User::whereIn('role', ['instructor', 'accountant', 'marketing'])->count();
            // Doanh thu
            $totalRevenue = Course::whereHas('modules.enrollments', function ($query) {
                $query->where('enroll', true);
            })
                ->get()
                ->sum(function ($course) {
                    // Giá gốc của khóa học
                    $price = $course->price_course;

                    // Kiểm tra và áp dụng giảm giá (nếu có)
                    $discountAmount = isset($course->discount_price_course) && $course->discount_price_course > 0
                        ? $price * ($course->discount_price_course / 100)
                        : 0; // Không có giảm giá
    
                    $priceAfterDiscount = $price - $discountAmount;

                    // Tính thuế dựa trên giá đã giảm
                    $taxAmount = ($priceAfterDiscount * $course->tax_rate) / 100;

                    // Giá cuối cùng sau khi trừ thuế
                    return $priceAfterDiscount - $taxAmount;
                });

            // Định dạng tổng doanh thu thành VND
            $totalCourseRevenue = $totalRevenue;

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy ra dữ liệu thành công',
                'totalCourse' => $totalCourse,
                'totalCourseNow' => $totalCourseNow,
                'totalCourseLecturer' => $totalCourseLecturer,
                'totalCourseRevenue' => $totalCourseRevenue,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getTotalCourseRevenue()
    {
        try {
            // Lấy năm hiện tại
            $currentYear = Carbon::now()->year;

            // Truy vấn doanh thu theo tháng
            $monthlyRevenue = Course::with([
                'modules.enrollments' => function ($query) {
                    $query->where('enroll', true); // Chỉ lấy các enrollment có trạng thái true
                }
            ])
                ->selectRaw("EXTRACT(MONTH FROM enrollments.created_at) AS month")
                ->selectRaw("SUM(CASE 
                WHEN discount_price_course IS NOT NULL THEN discount_price_course 
                ELSE price_course 
            END) AS total_price")
                ->join('modules', 'modules.course_id', '=', 'courses.id')
                ->join('enrollments', 'enrollments.module_id', '=', 'modules.id')
                ->whereYear('enrollments.created_at', $currentYear) // Chỉ lấy dữ liệu trong năm hiện tại
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            // Định dạng doanh thu
            $formattedRevenue = $monthlyRevenue->map(function ($revenue) {
                return [
                    'month' => (int) $revenue->month, // Tháng (số nguyên)
                    'total_price' => number_format($revenue->total_price, 0, ',', '.') . ' VND' // Định dạng tiền tệ
                ];
            });
            // Trả về dữ liệu trong định dạng JSON
            return response()->json([
                'status' => 'success',
                'data' => $formattedRevenue,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unable to fetch monthly revenue',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCourseInProgressCompletedAssessmentView($course_id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            $moduleId = Module::where('course_id', $course_id)->pluck('id')->first();
            // lấy ra các khóa học đã hoàn thành
            $totalCourseComplted = Enrollment::where('status_course', 'completed')
                ->where('module_id', $moduleId)
                ->count();
            // Lấy ra ra các khóa học chưa hoàn thành
            $totalCourseInProgress = Enrollment::where('status_course', 'in_progress')
                ->where('module_id', $moduleId)
                ->count();
            // tổng đánh giá của của học viên
            $totalCoursAssment = Enrollment::where('status_course', 'completed')
                ->where('module_id', $moduleId)
                ->avg('rating_course');
            $totalScore = round($totalCoursAssment, 1);
            // tổng lượt xem của video
            $totalViewCourse = Course::where('id', $course_id)->sum('views_course');

            return response()->json([
                'totalCourseComplted' => $totalCourseComplted,
                'totalCourseInProgress' => $totalCourseInProgress,
                'totalCoursAssment' => $totalScore,
                'totalViewCourse' => $totalViewCourse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getCourseRevenue($course_id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            // lấy dữ liệu
            $moduleId = Module::where('course_id', $course_id)->pluck('id');
            // kiểm tra lỗis
            if ($moduleId->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy module nào cho course_id này.'
                ], 404);
            }
            // lấy dữ liệu
            $totalCourseComplted = Enrollment::where('enroll', true)
                ->whereIn('module_id', $moduleId)
                ->pluck('id');
            // kiểm tra lỗi
            if ($totalCourseComplted->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy enrollment nào cho các module này.'
                ], 404);
            }
            // lấy dữ liệu
            $totalAmount = Transaction::where('status', 'completed')->whereIn('enrollment_id', $totalCourseComplted)
                ->selectRaw("EXTRACT(MONTH FROM created_at) AS month")
                ->selectRaw("SUM(amount) AS total_amount")
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            // kiểm tra lỗi
            if ($totalAmount->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy giao dịch nào trong các enrollment này.'
                ], 404);
            }
            $totalCoursebyMouth = $totalAmount->map(function ($revenue) {
                return [
                    'month' => (int) $revenue->month,
                    'total_amount' => number_format($revenue->total_amount, 0, ',', '.') . ' VND'
                ];
            });
            return response()->json([
                'totalCoursebyMouth' => $totalCoursebyMouth,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getTotalClinetCartProfitCartNow()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            // tổng người dùng
            $usersClinet = User::where('role', 'client')->count();
            // tổng đơn hàng hôm nay
            $enrollmentId = Enrollment::where('enroll', true)
                ->whereDate('created_at', Carbon::today())
                ->pluck('id')
                ->toArray();
            $cartToDay = Transaction::whereIn('enrollment_id', $enrollmentId)
                ->whereDate('created_at', Carbon::today())
                ->pluck('id')
                ->count();
            $cartToDayMoney = Transaction::whereIn('enrollment_id', $enrollmentId)
                ->whereDate('created_at', Carbon::today())
                ->sum('amount');

            // $totalCourseNow = count($courseIds);

            // $moduleIds  = Enrollment::where('enroll', true)
            //     ->whereDate('created_at', Carbon::today())
            //     ->pluck('module_id')
            //     ->toArray();
            // $courseIds = Module::whereIn('id', $moduleIds)
            //     // ->pluck('course_id')
            //     ->toArray();
            // $totalCourseCartAll = count($courseIds);
            // // tổng đơn hàng 
            // $moduleId  = Enrollment::where('enroll', true)->toArray();


            return response()->json([
                'totalUserClinet' => $usersClinet,
                'totalCourseNow' => $cartToDay,
                'totalCartToDayMoney' => $cartToDayMoney,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
