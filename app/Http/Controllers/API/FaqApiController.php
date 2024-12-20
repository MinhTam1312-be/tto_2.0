<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FAQ_Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqApiController extends Controller
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

    public function faqByCourseId($course_id, $limit)
    {
        try {
            // Kiểm tra tính hợp lệ của limitlimit
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số limit không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Lấy các FAQ liên quan đến khóa học mà del_flag là true
            $faqs = FAQ_Course::whereHas('course', function ($query) use ($course_id) {
                $query->where('id', $course_id)
                    ->where('del_flag', true); // Lọc theo course_id và del_flag
            })
                ->where('del_flag', true) // Kiểm tra del_flag cho FAQ_Course
                ->limit($limit) // Giới hạn số lượng bản ghi
                ->get();

            // Kiểm tra nếu không có dữ liệu
            if ($faqs->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có FAQ nào cho khóa học này',
                    'data' => null,
                ], 404);
            }

            // Xử lý kết quả
            $result = $faqs->map(function ($faq) {
                return [
                    'question_faq' => $faq->question_faq,
                    'answer_faq' => $faq->answer_faq,
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
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500); // Trả về mã lỗi 500 cho lỗi server
        }
    }
}
