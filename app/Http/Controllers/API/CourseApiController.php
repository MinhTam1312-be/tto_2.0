<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminStatus_DocResource;
use App\Http\Resources\CourseResource;
use App\Models\Chapter;
use App\Models\Code;
use App\Models\Course;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\FAQ_Course;
use App\Models\Module;
use App\Models\Post;
use App\Models\Question;
use Exception;
use App\Models\Route;
use App\Models\Status_Doc;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Support\Str;

class CourseApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allCourseClient()
    {
        try {
            $courses = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ]) // Đếm số chapters và lessons
                ->with('chapters.documents')
                ->where('status_course', 'success')
                ->where('del_flag', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => CourseResource::collection($courses),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function index()
    {
        try {
            $courses = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ]) // Đếm số chapters và lessons
                ->with('chapters.documents') // Lấy các chapters cùng lessons lồng nhau
                ->where('status_course', 'success')
                ->where('status_course', 'confirming')
                ->where('del_flag', true)
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Các khóa học được lấy thành công thành công',
                'data' => CourseResource::collection($courses),
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
            // Tìm khóa học với id và kiểm tra điều kiện del_flag
            $course = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ]) // Đếm số chapters và lessons
                ->with('chapters.documents') // Lấy các chapters cùng lessons lồng nhau
                ->where('status_course', 'success')
                ->where('del_flag', true)
                ->find($id);

            // Kiểm tra nếu khóa học không tồn tại hoặc có del_flag là false
            if (!$course) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'ID không hợp lệ hoặc khóa học đã bị ẩn.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Khóa học được lấy thành công',
                'data' => new CourseResource($course),
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

    // Lọc khóa học theo lộ trình
    public function filterConditionCourse(Request $request)
    {
        // Lấy các tham số từ request
        $routeName = $request->input('route_name'); // Tên lộ trình
        $isPaid = filter_var($request->input('is_paid'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE); // Chuyển đổi giá trị is_paid thành boolean
        $limit = (int) $request->input('limit', 2); // Giới hạn số lượng kết quả, mặc định là 2

        try {
            // Tạo truy vấn để tìm kiếm khóa học với đếm chapters và documents
            $query = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ])
                ->where('del_flag', true); // Chỉ lấy các khóa học có del_flag là true

            // Nếu có tên lộ trình
            if (!empty($routeName)) {
                $query->whereHas('modules.route', function ($query) use ($routeName) {
                    $query->where('name_route', 'LIKE', '%' . $routeName . '%');
                });
            }

            // Nếu có điều kiện về phí
            if (isset($isPaid)) { // Kiểm tra nếu 'is_paid' không null
                $query->where('price_course', $isPaid ? '>' : '=', 0); // Khóa học có phí hoặc miễn phí
            }

            // Lấy các khóa học với giới hạn
            $courses = $query->limit($limit)->get();
            // Kiểm tra nếu không tìm thấy khóa học nào
            if ($courses->isEmpty()) {
                return response()->json([
                    'message' => 'Không tìm thấy khóa học nào phù hợp.'
                ], 404); // 404 Not Found
            }

            return response()->json(CourseResource::collection($courses), 200); // Trả về danh sách khóa học
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm: ' . $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    // Lọc khóa học theo free và pro
    public function coursePrice($price, $slug_route = null, $limit = null, )
    {
        try {
            $query = Course::with([
                'user:id,fullname,email' // Lấy thông tin avatar từ user
            ])->withCount(
                    'chapters as num_chapter',
                    'documents as num_document'
                );
            if ($price == 'pro') {
                $query->where('price_course', '>', 0);
            } else if ($price == 'free') {
                $query->where('price_course', '=', 0);
            }

            // Nếu $limit có giá trị thì áp dụng limit, không thì lấy hết
            if ($limit) {
                $query->limit($limit);
            }
            if ($slug_route == 'all') {
                $courses = $query->where('del_flag', true)
                    ->whereIn('status_course', ['success', 'confirming'])
                    ->orderByDesc('status_course')
                    ->get();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy dữ liệu thành công',
                    'data' => CourseResource::collection($courses),
                ], 200);
            } else if ($slug_route) {
                $routeId = Route::where('slug_route', $slug_route)->pluck('id')->first();
                $courseId = Module::where('route_id', $routeId)->pluck('course_id');
                $courses = $query->whereIn('id', $courseId)
                    ->where('del_flag', true)
                    ->whereIn('status_course', ['success', 'confirming'])
                    ->orderByDesc('status_course')
                    ->get();

                // Sử dụng CourseResource để format dữ liệu
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy dữ liệu có route thành công',
                    'data' => CourseResource::collection($courses),
                ], 200);
            }

            // Sử dụng CourseResource để format dữ liệu

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Tìm kiếm khóa học
    public function filterNameCourse(Request $request)
    {
        $searchTerm = $request->input('search');

        // Kiểm tra xem chuỗi tìm kiếm có tồn tại hay không
        if (empty($searchTerm)) {
            return response()->json([
                'error' => 'Chuỗi tìm kiếm không được để trống.'
            ], 400); // 400 Bad Request
        }

        try {
            // Tìm kiếm các mô-đun cùng với khóa học và điều kiện quan hệ
            $modules = Module::with(['course'])
                ->whereHas('route', function ($query) use ($searchTerm) {
                    $query->where('name_route', 'LIKE', "%$searchTerm%")
                        ->where('del_flag', true);
                })
                ->orWhereHas('course', function ($query) use ($searchTerm) {
                    $query->where('name_course', 'LIKE', "%$searchTerm%")
                        ->where('del_flag', true)->where('status_course', 'success');
                })
                ->get();

            // Nếu không có module nào phù hợp, kiểm tra các khóa học riêng
            if ($modules->isEmpty()) {
                $filterCourse = Course::withCount([
                    'chapters as num_chapter',
                    'documents as num_document'
                ]) // Đếm số lượng chapters và documents
                    ->where('name_course', 'LIKE', "%$searchTerm%")
                    ->where('del_flag', true) // Chỉ lấy các khóa học có del_flag là true
                    ->get();

                if ($filterCourse->isEmpty()) {
                    return response()->json([
                        'message' => 'Không tìm thấy lộ trình hoặc khóa học nào phù hợp.'
                    ], 404); // 404 Not Found
                }

                return response()->json($filterCourse, 200); // Trả về danh sách khóa học nếu tìm thấy
            }

            // Lấy tất cả các khóa học từ mô-đun
            $courses = $modules->pluck('course')->unique('id'); // Sử dụng Eloquent để loại bỏ khóa học trùng lặp

            return response()->json(CourseResource::collection($courses), 200);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm: ' . $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    // Lấy tên các chapter theo ID khóa học
    public function nameChapterByCourseId($course_id): JsonResponse
    {
        try {
            // Kiểm tra tính hợp lệ của $course_id
            if (!Str::isUlid($course_id) || !Course::where('id', $course_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ hoặc khóa học đã bị xóa',
                    'data' => null,
                ], 400);
            }

            // Tìm khóa học theo ID cùng với các chapter có del_flag là true
            $course = Course::with([
                'chapters' => function ($query) {
                    $query->orderBy('serial_chapter', 'asc')->where('del_flag', true);
                }
            ])->where('id', $course_id)->where('del_flag', true)->firstOrFail();

            // Tạo kết quả với thông tin khóa học và các tên chapter
            $result = [
                // Lấy danh sách tên các chapter
                'name_chapters' => $course->chapters->pluck('name_chapter')
            ];
            Course::where('id', $course_id)->increment('views_course');
            return response()->json([
                'course_id' => $course_id,
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch course chapters',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Lấy tất cả các bài học của khóa học
    public function docByCourseId($course_id)
    {
        try {
            $user_id = auth('api')->user()->id;

            // Kiểm tra xem $course_id có hợp lệ không
            if (!Str::isUlid($course_id) || !Course::where('id', $course_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Kiểm tra quyền truy cập khóa học dựa trên Enrollment và cờ del_flag
            $course = Course::whereHas('modules.enrollments', function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->where('enroll', true)
                    ->where('del_flag', true);
            })->where('id', $course_id)->where('del_flag', true)->first();

            if (is_null($course)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học không tồn tại hoặc bạn không có quyền truy cập.',
                    'data' => null,
                ], 403);
            }

            // Lấy các chapter của khóa học
            $chapters = Chapter::where('course_id', $course_id)
                ->where('del_flag', true)
                ->get();

            if ($chapters->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có chương nào thuộc khóa học này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $chapterData = Chapter::where('course_id', $course_id)
                ->where('del_flag', true)
                ->orderBy('serial_chapter', 'asc')
                ->with([
                    'documents' => function ($query) {
                        $query->select('id', 'chapter_id', 'name_document', 'discription_document', 'serial_document', 'url_video', 'type_document', 'del_flag', 'updated_at')
                            ->where('del_flag', true)
                            ->orderBy('serial_document', 'asc');
                    },
                    'documents.question', // Quan hệ 1-1 với Question
                    'documents.code',     // Quan hệ 1-1 với Code
                ])
                ->get()
                ->map(function ($chapter) use ($course_id) {
                    $user_id = auth('api')->user()->id;
                    $modules = Module::where('course_id', $course_id)->pluck('id');
                    $enrollment = Enrollment::where('user_id', $user_id)
                        ->whereIn('module_id', $modules)
                        ->where('del_flag', true)
                        ->select('id')
                        ->first();

                    $enrollment_id = $enrollment ? $enrollment->id : null;

                    return [
                        'chapter_id' => $chapter->id,
                        'chapter_name' => $chapter->name_chapter,
                        'documents' => $chapter->documents->map(function ($document) use ($enrollment_id) {
                            $statusDoc = Status_Doc::where('document_id', $document->id)
                                ->where('enrollment_id', $enrollment_id)
                                ->pluck('status_doc') // Truy xuất trực tiếp giá trị `status_doc`
                                ->first();

                            $documentDetails = [
                                'document_id' => $document->id,
                                'name_document' => $document->name_document,
                                'discription_document' => $document->discription_document,
                                'url_video' => $document->url_video,
                                'type_document' => $document->type_document,
                                'status_document' => $statusDoc ?? null,
                                'updated_at' => $document->updated_at,
                            ];

                            // Lấy dữ liệu liên quan dựa trên type_document
                            if ($document->type_document === 'quiz' && $document->question) {
                                $documentDetails['questions'] = [
                                    (object) [
                                        'id' => $document->question->id,
                                        'content_question' => $document->question->content_question,
                                        'type_question' => $document->question->type_question,
                                        'updated_at' => $document->question->updated_at,
                                    ]

                                ];
                            } elseif ($document->type_document === 'code' && $document->code) {
                                $documentDetails['codes'] = [
                                    'id' => $document->code->id,
                                    'question_code' => $document->code->question_code,
                                    'answer_code' => $document->code->answer_code,
                                    'tutorial_code' => $document->code->tutorial_code,
                                    'updated_at' => $document->code->updated_at,
                                ];
                            }

                            return $documentDetails;
                        }),
                    ];
                });
            return response()->json([
                'course_id' => $course_id,
                'data' => $chapterData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Lấy ra trạng thái giữa các bài học, tuần tự
    public function statusDocByDocument($document_id, $course_id)
    {
        try {
            $user_id = auth('api')->user()->id;

            // Kiểm tra tài liệu có thuộc khóa học không
            $document = Document::where('id', $document_id)
                ->whereHas('chapter.course', function ($query) use ($course_id) {
                    $query->where('id', $course_id);
                })
                ->first();

            if (!$document) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tài liệu không thuộc khóa học này hoặc không tồn tại.',
                    'data' => null,
                ], 404);
            }

            // Lấy danh sách module thuộc khóa học
            $modules = Module::where('course_id', $course_id)->pluck('id')->toArray();

            // Kiểm tra Enrollment
            $enrollment = Enrollment::where('user_id', $user_id)
                ->whereIn('module_id', $modules)
                ->where('del_flag', true)
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy thông tin ghi danh phù hợp.',
                    'data' => null,
                ], 404);
            }

            // Lấy trạng thái tài liệu
            $statusDoc = Status_Doc::where('del_flag', true)
                ->where('document_id', $document_id)
                ->where('enrollment_id', $enrollment->id)
                ->get();

            if ($statusDoc->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy trạng thái tài liệu.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $statusDoc
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    // Tạo trạng thái cho bài học khi dùng bấm vào
    public function createDocument($document_id, $course_id)
    {
        try {
            $user_id = auth('api')->user()->id;
            $modules = Module::where('course_id', $course_id)->pluck('id')->toArray();
            $Enrollment_id = Enrollment::where('user_id', $user_id)
                ->where('module_id', $modules)
                ->where('del_flag', true)
                ->select('id')
                ->first();
            // kiểm tra nếu như đã tồn tại rồi không càn tạo
            $status_doc = Status_Doc::firstOrCreate(
                [
                    'document_id' => $document_id,
                    'enrollment_id' => $Enrollment_id->id
                ],
                [
                    'status_doc' => false,
                    'cache_time_video' => 0,
                    'del_flag' => true
                ]
            );

            return response()->json([
                'data' => new AdminStatus_DocResource($status_doc)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Cập nhật trạng thái cho bài học
    public function updateStatusDocument(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'status_doc' => 'required|boolean',
                // 'cache_time_video' => '',
                'document_id' => 'required|exists:documents,id',
                'course_id' => 'required|exists:courses,id',
            ], [
                'status_doc.required' => 'Trạng thái không được bỏ trống',
                'status_doc.boolean' => 'Trạng thái phải là đúng hoặc sai',
                'cache_time_video.required' => 'Thời lượng video không được bỏ trống',
                'document_id.required' => 'Không được bỏ trống document_id',
                'document_id.exists' => 'không document_id',
                'course_id.required' => 'Enrollment không được bỏ trống',
                'course_id.exists' => 'Enrollment không được bỏ trống',
            ]);
            $modules = Module::where('course_id', $request->course_id)->pluck('id')->toArray();
            $Enrollment = Enrollment::where('user_id', $user_id)
                ->where('module_id', $modules)
                ->where('del_flag', true)
                ->select('id')
                ->first();
            $statusDoc = Status_Doc::where('enrollment_id', $Enrollment->id)->where('document_id', $request->document_id)->first();
            $statusDoc->update([
                'status_doc' => $request->status_doc,
                'cache_time_video' => $request->cache_time_video,
                'document_id' => $request->document_id,
                'enrollment_id' => $Enrollment->id,
                'del_flag' => true,
            ]);
            return response()->json([
                'message' => 'Đã cập nhật trạng thái thành công',
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
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


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
            $course = Course::where('id', $course_id)->where('del_flag', true)->first();

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
                'course_id: ' . $course_id => $chapterData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Hàm tìm kiếm khóa học lộ trình bài viết
    public function searchCourseRoutePost($searchTerm)
    {
        try {
            // Thực hiện tìm kiếm trên tất cả các bảng
            $routes = Route::where('name_route', 'ilike', '%' . $searchTerm . '%')->where('del_flag', true)->select('id', 'name_route as title', 'slug_route', 'img_route as image')->get();
            $courses = Course::where('name_course', 'ilike', '%' . $searchTerm . '%')->where('del_flag', true)->select('id', 'name_course as title', 'slug_course', 'img_course as image')->get();
            $posts = Post::where('title_post', 'ilike', '%' . $searchTerm . '%')->where('del_flag', true)->select('id', 'title_post as title', 'slug_post', 'img_post as image')->get();

            $combinedData = [
                'routes' => $routes,
                'courses' => $courses,
                'posts' => $posts,
            ];

            // Trả về dữ liệu dưới dạng một mảng
            return response()->json($combinedData);
        } catch (\Exception $e) {
            // Trả về thông báo lỗi cho người dùng
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }

    // Gợi ý khóa học có trong lộ trình
    public function courseSuggestions($course_id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }
            // $course = Module::with(['course', 'route'])->where('course_id', $course_id)->get();
            $route = Route::whereHas('modules', function ($query) use ($course_id) {
                $query->where('course_id', $course_id);
            })
                ->orderByRaw("CASE WHEN status = 'customize' THEN 1 ELSE 2 END")
                ->first(); // Ưu tiên lấy lộ trình có trạng thái customize


            $suggestedCourses = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ])
                ->whereHas('modules', function ($query) use ($route, $course_id) {
                    $query->where('route_id', $route->id)
                        ->where('course_id', '!=', $course_id);
                })
                ->get();
            // $data = [$suggestedCourses];
            foreach ($suggestedCourses as $course) {
                // Lấy danh sách module của khóa học hiện tại
                $modules = Module::where('course_id', $course->id)->pluck('id')->toArray();

                // Kiểm tra xem người dùng đã đăng ký bất kỳ module nào của khóa học này chưa
                $enrollmentExists = Enrollment::where('user_id', $user->id)
                    ->whereIn('module_id', $modules)
                    ->exists();

                // Nếu chưa đăng ký khóa học, thêm vào mảng
                if (!$enrollmentExists) {
                    $listCourses[] = $course; // Thêm khóa học vào danh sách
                }
            }

            // Trả về danh sách các khóa học chưa đăng ký
            return response()->json([
                'data' => $listCourses,
            ], 200);
        } catch (\Exception $e) {
            // Trả về thông báo lỗi cho người dùng
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }

    // Lấy ra tất cả trạng thái bài học theo khóa học
    public function getAllStatusDocByCourse($course_id)
    {
        try {
            // lấy ra các chapter theo course
            $getChapterByCourse_id = chapter::where('course_id', $course_id)->pluck('id');
            if (!$getChapterByCourse_id) {
                return response()->json([
                    'error' => 'Không tìm thấy chapter phù hợp.'
                ], 404);
            }

            // lấy ra các doc theo chapter
            $getDocByChapter_id = Document::WhereIn('chapter_id', $getChapterByCourse_id)->pluck('id');
            if (!$getChapterByCourse_id) {
                return response()->json([
                    'error' => 'Không tìm thấy Document phù hợp.'
                ], 404);
            }

            // láy auth user
            $user_id = auth('api')->user()->id;
            // lẩy ra id module
            $modules = Module::where('course_id', $course_id)->pluck('id')->toArray();
            if (!$modules) {
                return response()->json([
                    'error' => 'Không tìm thấy module phù hợp.'
                ], 404);
            }
            // dd($modules);
            // lấy ra Enrollment_id
            $Enrollment_id = Enrollment::where('user_id', $user_id)
                ->whereIn('module_id', $modules)
                ->where('del_flag', true)
                ->select('id')
                ->first();
            if (!$Enrollment_id) {
                return response()->json([
                    'error' => 'Không tìm thấy Enrollment phù hợp.'
                ], 404);
            }
            $getAllstatusDoc = Status_Doc::WhereIn('document_id', $getDocByChapter_id)->where('enrollment_id', $Enrollment_id->id)->get();
            return response()->json([
                'status' => 'success',
                'data' => $getAllstatusDoc
            ], 200);
        } catch (\Exception $e) {
            // Trả về thông báo lỗi cho người dùng
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình tìm kiếm.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }
    public function courseNext(Request $request, $orderby)
    {
        try {
            // Validate input
            $validatedData = $request->validate([
                'course_id' => 'required|array|exists:courses,id',
            ], [
                'course_id.required' => 'Course không được bỏ trống',
                'course_id.array' => 'Course phải là một mảng',
                'course_id.exists' => 'Course không hợp lệ',
            ]);

            $courseIds = $request->course_id;

            // Retrieve related data
            $moduleIds = Module::whereIn('course_id', $courseIds)->pluck('id');
            $enrolledModuleIds = Enrollment::whereIn('module_id', $moduleIds)->pluck('module_id');
            $routeIds = Module::whereIn('id', $enrolledModuleIds)->pluck('route_id');

            // Query courses
            $coursesQuery = Course::withCount(['chapters as num_chapter', 'documents as num_document'])
                ->where('del_flag', true)
                ->whereHas('modules', function ($query) use ($routeIds, $courseIds) {
                    $query->whereIn('route_id', $routeIds)
                        ->whereNotIn('course_id', $courseIds);
                });

            // Apply ordering filter
            if ($orderby === 'free') {
                $coursesQuery->where('price_course', 0);
            } elseif ($orderby === 'pro') {
                $coursesQuery->where('price_course', '>', 0);
            }

            // Get results
            $courses = $coursesQuery->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => CourseResource::collection($courses),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }

    public function tenPercentCourse($course_id)
    {
        try {
            // Kiểm tra quyền truy cập khóa học dựa trên Enrollment và cờ del_flag
            $course = Course::where('id', $course_id)->where('del_flag', true)->first();

            if (is_null($course)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học không tồn tại hoặc bạn không có quyền truy cập.',
                    'data' => null,
                ], 403);
            }
            $course_id = $course->id;
            // Lấy các chapter của khóa học
            $chapters = Chapter::where('course_id', $course_id)
                ->where('del_flag', true)
                ->get();

            if ($chapters->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có chương nào thuộc khóa học này',
                    'data' => null,
                ], 404);
            }

            // Lấy danh sách chapter_id dưới dạng mảng
            $chapter_ids = Chapter::where('course_id', $course_id)->pluck('id')->toArray();

            // Định dạng dữ liệu trả về
            $chapterData = Chapter::where('course_id', $course_id)
                ->where('del_flag', true)
                ->orderBy('serial_chapter', 'asc')
                ->with([
                    'documents' => function ($query) use ($chapter_ids) {
                        $query->select('id', 'chapter_id', 'name_document', 'discription_document', 'serial_document', 'url_video', 'type_document', 'del_flag', 'updated_at')
                            ->where('del_flag', true)
                            ->whereIn('chapter_id', $chapter_ids) // Sửa lỗi truy vấn
                            ->limit(2)
                            ->orderBy('serial_document', 'asc');
                    },
                    'documents.question', // Quan hệ 1-1 với Question
                    'documents.code',     // Quan hệ 1-1 với Code
                ])
                ->get()
                ->map(function ($chapter) {
                    return [
                        'chapter_id' => $chapter->id,
                        'chapter_name' => $chapter->name_chapter,
                        'documents' => $chapter->documents->map(function ($document) {
                            $documentDetails = [
                                'document_id' => $document->id,
                                'name_document' => $document->name_document,
                                'discription_document' => $document->discription_document,
                                'url_video' => $document->url_video,
                                'type_document' => $document->type_document,
                                'updated_at' => $document->updated_at,
                            ];

                            // Lấy dữ liệu liên quan dựa trên type_document
                            if ($document->type_document === 'quiz' && $document->question) {
                                $documentDetails['questions'] = [
                                    (object) [
                                        'id' => $document->question->id,
                                        'content_question' => $document->question->content_question,
                                        'type_question' => $document->question->type_question,
                                        'updated_at' => $document->question->updated_at,
                                    ]

                                ];
                            } elseif ($document->type_document === 'code' && $document->code) {
                                $documentDetails['codes'] = [
                                    'id' => $document->code->id,
                                    'question_code' => $document->code->question_code,
                                    'answer_code' => $document->code->answer_code,
                                    'tutorial_code' => $document->code->tutorial_code,
                                    'updated_at' => $document->code->updated_at,
                                ];
                            }

                            return $documentDetails;
                        }),
                    ];
                });
            return response()->json([
                'course_id' => $course_id,
                'data' => $chapterData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }
    public function slugByIdCourse($slug, $table)
    {
        try {
            // Bản đồ ánh xạ bảng và cột slug tương ứng
            $modelMap = [
                'Course' => ['model' => \App\Models\Course::class, 'slug_column' => 'slug_course'],
                'Post' => ['model' => \App\Models\Post::class, 'slug_column' => 'slug_post'],
                'Route' => ['model' => \App\Models\Route::class, 'slug_column' => 'slug_route'],
            ];

            // Kiểm tra bảng có tồn tại trong ánh xạ không
            if (!array_key_exists($table, $modelMap)) {
                return response()->json(['error' => 'Table không hợp lệ.'], 400);
            }

            // Lấy model và cột slug từ ánh xạ
            $model = $modelMap[$table]['model'];
            $slugColumn = $modelMap[$table]['slug_column'];


            // Truy vấn theo cột slug
            $record = $model::where($slugColumn, $slug)->pluck('id');

            if ($record->isEmpty()) {
                return response()->json(['message' => 'Không có kết quả.'], 404);
            }
            $result = $record->implode(',');

            // Trả về mảng với key là tên bảng
            return response()->json([$table => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi.',
                'details' => $e->getMessage(), // Có thể loại bỏ trong production để bảo mật
            ], 500);
        }
    }
    public function handleCourseRequest($course_id)
    {
        try {
            // Kiểm tra tính hợp lệ của course_id
            if (!Str::isUlid($course_id) || !Course::where('id', $course_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ hoặc khóa học đã bị xóa',
                    'data' => null,
                ], 400);
            }

            // Dữ liệu khóa học
            $course = Course::with([
                'chapters' => function ($query) {
                    $query->orderBy('serial_chapter', 'asc')->where('del_flag', true);
                },
            ])->where('id', $course_id)->where('del_flag', true)->firstOrFail();

            // Lấy danh sách feedbacks
            $feedbacks = Enrollment::with(['module.course', 'user'])
                ->whereHas('module.course', function ($query) use ($course_id) {
                    $query->where('id', $course_id);
                })
                ->where('rating_course', '>=', 1)
                ->get();

            $feedbackResult = $feedbacks->map(function ($feedback) {
                return [
                    'course_id' => optional($feedback->module->course)->id,
                    'user_id' => optional($feedback->user)->id,
                    'fullname' => optional($feedback->user)->fullname,
                    'avatar' => optional($feedback->user)->avatar,
                    'rating_course' => $feedback->rating_course,
                    'feedback_text' => $feedback->feedback_text,
                ];
            });

            // Lấy danh sách FAQ
            $faqs = FAQ_Course::whereHas('course', function ($query) use ($course_id) {
                $query->where('id', $course_id)
                    ->where('del_flag', true);
            })
                ->where('del_flag', true)
                ->get();

            $faqResult = $faqs->map(function ($faq) {
                return [
                    'question_faq' => $faq->question_faq,
                    'answer_faq' => $faq->answer_faq,
                ];
            });

            // Tăng views_course
            Course::where('id', $course_id)->increment('views_course');
            $coursess = Course::withCount([
                'chapters as num_chapter',
                'documents as num_document'
            ]) // Đếm số chapters và lessons
                ->with('chapters.documents') // Lấy các chapters cùng lessons lồng nhau
                ->where('status_course', 'success')
                ->where('del_flag', true)
                ->find($course_id);

            // Tổng hợp dữ liệu trả về
            $result = [
                'course' => new CourseResource($course),
                'name_chapters' => $course->chapters->pluck('name_chapter'),
                'feedbacks' => $feedbackResult,
                'faqs' => $faqResult,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
