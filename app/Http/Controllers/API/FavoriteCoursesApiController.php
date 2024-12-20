<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Favorite_Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class FavoriteCoursesApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user_id = auth('api')->user()->id;
            $Favorite_Course_id = Favorite_Course::where('user_id', $user_id)->where('del_flag', true)->pluck('course_id');
            if ($Favorite_Course_id->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy khóa học yêu thích nào cho người dùng này.'], 404);
            }
            $courses = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ])->whereIn('id', $Favorite_Course_id)
                ->get();
            if ($courses->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy khóa học nào cho ID khóa học yêu thích nhất định.'], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' =>  CourseResource::collection($courses),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500); // Trả về mã lỗi 500 cho lỗi server
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
    // thêm khóa học yêu thích
    public function store(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|string|exists:courses,id',

            ], [
                'course_id.required' => 'Chưa có course_id',
                'course_id.string' => 'Không đúng định dạng course_id',
                'course_id.exists' => 'Không đúng định dạng course_id',
            ]);

            $user = auth('api')->user()->id;
            $favoriteCourse = Favorite_Course::where('course_id', $request->course_id)
                ->where('user_id', $user)
                ->first();

            if ($favoriteCourse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Khóa học này đã có trong danh sách yêu thích của bạn.'
                ], 400); // 400: Bad Request
            }
            $favoriteCourses = Favorite_Course::create([
                'user_id' => $user,
                'course_id' => $request->course_id,
                'del_flag' => true,
            ]);
            return response()->json(['message' => 'Thêm khóa học yêu thích thành công.', 'FavoriteCourse' => $favoriteCourses], 201);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500); // Trả về mã lỗi 500 cho lỗi server
        }
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

        try {
            $courseId = Course::where('id', $id)->select('id')->first()->id;
            $user_id = auth('api')->user()->id;
            $favoriteCourses = Favorite_Course::where('course_id', $courseId)->where('user_id', $user_id)->first();
            $favoriteCourses->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa khóa học yêu thích thành công',
                'data' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bài viết không được tìm thấy.',
            ], 404); // Trả về mã lỗi 404
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
