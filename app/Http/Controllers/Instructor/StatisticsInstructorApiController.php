<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCourseResource;
use App\Http\Resources\CourseResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Route;
use App\Models\Transaction;
use App\Services\LogActivityService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Str;

class StatisticsInstructorApiController extends Controller
{

    // THỐNG KÊ
    // Gộp 4 cái tổng khóa học, tổng doanh thu, đánh giá giảng viên, tổng lượt xem (Đã sửa)
    public function getStatisticalCourse()
    {
        try {
            // Lấy ra user_id
            $user_id = auth('api')->user()->id;

            // Lấy ra tổng khóa học của giảng viên
            $totalCourse = Course::where('user_id', $user_id)->count();

            // Lấy ra tất cả các modules của giảng viên
            $coursesInstructor = Course::where('user_id', $user_id)->get();

            // Lấy ra tất cả các enrollments liên quan đến modules của giảng viên
            $enrollments = Enrollment::whereIn('course_id', $coursesInstructor->pluck('id'))->get();

            // Lấy ra tất cả các transactions liên quan đến enrollments
            $transactions = Transaction::whereIn('enrollment_id', $enrollments->pluck('id'))->get();

            // Tính tổng doanh thu từ các transactions
            $totalRevenue = $transactions->sum('amount');

            // Lấy ra đánh giá trung bình cho các khóa học của giảng viên
            $averageRating = Course::where('user_id', $user_id)->avg('rating_course');

            // Lấy ra tổng số lượt xem các khóa học của giảng viên
            $totalViews = Course::where('user_id', $user_id)->sum('views_course');

            // Trả về kết quả dưới dạng JSON
            return response()->json([
                'status' => 'success',
                'totalCourse' => $totalCourse,
                'totalRevenue' => $totalRevenue,
                'averageRating' => $averageRating,
                'totalViews' => $totalViews,
            ], 200);
        } catch (\Exception $e) {
            // Nếu có lỗi, trả về thông báo lỗi
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê tổng số khóa học của giảng viên, doanh thu tổng của giảng viên dựa trên người mua khóa học sau thuế.
    public function getStatistical()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Người dùng chưa đăng nhập',
                    'data' => null,
                ], 401);
            }

            // Lấy user_id từ người dùng đang đăng nhập
            $userId = $user->id;

            // Tính tổng doanh thu từ bảng transactions với điều kiện thêm
            $totalRevenue = Transaction::where('status', 'completed')
                ->whereIn(
                    'enrollment_id',
                    Enrollment::where('enroll', true)
                        ->whereHas('course', function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        })->pluck('id')
                )->get()->sum(function ($transaction) {
                    // Lấy khóa học liên quan
                    $enrollment = Enrollment::find($transaction->enrollment_id);
                    if ($enrollment && $enrollment) {
                        $course = $enrollment->course;
                        $taxAmount = ($transaction->amount * $course->tax_rate) / 100;
                        return $transaction->amount - $taxAmount; // Trừ thuế
                    }
                    return 0;
                });

            // Định dạng tổng doanh thu thành VND
            $totalRevenueFormatted = $totalRevenue;

            // Lấy tổng khóa học
            $totalCoursesCount = Course::where('user_id', $userId)->count();

            // Tính tổng lượt xem và tổng đánh giá
            $totalViews = Course::where('user_id', $userId)->sum('views_course');
            $totalRatings = Course::where('user_id', $userId)->sum('rating_course');

            // Tính trung bình đánh giá sao
            $averageRating = $totalCoursesCount > 0 ? $totalRatings / $totalCoursesCount : 0;

            return response()->json([
                'status' => 'success',
                'totalCourse' => $totalCoursesCount,
                'totalRevenue' => $totalRevenueFormatted,
                'totalViews' => $totalViews,
                'instructorRating' => $averageRating, // Định dạng trung bình với 1 số thập phân
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Doanh thu của giảng viên theo 12 tháng trong năm
    public function statisticalColumnChart()
    {
        try {
            $user_id = auth('api')->user()->id;

            // Lấy các khóa học của người dùng
            $courses = Course::where('user_id', $user_id)
                ->where('del_flag', true) // Kiểm tra cờ del_flag của Course
                ->with([
                    'enrollments' => function ($query) {
                        $query->where('enroll', true)->where('del_flag', true); // Kiểm tra cờ enroll và del_flag của Enrollment
                    },
                    'enrollments.transactions' => function ($query) {
                        $query->where('del_flag', true); // Kiểm tra cờ del_flag của Transaction
                    }
                ])
                ->get();

            // Mảng để lưu doanh thu theo tháng, khởi tạo từ tháng 01 đến 12 với giá trị 0
            $monthlyRevenue = array_fill(1, 12, 0);

            // Duyệt qua từng khóa học
            foreach ($courses as $course) {

                // Duyệt qua từng enrollment trong module
                foreach ($course->enrollments as $enrollment) {
                    $transaction = $enrollment->transactions; // Quan hệ hasOne

                    // Kiểm tra transaction có hợp lệ hay không
                    if (!$transaction || !isset($transaction->created_at)) {
                        continue; // Bỏ qua nếu transaction không hợp lệ
                    }

                    // Lấy tháng từ ngày tạo transaction
                    $month = (int) $transaction->created_at->format('m'); // Định dạng tháng: MM

                    // Cộng amount vào doanh thu của tháng tương ứng
                    $monthlyRevenue[$month] += $transaction->amount;

                }
            }

            // Định dạng kết quả để trả về
            $formattedRevenue = [];
            foreach ($monthlyRevenue as $month => $revenue) {
                $formattedRevenue[str_pad($month, 2, '0', STR_PAD_LEFT)] = $revenue; // Tháng định dạng 2 chữ số
            }

            return response()->json([
                'status' => 'success',
                'data' => $formattedRevenue,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

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

    // Khóa học có tổng đánh giá cao nhất
    public function statisticalHighestRatingCourse()
    {
        try {
            $user_id = auth('api')->user()->id;


            // Lấy khóa học có rating_course cao nhất và status_course là active
            $highestRatedCourses = Course::where('user_id', $user_id)
                ->where('status_course', 'success')
                ->orderBy('rating_course', 'desc')
                ->take(1)
                ->get();

            if ($highestRatedCourses->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học.',
                    'data' => null,
                ], 404);
            }

            // Trả về kết quả dưới dạng CourseResource collection
            return response()->json([
                'status' => 'success',
                'data' => CourseResource::collection($highestRatedCourses),
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
