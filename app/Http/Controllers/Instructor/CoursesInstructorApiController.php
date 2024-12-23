<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCourseResource;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Document;
use App\Models\Module;
use App\Models\Route;
use App\Services\LogActivityService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CoursesInstructorApiController extends Controller
{
    // CÁC CHỨC NĂNG QUẢN LÝ KHÓA HỌC CỦA INSTRUCTOR
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth('api')->user();
            if ($user->role == 'admin') {
                $courses = Course::with('user')->get();
            } else {
                $courses = Course::with('user')->where('user_id', $user->id)->get();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminCourseResource::collection($courses),
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
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'name_course' => 'required|string|max:255',
                'discription_course' => 'required',
                'img_course' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'price_course' => 'required|numeric|min:0',
                'discount_price_course' => 'nullable|numeric|min:0|max:100',
                'tax_rate' => 'required|numeric|min:0|max:100',
                'route_id' => 'required|array',
                'route_id.*' => 'exists:routes,id',
            ], [
                'route_id.required' => 'Vui lòng chọn lộ trình.',
                'route_id.exists' => 'Lộ trình không tồn tại.',
                'route_id.array' => 'Lộ trình phải là một danh sách.',
            ]);

            // Upload ảnh lên Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('img_course')->getRealPath(), [
                'folder' => 'courses',
                'public_id' => pathinfo($request->file('img_course')->getClientOriginalName(), PATHINFO_FILENAME)
            ])->getSecurePath();

            // Tạo khóa học mới
            $course = Course::create([
                'name_course' => $validatedData['name_course'],
                'discription_course' => $validatedData['discription_course'],
                'img_course' => $uploadedFileUrl,
                'price_course' => $validatedData['price_course'],
                'discount_price_course' => $validatedData['discount_price_course'] ?? 0,
                'tax_rate' => $validatedData['tax_rate'],
                'status_course' => 'confirming',
                'del_flag' => true,
                'views_course' => 0,
                'rating_course' => 0,
                'user_id' => $user->id,
            ]);

            // Truy xuất tên lộ trình và gắn vào bảng module
            $routeNames = [];
            foreach ($validatedData['route_id'] as $routeId) {
                $route = Route::find($routeId);
                if ($route) {
                    $routeNames[] = $route->name_route;
                    Module::create([
                        'route_id' => $route->id,
                        'course_id' => $course->id,
                        'del_flag' => true,
                    ]);
                }
            }

            // Ghi log chỉ bao gồm tên khóa học và tên lộ trình
            LogActivityService::log(
                'tao_khoa_hoc',
                "Khóa học '{$course->name_course}' đã được tạo thành công với các lộ trình: " . implode(', ', $routeNames),
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Khóa học được tạo thành công.',
                'data' => new AdminCourseResource($course),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ghi log lỗi xác thực
            LogActivityService::log(
                'tao_khoa_hoc',
                "Lỗi xác thực khi tạo khóa học: " . json_encode($e->errors(), JSON_UNESCAPED_UNICODE),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log lỗi hệ thống
            LogActivityService::log(
                'tao_khoa_hoc',
                "Đã xảy ra lỗi khi tạo khóa học: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi trong quá trình tạo khóa học.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $course = Course::findOrFail($id);

            $route_id = Module::where('course_id', $course->id)->pluck('route_id');

            $course->route_id = $route_id;

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $course,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
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

            // Tìm khóa học
            $course = Course::find($id);
            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học không tồn tại.',
                    'data' => null,
                ], 404);
            }

            // Xác thực dữ liệu
            $validatedData = $request->validate([
                'name_course' => 'string|max:255',
                'discription_course' => 'string',
                'price_course' => 'numeric|min:0',
                'discount_price_course' => 'numeric|min:0',
                'tax_rate' => 'numeric|min:0|max:100',
                'del_flag' => 'nullable|boolean',
                'route_id' => 'array',
                'route_id.*' => 'exists:routes,id',
            ], [
                'name_course.required' => 'Vui lòng nhập tên khóa học',
                'name_course.string' => 'Tên khóa học phải là chuỗi ký tự',
                'name_course.max' => 'Tên khóa học không được vượt quá 255 ký tự',
                'discription_course.string' => 'Mô tả khóa học phải là chuỗi ký tự',
                'price_course.required' => 'Vui lòng nhập giá gốc của khóa học',
                'price_course.numeric' => 'Giá gốc phải là một số',
                'price_course.min' => 'Giá gốc không được nhỏ hơn 0',
                'discount_price_course.numeric' => 'Giá khuyến mãi phải là một số',
                'discount_price_course.min' => 'Giá khuyến mãi không được nhỏ hơn 0',
                'discount_price_course.max' => 'Giá khuyến mãi không được vượt quá 100',
                'tax_rate.required' => 'Vui lòng nhập thuế suất của khóa học',
                'tax_rate.numeric' => 'Thuế suất phải là một số',
                'tax_rate.min' => 'Thuế suất không được nhỏ hơn 0',
                'tax_rate.max' => 'Thuế suất không được vượt quá 100',
                'del_flag.boolean' => 'Del_flag phải là giá trị đúng/sai',
                'route_id.exists' => 'Lộ trình không tồn tại.',
                'route_id.array' => 'Lộ trình phải là một danh sách.',
            ]);

            // Kiểm tra và xử lý tệp ảnh nếu có thay đổi
            if ($request->hasFile('img_course')) {
                $file = $request->file('img_course');

                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'courses', // Lưu ảnh vào thư mục 'courses' trên Cloudinary
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ])->getSecurePath();

                $course->img_course = $uploadedFileUrl;
            }
            if (!empty($validatedData['route_id'])) {
                $module = Module::whereIn('route_id', $validatedData['route_id'])->where('course_id', $id)->update(['del_flag' => false]);
                foreach ($validatedData['route_id'] as $routeId) {
                    Module::create([
                        'route_id' => $routeId, // ID của lộ trình
                        'course_id' => $course->id,
                        'del_flag' => true
                    ]);
                }
                $course->fill($validatedData);
                $course->save();
            }
            // Cập nhật các trường dữ liệu khác từ request
            $course->fill($validatedData);
            $course->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Khóa học được cập nhật thành công.',
                'data' => new AdminCourseResource($course),
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

    // Trạng thái khóa học (ẩn, hiện)
    public function statusCourse($course_id)
    {
        try {
            // Tìm khóa học theo ID, nếu không tìm thấy sẽ throw ModelNotFoundException
            $course = Course::findOrFail($course_id);

            // Thay đổi trạng thái del_flag (ẩn/hiện)
            $course->update(['del_flag' => !$course->del_flag]);

            // Ghi log thành công
            $status = $course->del_flag ? 'hiện' : 'ẩn';  // Kiểm tra trạng thái mới (true là hiện, false là ẩn)
            LogActivityService::log(
                'thay_doi_trang_thai_khoa_hoc',
                "Đã thay đổi trạng thái khóa học '{$course->name_course}' sang trạng thái '$status'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái khóa học thành công',
                'data' => new AdminCourseResource($course),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {


            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy khóa học',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi khác
            LogActivityService::log(
                'thay_doi_trang_thai_khoa_hoc',
                "Thay đổi trạng thái thất bại cho khóa học '{$course->name_course}': " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi thay đổi trạng thái khóa học',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Lấy ra các bài học từ course
    public function docForUser($course_id)
    {
        try {
            auth('api')->user();

            // Kiểm tra tính hợp lệ của $course_id
            if (!Str::isUlid($course_id)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Kiểm tra tồn tại của khóa học và del_flag
            $course = Course::where('id', $course_id)->first();

            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học không tồn tại hoặc đã bị xóa.',
                    'data' => null,
                ], 404);
            }

            // Lấy các chapter của khóa học
            $chapters = Chapter::where('course_id', $course_id)->get();

            if ($chapters->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có chương nào thuộc khóa học này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $chapterData = $chapters->map(function ($chapter) {
                $documents = Document::select('id', 'name_document', 'type_document', 'updated_at')
                    ->where('chapter_id', $chapter->id)
                    ->get();

                $documentData = $documents->map(function ($document) {
                    return [
                        'document_id' => $document->id,
                        'name_document' => $document->name_document,
                        'type_document' => $document->type_document,
                        'updated_at' => $document->updated_at,
                    ];
                });

                return [
                    'chapter_id' => $chapter->id,
                    'chapter_name' => $chapter->name_chapter,
                    'documents' => $documentData,
                ];
            });

            return response()->json([
                'data' => $chapterData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Tìm kiếm khóa học
    public function searchNameCourse(Request $request)
    {
        $searchTerm = $request->input('search');

        // Kiểm tra chuỗi tìm kiếm có tồn tại hay không
        if (empty($searchTerm)) {
            return response()->json([
                'error' => 'Chuỗi tìm kiếm không được để trống.'
            ], 400); // 400 Bad Request
        }

        try {
            // Tìm kiếm khóa học, bỏ qua phân biệt chữ hoa và chữ thường
            $filterCourse = Course::whereRaw('LOWER(name_course) LIKE ?', ['%' . strtolower($searchTerm) . '%'])->get();

            // Kiểm tra nếu không tìm thấy khóa học nào
            if ($filterCourse->isEmpty()) {
                return response()->json([
                    'message' => 'Không tìm thấy khóa học nào phù hợp.'
                ], 404); // 404 Not Found
            }

            return response()->json($filterCourse, 200); // Trả về danh sách khóa học nếu tìm thấy
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm: ' . $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
}
