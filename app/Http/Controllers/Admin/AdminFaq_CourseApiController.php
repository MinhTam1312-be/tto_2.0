<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminFaq_CourseResource;
use App\Models\Activity_History;
use App\Models\Course;
use App\Models\FAQ_Course;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminFaq_CourseApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user; // Định nghĩa thuộc tính cho class

    public function __construct()
    {
        $this->user = auth('api')->user(); // Khởi tạo thuộc tính trong constructor
    }
    private function logActivity($activityName, $description, $status)
    {
        Activity_History::create([
            'name_activity' => $activityName,
            'discription_activity' => $this->user->fullname . ': ' . $description . ' ' . $this->user->role,
            'status_activity' => $status,
            'user_id' => $this->user->id
        ]);
    }
    public function index()
    {
        try {
            $faq_courses = FAQ_Course::get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminFaq_CourseResource::collection($faq_courses),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
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
        try {
            // Xác thực các trường dữ liệu
            $validatedData = $request->validate([
                'question' => 'required|string|max:255',
                'answer' => 'required|string|max:255',
                'course_id' => 'required|exists:courses,id'
            ], [
                'question.required' => 'Vui lòng nhập câu hỏi thường gặp',
                'question.string' => 'Câu hỏi học phải là chuỗi ký tự',
                'question.max' => 'Câu hỏi không được vượt quá 255 ký tự',
                'answer.required' => 'Vui lòng nhập câu trả lời',
                'answer.string' => 'Câu trả lời phải là chuỗi ký tự',
                'answer.max' => 'Câu trả lời không được vượt quá 255 ký tự',
                'course_id.required' => 'ID khóa học là bắt buộc.',
                'course_id.exists' => 'ID khóa học không tồn tại.'
            ]);

            // Lấy thông tin khóa học từ course_id
            $course = Course::find($validatedData['course_id']);
            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học với ID này.',
                    'data' => null,
                ], 404);
            }

            // Tạo FAQ khóa học mới
            $faq = FAQ_Course::create([
                'question_faq' => $validatedData['question'],
                'answer_faq' => $validatedData['answer'],
                'course_id' => $validatedData['course_id'],
                'del_flag' => true
            ]);

            // Ghi log khi thêm FAQ thành công
            LogActivityService::log(
                'thao_tac_them_faq',
                "Đã thêm FAQ cho khóa học '{$course->name_course}' với câu hỏi: '{$faq->question_faq}' và câu trả lời: '{$faq->answer_faq}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Khóa học được tạo thành công.',
                'data' => new AdminFaq_CourseResource($faq),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi thất bại
            LogActivityService::log(
                'thao_tac_them_faq',
                'Thêm FAQ thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Kiểm tra tính hợp lệ của $course_id
            if (!Str::isUlid($id)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ',
                    'data' => null,
                ], 400);
            }

            $faq_courses = FAQ_Course::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new AdminFaq_CourseResource($faq_courses),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Xác thực các trường dữ liệu
            $validatedData = $request->validate([
                'question' => 'required|string|max:255',
                'answer' => 'required|string|max:255',
                'course_id' => 'required|exists:courses,id'
            ], [
                'question.required' => 'Vui lòng nhập câu hỏi thường gặp',
                'question.string' => 'Câu hỏi phải là chuỗi ký tự',
                'question.max' => 'Câu hỏi không được vượt quá 255 ký tự',
                'answer.required' => 'Vui lòng nhập câu trả lời',
                'answer.string' => 'Câu trả lời phải là chuỗi ký tự',
                'answer.max' => 'Câu trả lời không được vượt quá 255 ký tự',
                'course_id.required' => 'ID khóa học là bắt buộc.',
                'course_id.exists' => 'ID khóa học không tồn tại.'
            ]);

            // Tìm bản ghi cần cập nhật
            $faqCourse = FAQ_Course::find($id);

            if (!$faqCourse) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy câu hỏi thường gặp.',
                    'data' => null,
                ], 404);
            }

            // Lưu trữ các thay đổi trước khi cập nhật
            $changes = [];

            // Kiểm tra nếu câu hỏi thay đổi
            if ($faqCourse->question_faq != $validatedData['question']) {
                $changes[] = [
                    'field' => 'question_faq',
                    'old_value' => $faqCourse->question_faq,
                    'new_value' => $validatedData['question'],
                ];
            }

            // Kiểm tra nếu câu trả lời thay đổi
            if ($faqCourse->answer_faq != $validatedData['answer']) {
                $changes[] = [
                    'field' => 'answer_faq',
                    'old_value' => $faqCourse->answer_faq,
                    'new_value' => $validatedData['answer'],
                ];
            }

            // Cập nhật dữ liệu
            $faqCourse->update([
                'question_faq' => $validatedData['question'],
                'answer_faq' => $validatedData['answer'],
                'course_id' => $validatedData['course_id']
            ]);

            // Ghi log các thay đổi
            foreach ($changes as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_faq',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho FAQ của khóa học '{$faqCourse->course->name_course}'.",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Câu hỏi thường gặp được cập nhật thành công.',
                'data' => new AdminFaq_CourseResource($faqCourse),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu.',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi thất bại
            LogActivityService::log(
                'thao_tac_cap_nhat_faq',
                'Cập nhật FAQ thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Ẩn, hiện các câu hỏi thường gặp
    public function statusFaqCourse($faq_course_id)
    {
        try {
            // Tìm câu hỏi thường gặp theo ID, nếu không có sẽ throw ModelNotFoundException
            $faq_course = FAQ_Course::findOrFail($faq_course_id);

            // Lưu trữ trạng thái cũ và mới trước khi thay đổi
            $old_del_flag = $faq_course->del_flag;
            $new_del_flag = !$faq_course->del_flag;

            // Cập nhật trạng thái del_flag (ẩn hoặc hiện câu hỏi)
            $faq_course->update(['del_flag' => $new_del_flag]);

            // Ghi log thay đổi trạng thái
            LogActivityService::log(
                'thao_tac_thay_doi_trang_thai_faq',
                "Đã thay đổi trạng thái 'del_flag' từ '" . ($old_del_flag ? 'hiện' : 'ẩn') . "' thành '" . ($new_del_flag ? 'hiện' : 'ẩn') . "' cho câu hỏi thường gặp của khóa học '{$faq_course->course->name_course}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái câu hỏi thường gặp của khóa học thành công',
                'data' => new AdminFaq_CourseResource($faq_course),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'thao_tac_thay_doi_trang_thai_faq',
                'Thay đổi trạng thái FAQ thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    // Gọi ra các chương thuộc khóa học
    public function getFaqByCourse($course_id)
    {
        try {
            // Tìm khóa học dựa trên course_id
            $course = Course::find($course_id);

            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học với ID đã cho.',
                    'data' => null,
                ], 404);
            }

            // Lấy các chương thuộc khóa học
            $faq_courses = $course->faq_courses()->get();

            if ($faq_courses->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có câu hỏi thường gặp nào thuộc khóa học này.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $faq_courses,
            ], 200);

        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
