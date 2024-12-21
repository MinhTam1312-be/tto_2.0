<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminChapterResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ChaptersInstructorApiController extends Controller
{
    /**
     * Display a listing of the reso   urce.
     */
    public function index()
    {
        try {
            $user = auth('api')->user();

            $chapters = Chapter::with('course')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminChapterResource::collection($chapters),
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
            $user = auth('api')->user();

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'course_id' => 'required|exists:courses,id',
                'serial_chapter' => 'required|numeric|integer',
                'name_chapter' => 'required|string|max:255',
            ], [
                'course_id.required' => 'ID khóa học là bắt buộc.',
                'course_id.exists' => 'ID khóa học không tồn tại.',
                'serial_chapter.required' => 'Thứ tự chương là bắt buộc.',
                'serial_chapter.numeric' => 'Thứ tự chương phải là số.',
                'serial_chapter.integer' => 'Thứ tự chương phải là số nguyên.',
                'name_chapter.required' => 'Tên chương là bắt buộc.',
                'name_chapter.string' => 'Tên chương phải là chuỗi ký tự.',
                'name_chapter.max' => 'Tên chương không được vượt quá 255 ký tự.',
            ]);

            // Kiểm tra tồn tại của khóa học theo course_id
            $course = Course::find($request->input('course_id'));

            if (!$course) {
                return response()->json(['message' => 'Không tìm thấy khóa học với ID này.'], 404);
            }

            // Xác định giá trị của del_flag dựa trên trạng thái của khóa học
            $delFlag = Course::where('id', $request->input('course_id'))
                ->where('status_course', 'success')
                ->exists() ? false : true;

            // Tạo một chương mới
            $chapter = new Chapter([
                'name_chapter' => $request->input('name_chapter'),
                'serial_chapter' => $request->input('serial_chapter'),
                'course_id' => $course->id,
                'del_flag' => $delFlag,
            ]);
            $chapter->save();

            // Ghi log thông tin tạo chương mới
            LogActivityService::log(
                'them_chuong_khoa_hoc',
                "Đã thêm chương '{$chapter->name_chapter}' với thứ tự '{$chapter->serial_chapter}' vào khóa học '{$course->name_course}'.",
                'success'
            );

            return response()->json([
                'message' => 'Chương đã được thêm thành công.',
                'chapter' => $chapter
            ], 201);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'them_chuong_khoa_hoc',
                'Thêm chương thất bại: ' . $e->getMessage(),
                'fail'
            );

            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi thêm chương.', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = auth('api')->user();

            $chapter = Chapter::with('course')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new AdminChapterResource($chapter),
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Xử lý lỗi không tìm thấy ID
            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy chapter với ID được cung cấp',
                'data' => null,
            ], 404);
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth('api')->user();

            // Kiểm tra đăng nhập
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'course_id' => 'required|exists:courses,id',
                'serial_chapter' => 'required|numeric|integer',
                'name_chapter' => 'required|string|max:255',
            ], [
                'course_id.required' => 'ID khóa học là bắt buộc.',
                'course_id.exists' => 'ID khóa học không tồn tại.',
                'serial_chapter.required' => 'Thứ tự chương là bắt buộc.',
                'serial_chapter.numeric' => 'Thứ tự chương phải là số.',
                'serial_chapter.integer' => 'Thứ tự chương phải là số nguyên.',
                'name_chapter.required' => 'Tên chương là bắt buộc.',
                'name_chapter.string' => 'Tên chương phải là chuỗi ký tự.',
                'name_chapter.max' => 'Tên chương không được vượt quá 255 ký tự.',
            ]);

            // Tìm chương cần cập nhật
            $chapter = Chapter::find($id);

            if (!$chapter) {
                return response()->json(['message' => 'Không tìm thấy chương với ID này.'], 404);
            }

            // Cập nhật thông tin chương
            $chapter->name_chapter = $request->input('name_chapter');
            $chapter->serial_chapter = $request->input('serial_chapter');
            $chapter->course_id = $request->input('course_id'); // Cập nhật ID khóa học nếu cần
            $chapter->save();

            // Ghi log thông tin cập nhật chương
            LogActivityService::log(
                'cap_nhat_chuong_khoa_hoc',
                "Đã cập nhật chương '{$chapter->name_chapter}' (thứ tự: {$chapter->serial_chapter}) trong khóa học '{$chapter->course->name_course}'.",
                'success'
            );

            return response()->json([
                'message' => 'Chương đã được cập nhật thành công.',
                'chapter' => $chapter
            ], 200);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'cap_nhat_chuong_khoa_hoc',
                'Cập nhật chương thất bại: ' . $e->getMessage(),
                'fail'
            );

            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật chương.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Gọi ra các chương thuộc khóa học
    public function getChaptersByCourse($course_id)
    {
        try {
            // Tìm khóa học dựa trên course_id
            $course = Course::findOrFail($course_id);

            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy khóa học với ID đã cho.',
                    'data' => null,
                ], 404);
            }
            // $ = $course->name_course;
            // Lấy các chương thuộc khóa học
            $chapters = $course->chapters()->orderBy('serial_chapter', 'asc')->get();

            if ($chapters->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có chương nào thuộc khóa học này.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'course_id' => $course->id,
                'name_course' => $course->name_course,
                'data' => $chapters,
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

    // Ẩn, hiện các chương
    public function statusChapter($chapter_id)
    {
        try {
            // Tìm chương theo ID, nếu không có sẽ throw ModelNotFoundException
            $chapter = Chapter::findOrFail($chapter_id);

            // Cập nhật trạng thái del_flag (ẩn hoặc hiện chương)
            $chapter->update(['del_flag' => !$chapter->del_flag]);

            // Ghi log trạng thái thay đổi thành công
            LogActivityService::log(
                'thay_doi_trang_thai_chuong',
                "Đã thay đổi trạng thái chương '{$chapter->name_chapter}' trong khóa học '{$chapter->course->name_course}' thành " . ($chapter->del_flag ? 'hiện' : 'ẩn') . ".",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái chương thành công.',
                'data' => new AdminChapterResource($chapter),
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
                'thay_doi_trang_thai_chuong',
                'Thay đổi trạng thái chương thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Lấy id khóa học đếm chapter đếm chi tiết chapter trong doc
    public function getCountChapterAndDoc($course_id)
    {
        try {
            $course = Course::with('chapters.documents')->findOrFail($course_id);

            $courseData = [
                'course_id' => $course->id,
                'count_chapter' => $course->chapters->count(),
                'chapters' => $course->chapters->map(fn($chapter) => [
                    'chapter_id' => $chapter->id,
                    'document_count' => $chapter->documents->count(),
                ])
            ];

            // Trả về kết quả dưới dạng JSON
            return response()->json([
                'success' => true,
                'data' => $courseData,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
}
