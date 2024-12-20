<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\RouteResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class RouteApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $routes = Route::where('del_flag', true)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Lộ trình được lấy thành công',
                'data' => RouteResource::collection($routes),
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
        //
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $route = Route::findOrFail($id);

            $course = Course::whereHas('modules', function ($query) use ($route) {
                $query->where('route_id', $route->id);
            })
                ->where('del_flag', true)
                ->whereIn('status_course', ['confirming', 'success'])
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => CourseResource::collection($course),
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
    public function detailRoute()
    {
        try {
            // Lấy tất cả các routes còn hiệu lực (del_flag = true)
            $routes = Route::where('del_flag', true)->get();

            // Khởi tạo mảng để chứa kết quả
            $routesDetails = $routes->map(function ($route) {
                // Lấy danh sách các courses từ các modules của từng route, kiểm tra del_flag của modules và courses
                $courses = Course::whereHas('modules', function ($query) use ($route) {
                    $query->where('route_id', $route->id)
                        ->where('del_flag', true); // Lọc các module có del_flag = true
                })->where('del_flag', true) // Lọc các course có del_flag = true
                    ->withCount([
                        'chapters as num_chapter',
                        'documents as num_document'
                    ]) // Lấy thêm count của chapters và documents
                    ->get();

                // Định dạng lại courses
                $detailsRoute = $courses->map(function ($course) {
                    return [
                        'course_id' => $course->id,
                        'name_course' => $course->name_course,
                        'instructor_id' => $course->user_id,
                    ];
                });

                return [
                    'route_id' => $route->id,
                    'name_route' => $route->name_route,
                    'img_route' => $route->img_route,
                    'description_route' => $route->discription_route,
                    'courses' => $detailsRoute, // Thêm courses vào route
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $routesDetails, // Trả về tất cả các route
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function routeDetail($route_id)
    {
        try {
            // Tìm khóa học với id và kiểm tra điều kiện del_flag
            $route = Route::where('del_flag', true)
                ->findOrFail($route_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Lộ trình được lấy thành công',
                'data' => new RouteResource($route),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function detailByRouteId($route_id)
    {
        try {
            // Kiểm tra route_id
            if (!Route::where('id', $route_id)->where('del_flag', false)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ hoặc khóa học đã bị xóa',
                    'data' => null,
                ], 400);
            }

            // Tìm route theo ID được truyền vào
            $route = Route::findOrFail($route_id);

            // Lấy danh sách các courses từ các modules của route này, kiểm tra del_flag của modules và courses
            $courses = Course::whereHas('modules', function ($query) use ($route) {
                $query->where('route_id', $route->id)
                    ->where('del_flag', true); // Lọc các module có del_flag = true
            })
                ->where('del_flag', true) // Lọc các course có del_flag = true
                ->withCount([
                    'chapters as num_chapter',
                    'documents as num_document'
                ]) // Lấy thêm count của chapters và documents
                ->get();

            // Định dạng lại courses
            $detailsRoute = $courses->map(function ($course) {
                return [
                    'course_id' => $course->id,
                    'name_course' => $course->name_course,
                    'img_course' => $course->img_course,
                    'price_course' => $course->price_course,
                    'discount_price_course' => $course->discount_price_course,
                    'status_course' => $course->status_course,
                    'views_course' => $course->views_course,
                    'rating_course' => $course->rating_course,
                    'num_document' => $course->documents_count,
                    'num_chapter' => $course->chapters_count,
                    'tax_rate' => $course->tax_rate,
                    'del_flag' => $course->del_flag,
                    'instructor_id' => $course->user_id,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ];
            });

            // Định dạng lại route
            $routeDetails = [
                'route_id' => $route->id,
                'name_route' => $route->name_route,
                'img_route' => $route->img_route,
                'description_route' => $route->discription_route,
                'courses' => $detailsRoute, // Thêm courses vào route
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $routeDetails, // Trả về chi tiết route
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function addCourseInRoute(Request $request)
    {
        try {

            $request->validate([
                'name_route' => 'required|string|max:255',
                'description_route' => 'nullable|string',
                'img_route' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'course_ids' => 'required',
                'course_ids.*' => 'exists:courses,id',
            ]);
            $course_ids = $request->course_ids;
            if (!is_array($course_ids)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Lỗi xác thực dữ liệu',
                    'errors' => [
                        'course_ids' => ['The course_ids field must be an array.']
                    ]
                ], 422);
            }

            if ($request->hasFile('img_route')) {
                $file = $request->file('img_route');

                // Upload ảnh lên Cloudinary
                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'routes',
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ])->getSecurePath();
            }
            // Tạo lộ trình mới
            $route = Route::create([
                'name_route' => $request->name_route,
                'discription_route' => $request->discription_route,
                'img_route' => $uploadedFileUrl,
                'del_flag' => true, // hoặc giá trị mặc định của `status`
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Lấy danh sách các course_id từ yêu cầu
            $courseIds = $course_ids;

            // Khởi tạo một mảng để chứa các `module_id` sau khi tạo
            $moduleIds = [];

            // Tạo bản ghi trong bảng Modules cho từng khóa học
            foreach ($courseIds as $courseId) {
                $module = Module::create([
                    'route_id' => $route->id,
                    'course_id' => $courseId,
                    'del_flag' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Lưu `module_id` vào mảng
                $moduleIds[] = $module->id;
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Lộ trình đã được tạo thành công',
                'data' => $route,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Lấy ra các khóa học thuộc lộ trình với đkien khóa học đã đăng ký
    public function courseByRoute($route_id)
    {
        try {
            // Kiểm tra route_id hợp lệ
            $route = Route::where('id', $route_id)->where('del_flag', true)->first();
            if (!$route) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Lộ trình không tồn tại hoặc đã bị xóa.',
                    'data' => null,
                ], 404);
            }

            // Lấy thông tin người dùng đăng nhập
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Lấy các module liên kết với route
            $modules = Module::where('route_id', $route->id)
                ->where('del_flag', true)
                ->get();

            if ($modules->isEmpty()) {
                return response()->json(['message' => 'Không có module nào trong lộ trình này.'], 404);
            }

            // Lấy tất cả các khóa học liên quan đến lộ trình
            $courseIds = $modules->pluck('course_id');
            $courses = Course::whereIn('id', $courseIds)->get();

            // Lấy tất cả các module mà user đã đăng ký (Enrollment)
            $userModules = Enrollment::where('user_id', $user->id)
                ->pluck('module_id');

            // Lấy tất cả các module liên quan đến user và khóa học hiện tại
            $relatedModules = Module::whereIn('id', $userModules)
                ->whereIn('course_id', $courseIds)
                ->pluck('id');

            // Lấy tất cả các enrollment liên quan đến user và module liên quan
            $enrollments = Enrollment::whereIn('module_id', $relatedModules)
                ->where('user_id', $user->id)
                ->get();

            // Ánh xạ thông tin enrollment cho từng khóa học
            $courses = $courses->map(function ($course) use ($enrollments, $relatedModules) {
                // Tìm enrollment liên quan đến khóa học qua các module
                $moduleIds = Module::where('course_id', $course->id)->pluck('id');
                $enrollment = $enrollments->firstWhere('module_id', fn($module_id) => $moduleIds->contains($module_id));

                if ($enrollment) {
                    $course->is_enrolled = true;
                    $course->status_course = $enrollment->status_course;
                } else {
                    $course->is_enrolled = false;
                    $course->status_course = null;
                }

                return $course;
            });

            if ($courses->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy khóa học cho lộ trình này.'], 404);
            }

            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy danh sách khóa học thành công.',
                'data' => $courses,
            ], 200);
        } catch (\Exception $e) {
            // Nếu lỗi xảy ra, trả về thông báo lỗi
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }
    public function getCoursesByRouteClient($route_id)
    {
        try {
            $user_id = auth('api')->user()->id;

            // Lấy tất cả các khóa học trong lộ trình
            $courses = Course::whereHas('modules.route', function ($query) use ($route_id) {
                $query->where('routes.id', $route_id);
            })
                ->where('courses.del_flag', true)
                ->where('courses.status_course', 'success')
                ->with([
                    'chapters' => function ($query) {
                        $query->with(['documents']); // Lấy tất cả tài liệu không kiểm tra del_flag
                    },
                    'user' // Thông tin giảng viên
                ])
                ->get();

            // Lấy danh sách các khóa học người dùng đã đăng ký
            $enrollments = Enrollment::where('user_id', $user_id)
                ->where('enroll', true)
                ->where('del_flag', true)
                ->with(['module.course.chapters.documents', 'status_docs'])
                ->get();

            // Tạo một danh sách các khóa học đã đăng ký với `progress_percentage`
            $enrollmentData = $enrollments->mapWithKeys(function ($enrollment) {
                $course = $enrollment->module->course;

                // Đếm số video đã xem
                $watchedVideos = $enrollment->status_docs()->where('status_doc', true)->count();

                // Đếm tổng số tài liệu trong khóa học
                $numDocuments = $course->chapters->flatMap(function ($chapter) {
                    return $chapter->documents;
                })->count();

                // Tính toán phần trăm tiến độ
                $progressPercentage = $numDocuments > 0 ? round(($watchedVideos / $numDocuments) * 100, 1) : 0;

                return [$course->id => $progressPercentage, 'status_course_enrollment' => $enrollment];
            });

            // Kết hợp tất cả khóa học và thêm `progress_percentage` nếu có
            $result = $courses->map(function ($course) use ($enrollmentData) {
                $statusCourseEnrollment = $enrollmentData->has($course->id)
                    ? $enrollmentData['status_course_enrollment']['status_course']
                    : null;
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
                    'num_document' => $course->chapters->flatMap(fn($chapter) => $chapter->documents)->count(),
                    'num_chapter' => $course->chapters()->count(),
                    'status_course_enrollment' => $statusCourseEnrollment,
                    'instructor_id' => $course->user_id,
                    'instructor_name' => $course->user->fullname,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    // Thêm progress_percentage nếu đã đăng ký, nếu chưa thì null
                    'progress_percentage' => $enrollmentData->get($course->id, null),
                ];
            });

            return response()->json([
                'data' => $result->values(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
