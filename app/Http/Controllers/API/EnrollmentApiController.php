<?php

namespace App\Http\Controllers\API;

use App\Events\NotificationUserRegisterCourse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Status_Doc;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Dompdf\Dompdf;
use Cloudinary\Uploader;
use Cloudinary\Api\Upload\UploadApi;
use Dompdf\Options;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;

class EnrollmentApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Lấy ra feedback theo sao và limit (đã sửa)
    public function feedbackLimit($star, $limit)
    {
        try {
            // Kiểm tra xem $param có phải là một số hợp lệ không
            if (!is_numeric($star) || $star <= 0 || $star > 5) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Rating không hợp lệ',
                    'data' => null,
                ], 400);
            }
            // Kiểm tra xem $param có phải là một số hợp lệ không
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Lấy các feedback có rating_course >= $star và feedback_text khác null
            $feedbacks = Enrollment::with(['course', 'user']) // Lấy thông tin từ Module, Course và User
                ->where('rating_course', '>=', $star) // Lọc theo rating_course
                ->whereNotNull('feedback_text') // Đảm bảo feedback_text khác null
                ->where('feedback_text', '!=', '')
                ->limit($limit) // Giới hạn số lượng bản ghi
                ->get();
            // dd($feedbacks);

            if ($feedbacks->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có dữ liệu phù hợp',
                    'data' => null,
                ], 404);
            }

            $result = $feedbacks->map(function ($feedback) {
                return [
                    'course_id' => optional($feedback->course)->id,
                    'img_course' => optional($feedback->course)->img_course,
                    'user_id' => optional($feedback->user)->id,
                    'rating_course' => $feedback->rating_course,
                    'feedback_text' => $feedback->feedback_text,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    // Chức năng đăng ký khóa học của học viên (đã sửa) 
    public function userRegisterCourse($course_id)
    {
        try {
            // Xác thực người dùng (middleware auth đảm bảo user luôn tồn tại)
            $user = auth('api')->user();

            // Kiểm tra xem người dùng đã đăng ký khóa học này ở bất kỳ module nào chưa
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course_id) // Hoặc kiểm tra theo course_id nếu muốn
                ->first();

            if ($existingEnrollment) {
                return response()->json(['message' => 'Bạn đã đăng ký khóa học này rồi.'], 400);
            }
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course_id,
                'status_course' => 'in_progress',
                'enroll' => 1,
                'del_flag' => true,
            ]);

            return response()->json(['message' => 'Đăng ký khóa học thành công.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đăng ký khóa học. Vui lòng thử lại sau.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Đã sửa xóa module ra và truyền course_id vào Enrollment (Không xài tới)
    public function checkEnrollment($courseId)
{
    try {
        // Lấy người dùng hiện tại
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
        }

        // Kiểm tra xem người dùng đã đăng ký khóa học hay chưa
        $enrollmentExists = Enrollment::where([
            ['user_id', $user->id],
            ['course_id', $courseId],
            ['enroll', true],
            ['del_flag', true] // del_flag = true tức là còn hoạt động
        ])->exists();

        return response()->json([
            'is_enrolled' => $enrollmentExists
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
        ], 500);
    }
}


    // Gọi ra các feedback của khóa học
    // Đã sửa xóa module ra và truyền course_id vào Enrollment (đã sửa)
    public function feedbackCourse($course_id, $star, $limit)
    {
        try {
            // Kiểm tra xem $star, $limit có hợp lệ không
            if (!is_numeric($star) || $star <= 0 || $star > 5) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Rating không hợp lệ',
                    'data' => null,
                ], 400);
            }
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Lấy feedbacks có rating_course >= $star và slug_course = $slug_course
            $feedbacks = Enrollment::with(['module.course', 'user']) // Lấy ra course_uuid từ module và thông tin user
                ->whereHas('module.course', function ($query) use ($course_id) {
                    $query->where('id', $course_id); // Lọc theo course_uuid
                })
                ->where('rating_course', '>=', $star) // Lọc theo rating_course
                ->limit($limit) // Giới hạn số lượng bản ghi
                ->get();

            if ($feedbacks->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có feedback nào ở khóa học này',
                    'data' => null,
                ], 404);
            }

            $result = $feedbacks->map(function ($feedback) {
                return [
                    'course_id' => optional($feedback->module->course)->id,
                    'user_id' => optional($feedback->user)->id,
                    'fullname' => optional($feedback->user)->fullname,
                    'avatar' => optional($feedback->user)->avatar,
                    'rating_course' => $feedback->rating_course,
                    'feedback_text' => $feedback->feedback_text,
                ];
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    //Lấy ra tiến độ của các khóa học cho người dùng (đã sửa)
    public function getProgress($orderBy = null): JsonResponse
    {
        try {
            $user_id = auth('api')->user()->id;

            $enrollments = Enrollment::where('user_id', $user_id)
                ->where('enroll', true)
                ->where('del_flag', true)
                ->with([
                    'course' => function ($query) {
                        $query->where('del_flag', true)
                            ->where('status_course', 'success')
                            ->with([
                                'chapters' => function ($query) {
                                    $query->with('documents'); // Không kiểm tra del_flag cho documents
                                }
                            ]);
                    },
                    'status_docs' // Không kiểm tra del_flag trong status_docs
                ])
                ->get();
            // dd($enrollments);
            // Khởi tạo mảng chứa thông tin khóa học
            $courses = $enrollments->map(function ($enrollment) {

                // Kiểm tra nếu module và course tồn tại
                if ($enrollment && $enrollment->course) {
                    $course = $enrollment->course;

                    // Đếm số video đã xem
                    $watchedVideos = $enrollment->status_docs()->where('status_doc', true)->count();

                    // Đếm số chương và số tài liệu
                    $numDocuments = $course->chapters->flatMap(function ($chapter) {
                        return $chapter->documents;
                    })->count();
                    // Tính phần trăm tiến độ
                    $progressPercentage = $numDocuments > 0 ? round(($watchedVideos / $numDocuments) * 100, 1) : 0;

                    // Đếm số chương
                    $numChapters = $course->chapters()->count(); // Không kiểm tra del_flag cho chapters

                    // Trả về mảng dữ liệu khóa học
                    return [
                        'id' => $course->id,
                        'name_course' => $course->name_course,
                        'slug_course' => $course->slug_course,
                        'img_course' => $course->img_course,
                        'discription_course' => $course->discription_course,
                        'price_course' => $course->price_course,
                        'discount_price_course' => $course->discount_price_course,
                        'status_course' => $course->status_course,
                        'views_course' => $course->views_course,
                        'rating_course' => $course->rating_course,
                        'num_document' => $numDocuments,
                        'status_course_enrollment' => $enrollment->status_course,
                        'num_chapter' => $numChapters,
                        'del_flag' => $course->del_flag,
                        'instructor_id' => $course->user_id,
                        'instructor_name' => $course->user->fullname,
                        'created_at' => $course->created_at,
                        'updated_at' => $course->updated_at,
                        'watchedVideos' => $watchedVideos,
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                return null;
            })->filter();

            switch ($orderBy) {
                case 'free':
                    $courses = $courses->filter(function ($course) {
                        return $course['price_course'] == 0;
                    });
                    break;
                case 'pro':
                    $courses = $courses->filter(function ($course) {
                        return $course['price_course'] > 0;
                    });
                    break;

                default:
                    return response()->json([
                        'data' => $courses->values(),
                    ], 200);
                    break;
            }

            return response()->json([
                'data' => $courses->values(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Lấy ra tiến độ của một khóa học cho người dùng (đã sửa)
    public function getProgressByCourse($course_id)
    {
        try {
            $user_id = auth('api')->user()->id;
            $enrollments = Enrollment::where('user_id', $user_id)
                ->where('enroll', true)
                ->where('del_flag', true)
                ->with([
                    'course' => function ($query) use ($course_id) {
                        $query->where('del_flag', true)
                            ->where('id', $course_id)
                            ->where('status_course', 'success')
                            ->with([
                                'chapters' => function ($query) {
                                    $query->with('documents'); // Không kiểm tra del_flag cho documents
                                }
                            ]);
                    },
                    'status_docs' // Không kiểm tra del_flag trong status_docs
                ])
                ->get();
            // Khởi tạo mảng chứa thông tin khóa học
            $courses = $enrollments->map(function ($enrollment) {

                // Kiểm tra nếu module và course tồn tại
                if ($enrollment && $enrollment->course) {
                    $course = $enrollment->course;

                    // Đếm số video đã xem
                    $watchedVideos = $enrollment->status_docs()->where('status_doc', true)->count();

                    // Đếm số chương và số tài liệu
                    $numDocuments = $course->chapters->flatMap(function ($chapter) {
                        return $chapter->documents;
                    })->count();

                    // Tính phần trăm tiến độ
                    $progressPercentage = $numDocuments > 0 ? round(($watchedVideos / $numDocuments) * 100, 1) : 0;

                    // Đếm số chương
                    $numChapters = $course->chapters()->count(); // Không kiểm tra del_flag cho chapters

                    // Trả về mảng dữ liệu khóa học
                    return [
                        'id' => $course->id,
                        'name_course' => $course->name_course,
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                return null;
            })->filter();

            return response()->json([
                'status' => 'success',
                $courses->values(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // lấy ra tên và tiến độ của khóa học mà người dùng đã học (đã sửa)
    public function learingCourse(): JsonResponse
    {
        try {
            $user_id = auth('api')->user()->id;

            // Lấy ra tất cả các enrollment cho user_id đã đăng ký enroll = true
            $enrollments = Enrollment::where('user_id', $user_id)
                ->where('enroll', true)
                ->with(['course.chapters.documents', 'status_docs']) //Quan hệ
                ->get();

            // Khởi tạo mảng chứa thông tin khóa học
            $courses = $enrollments->map(function ($enrollment) {

                // Kiểm tra nếu module và course tồn tại
                if ($enrollment && $enrollment->course) {
                    $course = $enrollment->course;

                    // Lấy tổng số tài liệu của khóa học qua các chapters
                    $totalDocuments = $course->chapters->flatMap(function ($chapter) {
                        return $chapter->documentsProgress;
                    })->count();

                    // Đếm số video đã xem
                    $watchedVideos = $enrollment->status_docs()->where('status_doc', true)->count();

                    // Tính phần trăm tiến độ
                    $progressPercentage = $totalDocuments > 0 ? round(($watchedVideos / $totalDocuments) * 100, 1) : 0;

                    // Trả về mảng dữ liệu khóa học
                    return [
                        'course_id' => $course->id,
                        'name_course' => $course->name_course,
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                return null;
            })->filter(); // Loại bỏ các phần tử null nếu không có module/course

            // Trả về dữ liệu JSON
            return response()->json([
                'courses' => [
                    'user_id' => $user_id, // Return user ID properly
                    'courses' => $courses->values(), // Đảm bảo không có chỉ số bị trống
                ]
            ], 200);
        } catch (Exception $e) {
            // Trả về lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Đánh giá khóa học (đã sửa)
    public function addFeedback(Request $request, $course_id): JsonResponse
    {
        try {
            $user = auth('api')->user();

            // Lấy thông tin Enrollment
            $enrollment = Enrollment::where('course_id', $course_id)
                ->where('user_id', $user->id)
                ->where('status_course', 'completed')
                ->where('enroll', true)
                ->where('del_flag', true)
                ->first();

            // Kiểm tra nếu không tìm thấy Enrollment
            if (!$enrollment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin đăng ký hoặc khóa học chưa hoàn thành.'
                ], 404);
            }

            // Validate input
            $validatedData = $request->validate([
                'rating_course' => 'nullable|numeric|integer|min:1|max:5',
                'feedback_text' => 'nullable|max:300',
            ], [
                'rating_course.min' => 'Đánh giá sao không được bé hơn 1.',
                'rating_course.max' => 'Đánh giá sao không được lớn hơn 5.',
                'rating_course.numeric' => 'Đánh giá sao phải là số.',
                'rating_course.integer' => 'Đánh giá sao phải là số nguyên.',
                'feedback_text.max' => 'Đánh giá chỉ tối đa 300 kí tự.',
            ]);

            // Lưu đánh giá vào database
            if ($request->has('rating_course')) {
                $enrollment->rating_course = $request->rating_course;
            }
            if ($request->has('feedback_text')) {
                $enrollment->feedback_text = $request->feedback_text;
            }
            $enrollment->save();

            // Tính trung bình rating_course của các Enrollment cho khóa học này
            $ratings = Enrollment::where('course_id', $course_id)
                ->whereNotNull('rating_course')
                ->where('del_flag', true)
                ->pluck('rating_course');

            // dd($ratings);

            if ($ratings->isNotEmpty()) {
                $averageRating = $ratings->avg();

                // Cập nhật rating_course cho bảng Course
                $course = Course::find($course_id);
                if ($course) {
                    $course->rating_course = round($averageRating, 1);
                    $course->save();
                }
            }

            // Trả về kết quả thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Đánh giá khóa học thành công.',
                'rating_course' => $enrollment->rating_course,
                'feedback_text' => $enrollment->feedback_text,
                'average_rating' => $course->rating_course ?? null,
            ], 200);
        } catch (Exception $e) {
            // Log lỗi chi tiết

            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Check người dùng đã đăng ký khóa học hay chưa (Không tồn tại api)
    public function checkEnrollmentCourse(Request $request)
    {
        try {
            // Lấy người dùng hiện tại đã đăng nhập
            $user = auth('api')->user();

            // Kiểm tra xem người dùng đã đăng ký khóa học chưa
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('del_flag', true) // Nếu bạn sử dụng cờ xóa
                ->first();

            if ($enrollment) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy ra thành công dữ liệu.',
                    'enrollment' => $enrollment->enroll,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Có lỗi xảy ra trong quá trình kiểm tra.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Thay đổi trạng thái khóa học cho học viên (đã sửa)
    public function changeStatusCourseCompleted($course_id)
    {
        try {
            $user = auth('api')->user();

            // Lấy thông tin Enrollment
            $updatedRows = Enrollment::where('course_id', $course_id)
                ->where('user_id', $user->id)
                ->where('enroll', true)
                ->where('del_flag', true)
                ->update(['status_course' => 'completed']);

            if ($updatedRows === 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy thông tin đăng ký khóa học hoặc khóa học chưa được hoàn thành.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái khóa học thành completed thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi thay đổi trạng thái khóa học: ' . $e->getMessage(),
            ], 500);
        }
    }
}
