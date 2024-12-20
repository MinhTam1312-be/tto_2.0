<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\CheckMail;
use App\Models\Activity_History;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Course;
use App\Models\Chapter;

class AdminUserApiController extends Controller
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
            'discription_activity' => $this->user->fullname . ': ' . $description,
            'status_activity' => $status,
            'user_id' => $this->user->id
        ]);
    }
    public function index()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            $users = User::where('role', '!=', 'client')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminUserResource::collection($users),
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
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new UserResource($user),
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
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getUserRole($role)
    {
        try {
            // Kiểm tra quyền dựa trên $role
            switch ($role) {
                case 'admin':
                    $users = User::where('role', 'admin')->get();
                    break;
                case 'accountant':
                    $users = User::where('role', 'accountant')->get();
                    break;
                case 'marketing':
                    $users = User::where('role', 'marketing')->get();
                    break;
                case 'instructor':
                    $users = User::where('role', 'instructor')->get();
                    break;
                case 'client':
                    $users = User::where('role', 'client')->get();
                    break;

                default:
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Quyền không hợp lệ',
                        'data' => null,
                    ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminUserResource::collection($users),
            ], 200);

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminUserResource::collection($users),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Ẩn, hiện người dùng
    public function statusUser($user_id)
    {
        try {
            // Tìm người dùng dựa trên ID
            $user = User::findOrFail($user_id);

            // Lấy trạng thái cũ
            $oldStatus = $user->del_flag;

            // Thay đổi trạng thái (ẩn/hiện)
            $user->update(['del_flag' => !$user->del_flag]);

            // Ghi log trạng thái
            $newStatus = $user->del_flag ? 'hiện' : 'ẩn'; // Ẩn = false, Hiện = truetrue
            $logMessage = "Thay đổi trạng thái người dùng: " . $user->fullname . " từ " . ($oldStatus ? 'hiện' : 'ẩn') . " sang $newStatus.";
            LogActivityService::log('thay_doi_trang_thai_nguoi_dung', $logMessage, 'success');

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái người dùng thành công',
                'data' => new AdminUserResource($user),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log lỗi
            LogActivityService::log('thay_doi_trang_thai_nguoi_dung', "Thay đổi trạng thái thất bại cho user ID $user_id: " . $e->getMessage(), 'fail');

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function checkMailRegisterAdmin(Request $request)
    {
        // Kiểm tra định dạng email
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@fpt\.edu\.vn$/'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.regex' => 'Email phải có đuôi là @fpt.edu.vn.',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['message' => 'Email này đã tồn tại trong hệ thống'], 422);
        }
        if ($validator->fails()) {
            return response()->json(['status' => 'error', $validator->errors()], 422);
        }

        // Kiểm tra thời gian gửi yêu cầu trước đó
        $lastRequest = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        // Tạo mã xác thực (OTP)
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // Log::info('Mã OTP được tạo: ' . $token . ' cho email: ' . $request->email);

        // Cập nhật hoặc tạo mới token
        if ($lastRequest) {
            if (!Carbon::parse($lastRequest->created_at)->addSeconds(120)->isPast()) {
                return response()->json(['message' => 'Vui lòng chờ ít nhất 120 giây trước khi yêu cầu mã xác thực mới'], 429);
            }
            // Nếu tồn tại, cập nhật token mới
            DB::table('password_reset_tokens')->where('email', $request->email)->update([
                'token' => $token,
                'created_at' => Carbon::now('Asia/Ho_Chi_Minh') // Cập nhật thời gian tạo
            ]);
        } else {
            // Nếu chưa tồn tại, tạo mới token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now() // Thời gian tạo mới
            ]);
        }

        // Gửi email chứa mã xác thực
        try {
            Mail::to($request->email)->send(new CheckMail($token));
            Log::info('Gửi được mail thành công: ' . $request->email);
            return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
        } catch (\Exception $e) {
            Log::error('Gửi email thất bại: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
        }
    }
    public function getMailFpt()
    {
        try {
            // Lấy danh sách người dùng có email kết thúc bằng @fpt.edu.vn
            $users = User::where('email', 'like', '%@fpt.edu.vn')->get();

            // Kiểm tra nếu không có kết quả nào được tìm thấy
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Không có người dùng nào với email thuộc miền @fpt.edu.vn.',
                    'data' => []
                ]);
            }

            // Trả về danh sách người dùng nếu tìm thấy
            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            // Bắt lỗi và trả về phản hồi lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người dùng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Cập nhật quyền cho người dùng có email @fpt.edu.vn
    public function updateRoleAdmin(Request $request)
    {
        try {
            // Xác thực vai trò mới
            $validatedData = $request->validate([
                'role' => 'required|in:marketing,instructor,accountant,admin',
                'user_id' => 'required|exists:users,id', // Xác thực user_id phải tồn tại trong bảng users
            ], [
                'role.required' => 'Vai trò là bắt buộc.',
                'role.in' => 'Vai trò không hợp lệ, vui lòng chọn đúng vai trò.',
                'user_id.required' => 'ID người dùng là bắt buộc.',
                'user_id.exists' => 'ID người dùng không tồn tại.',
            ]);

            // Tìm người dùng
            $user = User::find($request->user_id);

            // Kiểm tra điều kiện email và cập nhật vai trò
            $updatedCount = User::where('email', 'like', '%@fpt.edu.vn')
                ->where('id', $request->user_id)
                ->update(['role' => $request->role]);

            if ($updatedCount) {
                // Ghi log chi tiết thay đổi vai trò
                LogActivityService::log(
                    'cap_nhat_quyen',
                    "Đã thay đổi vai trò người dùng '{$user->name}', Email: {$user->email}) từ '{$user->role}' sang '{$request->role}'.",
                    'success'
                );

                return response()->json([
                    'status' => 'success',
                    'message' => "Đã cập nhật vai trò cho $updatedCount người dùng có email @fpt.edu.vn.",
                ]);
            } else {
                // Ghi log khi không có thay đổi
                LogActivityService::log(
                    'cap_nhat_quyen',
                    "Không có thay đổi vai trò cho người dùng (ID: {$request->user_id}). Email không khớp điều kiện @fpt.edu.vn.",
                    'success'
                );

                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có người dùng nào để cập nhật.',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ghi log lỗi xác thực
            LogActivityService::log(
                'cap_nhat_quyen',
                "Lỗi xác thực dữ liệu khi cập nhật vai trò: " . json_encode($e->errors(), JSON_UNESCAPED_UNICODE),
                'fail'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log lỗi chung
            LogActivityService::log(
                'cap_nhat_quyen',
                "Đã xảy ra lỗi khi cập nhật vai trò: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi trong quá trình cập nhật vai trò.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showCourseAdmin($course_id)
    {
        try {
            // lẩy ra id khóa học
            $getCourseId = Course::where('id', $course_id)->select('id')->first()->id;
            // lấy ra các chapter của khóa học đó
            $getChapterByCourse = Chapter::where('course_id', $getCourseId)
                ->orderBy('serial_chapter', 'asc')
                ->with([
                    'documents' => function ($query) {
                        $query->when(request('type_document') === 'video', function ($q) {
                            $q->where('type_document', 'video');
                        })->when(request('type_document') === 'quiz', function ($q) {
                            $q->where('type_document', 'quiz')->with('question');
                        })->when(request('type_document') === 'code', function ($q) {
                            $q->where('type_document', 'code')->with('code');
                        });
                    }
                ])
                ->get();
            return response()->json($getChapterByCourse);
        } catch (\Exception $e) {
            // Bắt lỗi và trả về phản hồi lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người dùng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
