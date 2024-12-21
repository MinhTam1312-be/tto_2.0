<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Favorite_Course;
use App\Models\Module;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class StatisticsAccountantApiController extends Controller
{
    // Thống kê tổng người dùng
    public function statisticalUser()
    {
        try {
            // Đếm số lượng người dùng với del_flag = true
            $userCount = User::where('del_flag', true)->count();

            // Trả về kết quả
            return response()->json([
                'success' => true,
                'data' => [
                    'user_count' => $userCount,
                ],
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500); // Mã trạng thái HTTP 500: Internal Server Error
        }
    }

    // Thống kê tổng enroll
    public function statisticalEnrollment()
    {
        try {
            // Đếm số lượng transaction với status là 'completed' và del_flag = true
            $enrollCount = Transaction::where('status', 'completed')
                ->where('del_flag', true)
                ->count();

            // Trả về kết quả
            return response()->json([
                'success' => true,
                'data' => [
                    'enrollCount' => $enrollCount,
                ],
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500); // Mã trạng thái HTTP 500: Internal Server Error
        }
    }

    //Thống kê tổng lợi nhuận (sau thuế)
    public function statisticalProfits()
    {
        try {
            // Tính tổng doanh thu từ bảng transactions với điều kiện enroll là true và del_flag = true
            $totalRevenue = Transaction::where('status', 'completed')
                ->whereIn(
                    'enrollment_id',
                    Enrollment::where('enroll', true)
                        ->where('del_flag', true)
                        ->pluck('id')
                )->get()->sum(function ($transaction) {
                    $enrollment = Enrollment::find($transaction->enrollment_id);
                    if ($enrollment && $enrollment->module) {
                        $course = $enrollment->module->course;
                        if ($course) {
                            $taxAmount = ($transaction->amount * $course->tax_rate) / 100;
                            return $transaction->amount - $taxAmount; // Trừ thuế
                        }
                    }
                    return 0;
                });

            return response()->json([
                'status' => 'success',
                // 'totalRevenue' s=> number_format($totalRevenue, 0, ',', '.'), // Định dạng VND
                'totalRevenue' => $totalRevenue, // Định dạng VND
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê tổng enroll hôm nay
    public function statisticalEnrollmentToday()
    {
        try {
            // Đếm số lượng người dùng với del_flag = true
            $enrollCountToday = Transaction::where('status', 'completed')
                ->where('del_flag', true)
                ->whereDate('created_at', now()->toDateString()) // Điều kiện ngày hôm nay
                ->count();

            // Trả về kết quả
            return response()->json([
                'success' => true,
                'enrollCountToday' => $enrollCountToday,
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500); // Mã trạng thái HTTP 500: Internal Server Error
        }
    }

    // Gộp 4 function thành 1 API
    public function getStatistics()
    {
        try {
            // Thống kê tổng người dùng
            $userCount = User::where('del_flag', true)->count();

            // Thống kê tổng enroll
            $enrollCount = Enrollment::where('del_flag', true)
                ->where('enroll', true)
                ->count();

            // Thống kê tổng lợi nhuận (sau thuế)
            $totalRevenue = Transaction::where('status', 'completed')
                ->whereIn(
                    'enrollment_id',
                    Enrollment::where('enroll', true)
                        ->where('del_flag', true)
                        ->pluck('id')
                )->get()->sum(function ($transaction) {
                    $enrollment = Enrollment::find($transaction->enrollment_id);
                    if ($enrollment && $enrollment->module) {
                        $course = $enrollment->module->course;
                        if ($course) {
                            $taxAmount = ($transaction->amount * $course->tax_rate) / 100;
                            return $transaction->amount - $taxAmount; // Trừ thuế
                        }
                    }
                    return 0;
                });

            // Thống kê tổng enroll hôm nay
            $enrollCountToday = Enrollment::where('del_flag', true)
                ->where('enroll', true)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            // Trả về kết quả
            return response()->json([
                'success' => true,
                'data' => [
                    'user_count' => $userCount,
                    'enroll_count' => $enrollCount,
                    'total_revenue' => $totalRevenue,
                    'enroll_count_today' => $enrollCountToday,
                ],
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Thống kê tổng lợi nhuận theo tháng trong năm
    public function statisticalProfitsByMonth($year = null)
    {
        try {
            // Nếu không truyền năm, sử dụng năm mặc định là 2024
            $year = $year ?? 2024;

            // Kiểm tra xem năm có hợp lệ không (chỉ nhận giá trị số)
            if (!is_numeric($year) || strlen($year) != 4) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Năm không hợp lệ.',
                ], 400); // Mã trạng thái HTTP 400: Bad Request
            }

            // Khởi tạo mảng kết quả theo tháng
            $profitsByMonth = [];

            // Lặp qua từng tháng (1 đến 12)
            for ($month = 1; $month <= 12; $month++) {
                // Tính tổng doanh thu trong tháng hiện tại
                $monthlyRevenue = Transaction::where('status', 'completed')
                    ->whereYear('created_at', $year) // Điều kiện năm
                    ->whereMonth('created_at', $month) // Điều kiện tháng
                    ->whereIn(
                        'enrollment_id',
                        Enrollment::where('enroll', true)
                            ->where('del_flag', true)
                            ->pluck('id')
                    )->get()->sum(function ($transaction) {
                        $enrollment = Enrollment::find($transaction->enrollment_id);
                        if ($enrollment && $enrollment->module) {
                            $course = $enrollment->module->course;
                            if ($course) {
                                $taxAmount = ($transaction->amount * $course->tax_rate) / 100;
                                return $transaction->amount - $taxAmount; // Trừ thuế
                            }
                        }
                        return 0;
                    });

                // Thêm doanh thu vào mảng kết quả
                $profitsByMonth[$month] = $monthlyRevenue;
            }

            return response()->json([
                'status' => 'success',
                'year' => $year,
                'profitsByMonth' => $profitsByMonth,
            ], 200); // Mã trạng thái HTTP 200: OK
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500); // Mã trạng thái HTTP 500: Internal Server Error
        }
    }

    // lấy thống kê ra theo tuần
    public function getWeeklyStatistics($year = 2024, $week)
    {
        try {
            // Lấy tuần hiện tại dựa trên tham số 'week' và tính toán ngày bắt đầu và kết thúc của tuần đó
            $startOfWeek = Carbon::now()->setISODate($year, $week, Carbon::MONDAY);
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            // Truy vấn dữ liệu từ bảng transactions và tính toán lợi nhuận theo từng ngày trong tuần
            $statistics = DB::table('transactions')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as date"),  // Định dạng ngày
                    DB::raw('SUM(amount) as total_amount'), // Tổng tiền trong ngày
                    DB::raw('EXTRACT(DOW FROM created_at) + 1 as day_of_week') // Thứ trong tuần (1 = Chủ nhật, 2 = Thứ 2, ...)
                )
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"), DB::raw('EXTRACT(DOW FROM created_at)')) // Nhóm theo ngày và thứ trong tuần
                ->orderBy('date', 'asc') // Sắp xếp theo ngày từ Thứ Hai đến Chủ Nhật
                ->get();

            // Để đổi giá trị ngày trong tuần thành tên ngày (Thứ 2, Thứ 3, ..., Chủ nhật)
            $dayNames = [
                1 => 'Sunday',
                2 => 'Monday',
                3 => 'Tuesday',
                4 => 'Wednesday',
                5 => 'Thursday',
                6 => 'Friday',
                7 => 'Saturday'
            ];

            // Cập nhật dữ liệu để thêm tên thứ và chuyển total_amount sang kiểu số
            $profitsByDay = [
                2 => 0, // Monday
                3 => 0, // Tuesday
                4 => 0, // Wednesday
                5 => 0, // Thursday
                6 => 0, // Friday
                7 => 0, // Saturday
                1 => 0  // Sunday
            ];

            // Cập nhật dữ liệu lợi nhuận theo ngày trong tuần
            foreach ($statistics as $item) {
                $profitsByDay[$item->day_of_week] = (float) $item->total_amount;
            }

            // Trả về kết quả dưới dạng JSON
            return response()->json([
                'status' => 'success',
                'year' => Carbon::now()->year,
                'profitsByDay' => [
                    'Thứ 2' => $profitsByDay[2],
                    'Thứ 3' => $profitsByDay[3],
                    'Thứ 4' => $profitsByDay[4],
                    'Thứ 5' => $profitsByDay[5],
                    'Thứ 6' => $profitsByDay[6],
                    'Thứ 7' => $profitsByDay[7],
                    'Chủ Nhật' => $profitsByDay[1],
                ] // Lợi nhuận cho từng ngày trong tuần
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching weekly profits. Please try again later.'
            ], 500);
        }
    }

    // lấy ra thống kê theo năm, tháng, ngày, theo trạng thái
    public function getTranstionStatisticsRequest($filterBy, $status, $order)
    {
        try {
            // Khởi tạo khoảng thời gian lọc
            $startDate = null;
            $endDate = null;

            switch ($filterBy) {
                case 'day': // Lọc theo ngày cụ thể
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;

                case 'week': // Lọc theo tuần
                    $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY); // Bắt đầu từ thứ 2
                    $endDate = Carbon::now()->endOfWeek(); // Kết thúc vào Chủ nhật
                    break;

                case 'month': // Lọc theo tháng
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;

                case 'year': // Lọc theo năm
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;

                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid filter_by parameter. Allowed values: day, week, month, year.'
                    ], 400);
            }

            // Kiểm tra giá trị status
            if (!in_array($status, ['completed', 'pending', 'canceled', 'failed', 'all'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status parameter. Allowed values: completed, pending, canceled, failed, all.'
                ], 400);
            }

            // Kiểm tra giá trị order
            if (!in_array($order, ['asc', 'desc'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid order parameter. Allowed values: asc, desc.'
                ], 400);
            }

            // Truy vấn dữ liệu
            $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $transactions = $query->orderBy('created_at', $order)->get();

            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching transactions. Please try again later.'
            ], 500);
        }
    }

    // Lấy chi tiết transtion ra các khóa học và thông tin user
    public function getDetailTranstion($transactions_id)
    {
        try {
            $enrollment_id = Transaction::where('id', $transactions_id)->pluck('enrollment_id');

            if (!$enrollment_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Enrollment ID not found',
                ], 400);
            }

            $module_id = Enrollment::where('id', $enrollment_id)->pluck('module_id');
            $user_id = Enrollment::where('id', $enrollment_id)->pluck('user_id');

            if (!$module_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Module ID not found',
                ], 400);
            }

            $course_id = Module::where('id', $module_id)->pluck('course_id');

            if (!$course_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course ID not found',
                ], 400);
            }

            $course = Course::with([
                'user:id,fullname,email,avatar' // Lấy thông tin avatar từ user
            ])->where('id', $course_id)->first();
            $course = Course::where('id', $course_id)->first();

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found',
                ], 400);
            }

            $user = User::where('id', $user_id)->first(['fullname', 'email', 'phonenumber']);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'course' => new CourseResource($course),
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Lấy chi tiết khóa học ra các trastion
    public function getTransactionsByCourse($slug_course, $filterBy, $status, $order)
    {
        // Lấy ID của khóa học từ slug
        $course_id = Course::where('slug_course', $slug_course)->pluck('id');

        if (!$course_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid course slug.'
            ], 404);
        }

        // Khởi tạo khoảng thời gian lọc
        $startDate = null;
        $endDate = null;

        switch ($filterBy) {
            case 'day': // Lọc theo ngày cụ thể
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;

            case 'week': // Lọc theo tuần
                $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $endDate = Carbon::now()->endOfWeek();
                break;

            case 'month': // Lọc theo tháng
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;

            case 'year': // Lọc theo năm
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid filter_by parameter. Allowed values: day, week, month, year.'
                ], 400);
        }

        // Kiểm tra giá trị status
        if (!in_array($status, ['completed', 'pending', 'canceled', 'failed', 'all'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid status parameter. Allowed values: completed, pending, canceled, failed, all.'
            ], 400);
        }

        // Kiểm tra giá trị order
        if (!in_array($order, ['asc', 'desc'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid order parameter. Allowed values: asc, desc.'
            ], 400);
        }

        // Truy vấn dữ liệu
        $query = Transaction::whereIn('enrollment_id', function ($query) use ($course_id) {
            $query->select('id')
                ->from('enrollments')
                ->whereIn('module_id', function ($subQuery) use ($course_id) {
                    $subQuery->select('id')
                        ->from('modules')
                        ->where('course_id', $course_id);
                });
        })->whereBetween('created_at', [$startDate, $endDate]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', $order)->get();

        // Trả về kết quả
        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ], 200);
        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ], 200);
    }

    // Lấy ra người dùng có bao nhiêu thanh đơn thanh toán
    public function userByTranstion($phone_or_email)
    {
        try {
            // Lấy user ID từ email hoặc số điện thoại
            $user_id = User::where('email', $phone_or_email)
                ->orWhere('phonenumber', $phone_or_email)
                ->value('id');

            if (!$user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found with the provided email or phone number.'
                ], 404);
            }

            // Lấy danh sách enrollment ID và module ID của user
            $enrollments = Enrollment::where('user_id', $user_id)->get(['id', 'module_id']);

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No enrollments found for the user.'
                ], 404);
            }

            // Lấy transaction dựa trên enrollment ID
            $enrollment_ids = $enrollments->pluck('id');
            $transactions = Transaction::whereIn('enrollment_id', $enrollment_ids)->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No transactions found for the user.'
                ], 404);
            }
            // Map dữ liệu giao dịch với thông tin khóa học
            // Tải trước tất cả thông tin cần thiết
            $enrollmentIds = $transactions->pluck('enrollment_id')->toArray();

            $enrollments = Enrollment::whereIn('id', $enrollmentIds)
                ->with(['module.course:id,name_course']) // Load thông tin module và khóa học liên quan
                ->get()
                ->keyBy('id'); // Tạo key để tìm kiếm nhanh hơn

            // Map dữ liệu giao dịch với thông tin khóa học
            $data = $transactions->map(function ($transaction) use ($enrollments) {
                // Lấy enrollment liên quan
                $enrollment = $enrollments->get($transaction->enrollment_id);

                // Kiểm tra dữ liệu trước khi sử dụng
                $courseName = $enrollment?->module?->course?->name_course ?? null;

                // Trả về thông tin giao dịch kèm khóa học
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'payment_method' => $transaction->payment_method,
                    'status' => $transaction->status,
                    'payment_discription' => $transaction->payment_discription,
                    'enrollment_id' => $transaction->enrollment_id,
                    'del_flag' => $transaction->del_flag,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'course' => $courseName,
                ];
            });


            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Lấy ra tất cả khóa học theo kèm doanh thu bán được của khóa học đó
    public function courseEnrollmentRevenue()
    {
        try {
            // Lấy tất cả khóa học và thông tin liên quan
            $courses = Course::with(['modules.enrollments.transactions', 'user'])
                ->whereHas('modules.enrollments', function ($query) {
                    $query->where('enroll', true)->where('del_flag', true);
                })
                ->get();

            // Kiểm tra nếu không có khóa học
            if ($courses->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học nào.',
                ], 404);
            }

            // Tính doanh thu từng khóa học
            $coursesWithRevenue = [];
            foreach ($courses as $course) {
                $totalRevenue = 0;

                foreach ($course->modules as $module) {
                    foreach ($module->enrollments as $enrollment) {
                        if ($enrollment->transactions) {
                            $totalRevenue += $enrollment->transactions
                                ->where('status', 'completed')
                                ->where('del_flag', true)
                                ->sum('amount');
                        }
                    }
                }

                $coursesWithRevenue[] = [
                    'id' => $course->id,
                    'name_course' => $course->name_course,
                    'slug_course' => $course->slug_course,
                    'description_course' => $course->description_course,
                    'img_course' => $course->img_course,
                    'price_course' => $course->price_course,
                    'discount_price_course' => $course->discount_price_course,
                    'status_course' => $course->status_course,
                    'views_course' => $course->views_course,
                    'rating_course' => $course->rating_course,
                    'tax_rate' => $course->tax_rate,
                    'num_document' => $course->documents()->count(),
                    'num_chapter' => $course->chapters()->count(),
                    'del_flag' => $course->del_flag,
                    'instructor_id' => $course->user_id,
                    'instructor_avatar' => $course->user->avatar,
                    'instructor_name' => $course->user->fullname,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'total_revenue' => $totalRevenue,
                ];
            }

            // Sắp xếp danh sách khóa học theo doanh thu giảm dần
            usort($coursesWithRevenue, function ($a, $b) {
                return $b['total_revenue'] <=> $a['total_revenue'];
            });

            return response()->json([
                'status' => 'success',
                'data' => $coursesWithRevenue,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Lấy ra tất cả khóa học theo kèm doanh thu bán được của khóa học đó theo slug_course 
    public function courseEnrollmentRevenueBySlug($slug_course)
    {
        try {
            // Lấy khóa học theo slug, tính tổng doanh thu trong query
            $course = Course::with([
                'modules.enrollments.transactions' => function ($query) {
                    $query->where('status', 'completed')->where('del_flag', true);
                },
                'documents',
                'chapters',
                'user',
            ])
                ->where('slug_course', $slug_course)
                ->whereHas('modules.enrollments', function ($query) {
                    $query->where('enroll', true)->where('del_flag', true);
                })
                ->first();

            // Kiểm tra nếu không tìm thấy khóa học
            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học.',
                ], 404);
            }

            // Tính tổng doanh thu bằng một truy vấn SQL
            $totalRevenue = Transaction::whereHas('enrollment.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
                ->where('status', 'completed')
                ->where('del_flag', true)
                ->sum('amount');

            // Trả về dữ liệu khóa học và doanh thu
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $course->id,
                    'name_course' => $course->name_course,
                    'slug_course' => $course->slug_course,
                    'img_course' => $course->img_course,
                    'price_course' => $course->price_course,
                    'total_revenue' => $totalRevenue,
                    'discription_course' => $course->description_course,
                    'discount_price_course' => $course->discount_price_course,
                    'views_course' => $course->views_course,
                    'rating_course' => $course->rating_course,
                    'status_course' => $course->status_course,
                    'tax_rate' => $course->tax_rate,
                    'num_document' => $course->documents->count(),
                    'num_chapter' => $course->chapters->count(),
                    'del_flag' => $course->del_flag,
                    'instructor_id' => $course->user_id,
                    'instructor_avatar' => $course->user->avatar,
                    'instructor_name' => $course->user->fullname,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Lấy ra các khóa học được nhiều lượt yêu thích nhất
    public function mostFavoritesByCourse()
    {
        try {
            $courses = Course::whereIn('id', function ($query) {
                $query->select('course_id')
                    ->from('favorite_courses')
                    ->where('del_flag', true) // Lọc các bản ghi không bị xóa mềm
                    ->groupBy('course_id')
                    ->orderByRaw('COUNT(course_id) DESC'); // Sắp xếp theo số lượng yêu thích giảm dần
            })
                ->get();
            return response()->json([
                'status' => 'success',
                'data' => CourseResource::collection($courses)
            ], 200); // Mã trạng thái HTTP 200: OK
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500); // Mã trạng thái HTTP 500: Internal Server Error
        }
    }

    // Lấy ra khóa học có đánh giá 5 sao yêu thích nhất 
    public function mostRaterFiveStarCourse()
    {
        try {
            $favorite_course_ids = Favorite_Course::where('del_flag', true)
                ->select('course_id', DB::raw('COUNT(course_id) as total'))
                ->groupBy('course_id')
                ->orderBy('total', 'desc')
                ->pluck('course_id');

            // Lấy chi tiết các khóa học và đếm số lượng chapters, documents
            $courses = Course::whereIn('id', $favorite_course_ids)->where('rating_course', '>', '4')->get();
            // Trả về JSON
            return response()->json([
                'status' => 'success',
                'data' => CourseResource::collection($courses),
            ], 200); // Mã trạng thái HTTP 200: OK
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Khóa học có doanh thu cao nhất
    public function HighRevenueCourse()
    {
        try {
            // Lấy tất cả các khóa học có liên quan
            $courses = Course::with(['modules.enrollments.transactions'])
                ->whereHas('modules.enrollments', function ($query) {
                    $query->where('enroll', true)->where('del_flag', true);
                })
                ->get();

            // Đếm số giao dịch hợp lệ
            $highestEnrollmentCourse = $courses->map(function ($course) {
                $transactionsCount = 0;
                $transactionsSum = 0;

                foreach ($course->modules as $module) {
                    foreach ($module->enrollments as $enrollment) {
                        $transactionsCount += $enrollment->transactions
                            ->where('status', 'completed')
                            ->where('del_flag', true)
                            ->count();
                        $transactionsSum += $enrollment->transactions
                            ->where('status', 'completed')
                            ->where('del_flag', true)
                            ->sum('amount');
                    }
                }

                $course->transactions_count = $transactionsCount;
                $course->transactions_sum = $transactionsSum;
                return $course;
            })->sortByDesc('transactions_count')->first();

            // Kiểm tra nếu không tìm thấy khóa học
            if (!$highestEnrollmentCourse) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học nào.',
                ], 404);
            }
            // dd($highestEnrollmentCourse);
            return response()->json([
                'status' => 'success',
                'count_revenue_times' => $highestEnrollmentCourse->transactions_count,
                'total_revenue' => $highestEnrollmentCourse->transactions_sum,

                'data' => new CourseResource(resource: $highestEnrollmentCourse)
            ], 200); // Mã trạng thái HTTP 200: OK

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Khóa học có lượng người mua thấp nhất
    public function LowRevenueCourse()
    {
        try {
            // Lấy tất cả các khóa học có liên quan
            $courses = Course::with(['modules.enrollments.transactions'])
                ->whereHas('modules.enrollments', function ($query) {
                    $query->where('enroll', true)->where('del_flag', true);
                })
                ->get();

            // Đếm số giao dịch hợp lệ
            $lowestEnrollmentCourse = $courses->map(function ($course) {
                $transactionsCount = 0;
                $transactionsSum = 0;
                foreach ($course->modules as $module) {
                    foreach ($module->enrollments as $enrollment) {
                        $transactionsCount += $enrollment->transactions
                            ->where('status', 'completed')
                            ->where('del_flag', true)
                            ->count();
                        $transactionsSum += $enrollment->transactions
                            ->where('status', 'completed')
                            ->where('del_flag', true)
                            ->sum('amount');
                    }
                }

                $course->transactions_count = $transactionsCount;
                $course->transactions_sum = $transactionsSum;
                return $course;
            })->sortBy('transactions_count')->first();

            // Kiểm tra nếu không tìm thấy khóa học
            if (!$lowestEnrollmentCourse) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học nào.',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'count_revenue_times' => $lowestEnrollmentCourse->transactions_count,
                'total_revenue' => $lowestEnrollmentCourse->transactions_sum,
                'data' => new CourseResource($lowestEnrollmentCourse)
            ], 200); // Mã trạng thái HTTP 200: OK

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Lấy ra gộp thống kê mostFavoritesByCourse, mostRaterFiveStarCourse, LowRevenueCourse, HighRevenueCourse, 
    public function getAllStatistics(Request $request)
    {
        try {
            $statistics = [];

            // Load tất cả các khóa học cần thiết
            $courses = Course::with([
                'modules.enrollments.transactions' => function ($query) {
                    $query->where('status', 'completed')->where('del_flag', true);
                }
            ])->whereHas('modules.enrollments', function ($query) {
                $query->where('enroll', true)->where('del_flag', true);
            })->get();

            // Tính toán High và Low Revenue Course trong một vòng lặp
            $lowestEnrollmentCourse = null;
            $highestEnrollmentCourse = null;

            foreach ($courses as $course) {
                $transactionsCount = 0;
                $transactionsSum = 0;

                foreach ($course->modules as $module) {
                    foreach ($module->enrollments as $enrollment) {
                        $transaction = $enrollment->transactions; // Lấy transaction

                        if ($transaction) {
                            $transactionsCount += 1; // Đếm số transaction
                            $transactionsSum += $transaction->amount; // Tổng tiền từ transaction
                        }
                    }
                }

                $course->transactions_count = $transactionsCount;
                $course->transactions_sum = $transactionsSum;

                if (
                    !$lowestEnrollmentCourse ||
                    $course->transactions_count < $lowestEnrollmentCourse->transactions_count
                ) {
                    $lowestEnrollmentCourse = $course;
                }

                if (
                    !$highestEnrollmentCourse ||
                    $course->transactions_count > $highestEnrollmentCourse->transactions_count
                ) {
                    $highestEnrollmentCourse = $course;
                }
            }

            // Gán kết quả LowRevenueCourse
            $statistics['low_revenue_course'] = $lowestEnrollmentCourse
                ? [
                    'count_revenue_times' => $lowestEnrollmentCourse->transactions_count,
                    'total_revenue' => $lowestEnrollmentCourse->transactions_sum,
                    'data' => new CourseResource($lowestEnrollmentCourse),
                ]
                : 'Không tìm thấy khóa học nào.';

            // Gán kết quả HighRevenueCourse
            $statistics['high_revenue_course'] = $highestEnrollmentCourse
                ? [
                    'count_revenue_times' => $highestEnrollmentCourse->transactions_count,
                    'total_revenue' => $highestEnrollmentCourse->transactions_sum,
                    'data' => new CourseResource($highestEnrollmentCourse),
                ]
                : 'Không tìm thấy khóa học nào.';

            // Most Favorite Course
            $mostFavoriteCourse = Course::whereIn('id', function ($query) {
                $query->select('course_id')
                    ->from('favorite_courses')
                    ->where('del_flag', true)
                    ->groupBy('course_id')
                    ->orderByRaw('COUNT(course_id) DESC')
                    ->limit(1); // Lấy 1 khóa học
            })->first();

            $statistics['most_favorite_course'] = $mostFavoriteCourse ? new CourseResource($mostFavoriteCourse) : null;


            // Most Rater Five Star Course
            $favoriteCourseIds = Favorite_Course::where('del_flag', true)
                ->select('course_id', DB::raw('COUNT(course_id) as total'))
                ->groupBy('course_id')
                ->orderBy('total', 'desc')
                ->pluck('course_id');

            // Lấy khóa học có rating_course cao nhất trong danh sách các khóa học yêu thích
            $topRatedCourse = Course::whereIn('id', $favoriteCourseIds)
                ->orderBy('rating_course', 'desc') // Sắp xếp theo rating_course giảm dần
                ->first(); // Lấy khóa học đầu tiên

            $statistics['most_rated_five_star_course'] = $topRatedCourse ? new CourseResource($topRatedCourse) : null;


            return response()->json([
                'status' => 'success',
                'data' => $statistics,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Thống kê doanh thu khóa học theo ngày truyền vào
    public function totalRevenueByDate(Request $request)
    {
        try {
            // Xác thực đầu vào
            $request->validate(
                [
                    'start_date' => 'required|date|before_or_equal:end_date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                ],
                [
                    'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
                    'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
                    'start_date.before_or_equal' => 'Ngày bắt đầu phải là ngày trước hoặc bằng ngày kết thúc.',
                    'end_date.required' => 'Ngày kết thúc là bắt buộc.',
                    'end_date.date' => 'Ngày kết thúc không hợp lệ.',
                    'end_date.after_or_equal' => ':Ngày kết thúc phải là ngày sau hoặc bằng ngày bắt đầu.',
                    // Thêm các quy tắc khác nếu cần
                ]
            );

            // Lấy doanh thu từ các giao dịch trong khoảng thời gian
            $totalRevenue = Transaction::where('status', 'completed')
                ->where('del_flag', true)
                ->whereBetween('created_at', [$request->start_date, $request->end_date])
                ->sum('amount');

            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_revenue' => $totalRevenue,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
