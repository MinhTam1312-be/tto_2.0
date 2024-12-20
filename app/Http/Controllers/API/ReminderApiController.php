<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReminderResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Reminder;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ReminderApiController extends Controller
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
    public function store(Request $request) {}

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
        try {
            // Tìm bản ghi theo ID
            $reminder = Reminder::findOrFail($id);

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'day_of_week' => [
                    'required',
                    'min:1'
                ],
                'day_of_week.*' => [
                    'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'
                ],
                'time' => [
                    'required',
                    'min:1'
                ],
                'time.*' => [
                    'date_format:H:i'
                ],
            ], [
                'day_of_week.required' => 'Vui lòng chọn ít nhất một ngày trong tuần.',
                'day_of_week.*.in' => 'Ngày trong tuần không hợp lệ. Chọn từ Chủ nhật đến Thứ bảy.',
                'time.required' => 'Vui lòng cung cấp ít nhất một thời gian.',
                'time.*.date_format' => 'Thời gian phải có định dạng HH:MM:SS.',
            ]);

            // Cập nhật dữ liệu
            $reminder->update([
                'day_of_week' => $validatedData['day_of_week'],
                'time' => $validatedData['time'],
                'del_flag' => true, // Hoặc giữ nguyên giá trị cũ nếu không cần thay đổi
            ]);
            // Trả về phản hồi thành công
            return response()->json([
                'message' => 'Cập nhật nhắc nhở thành công',
                'updated_reminders' => $reminder,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Xử lý lỗi xác thực
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Xử lý lỗi không tìm thấy bản ghi
            return response()->json(['message' => 'Không tìm thấy Reminder với ID: ' . $id], 404);
        } catch (\Exception $e) {
            // Xử lý lỗi khác
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật Reminder', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $reminder = Reminder::findOrFail($id); // Sửa tên biến cho đúng chính tả
            $reminder->delete();

            return response()->json(['message' => 'Đã xóa thành công'], 200); // Sửa lỗi chính tả 'messge'
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy Reminder với ID: ' . $id], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa Reminder', 'error' => $e->getMessage()], 500);
        }
    }


    public function getReminder($course_id)
    {
        try {
            // Lấy thông tin người dùng đăng nhập
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập'], 401);
            }

            // Lấy ra các reminder từ database
            $result = Reminder::whereHas('enrollment.module.course', function ($query) use ($course_id, $user) {
                $query->where('course_id', $course_id); // Lọc theo course_id
            })->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id); // Lọc theo user_id
            })->orderBy('day_of_week', 'asc') // Sắp xếp theo thứ
                ->orderBy('time', 'asc')       // Sau đó sắp xếp theo giờ
                ->get()->map(function ($reminder) {
                    return [
                        'reminder_id' => $reminder->id,
                        'day_of_week' => $reminder->day_of_week,
                        'time' => $reminder->time,
                        'enrollment_id' => $reminder->enrollment_id,
                    ];
                });


            return response()->json([
                'message' => 'Lấy ra nhắc nhở thành công',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy dữ liệu reminder', 'error' => $e->getMessage()], 500);
        }
    }

    public function postReminder(Request $request)
    {
        try {
            $user = auth('api')->user();

            $validatedData = $request->validate([
                'course_id' => [
                    'required',
                    'exists:courses,id'
                ],
                'day_of_week' => [
                    'required',
                    'array',
                    'min:1'
                ],
                'day_of_week.*' => [
                    'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'
                ],
                'time' => [
                    'required',
                    'array',
                    'min:1'
                ],
                'time.*' => [
                    'date_format:H:i'
                ],
            ], [
                'course_id.required' => 'Vui lòng cung cấp mã khóa học.',
                'course_id.exists' => 'Mã khóa học không tồn tại.',
                'day_of_week.required' => 'Vui lòng chọn ít nhất một ngày trong tuần.',
                'day_of_week.array' => 'Ngày trong tuần phải là một mảng.',
                'day_of_week.*.in' => 'Ngày trong tuần không hợp lệ. Chọn từ Chủ nhật đến Thứ bảy.',
                'time.required' => 'Vui lòng cung cấp ít nhất một thời gian.',
                'time.array' => 'Thời gian phải là một mảng.',
                'time.*.date_format' => 'Thời gian phải có định dạng HH:MM:SS.',
            ]);
            $moduleIds = Module::where('course_id', $validatedData['course_id'])->pluck('id');
            $enrollment = Enrollment::whereIn('module_id', $moduleIds)
                ->where('user_id', $user->id)
                ->where('enroll', true)
                ->first();
            // dd($enrollment);
            if (!$enrollment) {
                return response()->json(['error' => 'Đăng ký không hợp lệ hoặc chưa tham gia khóa học'], 403);
            }
            $currentReminderCount = Reminder::where('enrollment_id', $enrollment->id)->count();
            $newReminderCount = count($validatedData['day_of_week']) * count($validatedData['time']);

            if ($currentReminderCount + $newReminderCount >= 15) {
                return response()->json(['error' => 'Không thể thêm nhắc nhở. Số lượng nhắc nhở tối đa là 14.'], 400);
            }
            $reminders = []; // Tạo một mảng để lưu các reminders
            foreach ($validatedData['day_of_week'] as $day_of_week) {
                foreach ($validatedData['time'] as $time) {
                    // Kiểm tra nếu ngày và giờ đã tồn tại
                    $existingReminder = Reminder::where('enrollment_id', $enrollment->id)
                        ->where('day_of_week', $day_of_week)
                        ->where('time', $time)
                        ->first();

                    if ($existingReminder) {
                        return response()->json([
                            'error' => 'Nhắc nhở với ngày ' . $day_of_week . ' và giờ ' . $time . ' đã tồn tại.'
                        ], 400);
                    }
                    $reminder = Reminder::create([
                        'enrollment_id' => $enrollment->id,
                        'day_of_week' => $day_of_week,
                        'del_flag' => 'true',
                        'time' => $time,
                    ]);
                    $reminders[] = $reminder;
                }
            }


            return response()->json(['message' => 'Thêm nhắc nhở thành công', 'reminder' => $reminders], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tạo nhắc nhở', 'message' => $e->getMessage()], 500);
        }
    }
    public function updateReminder(Request $request)
    {
        try {
            $user = auth('api')->user();

            $validatedData = $request->validate([
                'reminder' => [
                    'required',
                    'array',
                ],
            ], [
                'reminder.required' => 'Vui lòng cung cấp mã đăng ký.',
                'reminder.array' => 'remider phải là mảng',
            ]);
            $updatedReminders = [];
            foreach ($validatedData['reminder'] as $reminderData) {
                $reminder = Reminder::find($reminderData['remider_id']);

                $reminder->update([
                    'day_of_week' => $reminderData['day_of_week'],
                    'time' => $reminderData['time'],
                    'del_flag' => true, // Hoặc giữ nguyên giá trị cũ nếu không cần thay đổi
                ]);

                $updatedReminders[] = $reminder;
            }
            return response()->json([
                'message' => 'Cập nhật nhắc nhở thành công',
                'updated_reminders' => $updatedReminders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tạo nhắc nhở', 'message' => $e->getMessage()], 500);
        }
    }
    public function getReminderCourse()
    {
        try {
            // Lấy user_id từ Auth
            $user_id = auth('api')->user()->id;

            // Lấy danh sách enrollment_id của user
            $enrollments = Enrollment::where('user_id', $user_id)
                ->where('enroll', true)
                ->where('enrollments.del_flag', true)
                ->with([
                    'module' => function ($query) {
                        $query->with([
                                'course' => function ($query) {
                                    $query->where('courses.del_flag', true)->with([
                                        'chapters' => function ($query) {
                                            $query->with(['documents']); // Không kiểm tra del_flag cho documents
                                        }
                                    ]);
                                }
                            ]);
                    },
                    'status_docs' => function ($query) {
                        // Không kiểm tra del_flag trong status_docs
                    }
                ])
                ->get();
                    
                // dd($enrollments);
            // Tạo danh sách khóa học với progress_percentage
            $coursesWithProgress = $enrollments->map(function ($enrollment) {
                $module = $enrollment->module;

                if ($module && $module->course) {
                    $course = $module->course;
                    // Đếm số video đã xem
                    $watchedVideos = $enrollment->status_docs()->where('status_doc', true)->count();

                    // Đếm số chương và số tài liệu
                    $numDocuments = $course->chapters->flatMap(function ($chapter) {
                        return $chapter->documents;
                    })->count();
                    

                    // Tính phần trăm tiến độ
                    $progressPercentage = $numDocuments > 0 ? round(($watchedVideos / $numDocuments) * 100, 1) : 0;

                    $document_ids = $enrollment->status_docs()->where('status_doc', false)->pluck('document_id');

                    // Tìm tên của các tài liệu chưa xem
                    $firstDocumentName = $course->chapters->flatMap(function ($chapter) use ($document_ids) {
                        return $chapter->documents->filter(function ($document) use ($document_ids) {
                            return $document_ids->contains($document->id);
                        })->pluck('name_document'); // Giả sử mỗi tài liệu có cột 'name'
                    })->first();
                    // Trả về dữ liệu khóa học
                    return [
                        'id' => $course->id,
                        'name_course' => $course->name_course,
                        'img_course' => $course->img_course,
                        'price_course' => $course->price_course,
                        'slug_course' => $course->slug_course,
                        'discount_price_course' => $course->discount_price_course,
                        'status_course' => $course->status_course,
                        'views_course' => $course->views_course,
                        'rating_course' => $course->rating_course,
                        'num_document' => $numDocuments,
                        'num_chapter' => $course->chapters()->count(),
                        'del_flag' => $course->del_flag,
                        'instructor_id' => $course->user_id,
                        'instructor_name' => $course->user->fullname,
                        'created_at' => $course->created_at,
                        'updated_at' => $course->updated_at,
                        'watchedVideos' => $watchedVideos,
                        'name_documents' => $firstDocumentName,
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                return null;
            })->filter();
            // Kết hợp reminders với các khóa học
            $mergedData = $coursesWithProgress->map(function ($course) use ($enrollments) {
                // Lấy danh sách reminders liên quan đến khóa học
                $reminders = Reminder::whereIn('enrollment_id', $enrollments->pluck('id'))
                    ->whereHas('enrollment.module.course', function ($query) use ($course) {
                        $query->where('id', $course['id']);
                    })
                    ->get();
                $hasReminders = $reminders->isNotEmpty() ? true : false;
                // Gộp reminders vào khóa học
                return array_merge($course, [
                    'has_reminders' => $hasReminders,
                ]);
            });

            // Trả về response
            return response()->json([
                'message' => 'success',
                'data' => $coursesWithProgress->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tạo nhắc nhở', 'message' => $e->getMessage()], 500);
        }
    }
}
