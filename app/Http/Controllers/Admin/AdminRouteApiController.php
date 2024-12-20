<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCourseResource;
use App\Http\Resources\Admin\AdminRouteResource;
use App\Models\Activity_History;
use App\Models\Course;
use App\Models\Module;
use App\Models\Route;
use App\Services\LogActivityService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class AdminRouteApiController extends Controller
{
    protected $user;
    public function __construct()
    {
        $this->user = auth('api')->user(); // Khởi tạo thuộc tính trong constructor
    }
    /**
     * Display a listing of the resource.
     */
    private function logActivity($activityName, $description, $status)
    {
        Activity_History::create([
            'name_activity' => $activityName,
            'discription_activity' => $this->user->fullname . ': ' . $description . ' ' . $this->user->role,
            'status_activity' => $status,
            'user_id' => $this->user->id
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $routes = Route::all();
            return response()->json([
                'routes' => AdminRouteResource::collection($routes),
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
            // Xác thực dữ liệu
            $validatedData = $request->validate([
                'name_route' => 'required',
                'img_route' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'discription_route' => 'required'
            ], [
                'name_route.required' => 'Vui lòng nhập tên lộ trình',
                'img_route.required' => 'Ảnh đại diện là bắt buộc.',
                'img_route.image' => 'Tệp tải lên phải là một hình ảnh.',
                'img_route.mimes' => 'Định dạng ảnh không được hỗ trợ. Vui lòng tải lên tệp .jpeg, .png, .jpg, hoặc .gif.',
                'img_route.max' => 'Dung lượng tệp quá lớn. Tối đa cho phép là 2MB.',
                'discription_route.required' => 'Vui lòng nhập mô tả lộ trình',
            ]);

            // Kiểm tra và xử lý tệp ảnh
            if ($request->hasFile('img_route')) {
                $file = $request->file('img_route');

                // Upload ảnh lên Cloudinary
                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'routes',
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ])->getSecurePath();
            }

            // Tạo mới lộ trình
            $route = Route::create([
                'name_route' => $validatedData['name_route'],
                'img_route' => $uploadedFileUrl,
                'del_flag' => true,
                'discription_route' => $validatedData['discription_route'],
            ]);

            LogActivityService::log('tao_lo_trinh', 'Thêm lộ trình thành công: ' . $validatedData['name_route'], 'success');

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm thành công',
                'data' => new AdminRouteResource($route),
            ], 201);
        } catch (\Exception $e) {
            LogActivityService::log('tao_lo_trinh', 'Lỗi khi thêm lộ trình: ' . $e->getMessage(), 'fail');

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Tìm route theo ID
            $route = Route::findOrFail($id);
            $course_id = Module::where('route_id', $id)->where('del_flag', true)->pluck('course_id');
            $course = Course::whereIn('id', $course_id)->where('del_flag', true)->get();
            // Trả về chi tiết lộ trình
            return response()->json([
                'route' => $route, // Thông tin lộ trình
                'courses' => AdminCourseResource::collection($course)
            ], 200);

        } catch (\Exception $e) {
            // Trả về phản hồi lỗi
            return response()->json([
                'error' => 'Không tìm thấy lộ trình',
                'message' => $e->getMessage()
            ], 404);
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
            // Validate các trường
            $validatedData = $request->validate([
                'name_route' => 'required',
                'discription_route' => 'required',
            ], [
                'name_route.required' => 'Vui lòng nhập tên lộ trình',
                'discription_route.required' => 'Vui lòng nhập mô tả lộ trình',
            ]);

            // Tìm route dựa trên ID
            $route = Route::find($id);
            if (!$route) {
                return response()->json(['status' => 'fail', 'message' => 'Không tìm thấy lộ trình'], 404);
            }

            // Lấy dữ liệu cũ để so sánh
            $oldData = $route->only(['name_route', 'discription_route']);

            // Cập nhật dữ liệu
            $route->update($validatedData);

            // So sánh và log theo từng thay đổi
            $logMessage = "Cập nhật lộ trình thành công với các thay đổi: ";
            $changes = [];
            if ($oldData['name_route'] != $validatedData['name_route']) {
                $changes[] = "Tên lộ trình mới: " . $validatedData['name_route'];
            }
            if ($oldData['discription_route'] != $validatedData['discription_route']) {
                $changes[] = "Mô tả lộ trình mới: " . $validatedData['discription_route'];
            }

            // Nếu có thay đổi thì log, ngược lại log thông báo không thay đổi
            if (!empty($changes)) {
                $logMessage .= implode(". ", $changes);
            } else {
                $logMessage = "Không có thay đổi nào trong lộ trình.";
            }
            LogActivityService::log('cap_nhat_lo_trinh', $logMessage, 'success');

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công',
                'data' => new AdminRouteResource($route),
            ], 200);

        } catch (\Exception $e) {
            // Ghi log thất bại
            LogActivityService::log('cap_nhat_lo_trinh', "Cập nhật thất bại: " . $e->getMessage(), 'fail');
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi cập nhật',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    // public function destroy($id)
    // {
    //     try {
    //         // Tìm tuyến đường cần xóa
    //         $route = Route::findOrFail($id);

    //         // Kiểm tra trạng thái trước khi xóa
    //         if ($route->status === 'default') {
    //             return response()->json([
    //                 'status' => 'fail',
    //                 'message' => 'Trạng thái mặc định, bạn không thể xóa được',
    //                 'data' => null,
    //             ], 400);
    //         }

    //         $route->delete();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Xóa lộ trình thành công',
    //             'data' => null,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'fail',
    //             'message' => $e->getMessage(),
    //             'data' => null,
    //         ], 400);
    //     }
    // }

    // Ẩn, hiện lộ trình
    public function statusRoute($route_id)
    {
        try {
            // Tìm lộ trình theo ID
            $route = Route::findOrFail($route_id);

            // Lấy trạng thái cũ để log
            $oldStatus = $route->del_flag ? 'hiện' : 'ẩn';

            // Thay đổi trạng thái (ẩn/hiện)
            $route->update(['del_flag' => !$route->del_flag]);

            // Lấy trạng thái mới sau khi thay đổi
            $newStatus = $route->del_flag ? 'hiện' : 'ẩn';

            // Ghi log với thông tin chi tiết
            LogActivityService::log(
                'thay_doi_trang_thai_lo_trinh',
                "Thay đổi trạng thái lộ trình '{$route->name_route}' từ $oldStatus sang $newStatus.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => "Đã thay đổi trạng thái lộ trình thành công",
                'data' => new AdminRouteResource($route),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy lộ trình',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            // Ghi log khi xảy ra lỗi khác
            LogActivityService::log(
                'thay_doi_trang_thai_lo_trinh',
                "Thay đổi trạng thái thất bại cho lộ trình ID $route_id: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi thay đổi trạng thái lộ trình',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    // Hàm upload ảnh lên Cloudinary
    public function uploadImage($file, $folder = 'routes')
    {
        // Upload file lên Cloudinary và trả về URL bảo mật
        return Cloudinary::upload($file->getRealPath(), [
            'folder' => $folder // Thư mục Cloudinary
        ])->getSecurePath();
    }

    // Hàm xử lý ảnh tải lên
    private function processUploadedImage(Request $request, $key = 'img_route')
    {
        $imgFile = $request->file($key);

        if ($imgFile) {
            // Kiểm tra nếu có nhiều hơn 1 file ảnh
            if (is_array($imgFile) || $request->hasFile("{$key}.1")) {
                throw new \Exception('Chỉ được phép tải lên tối đa 1 ảnh.');
            }

            // Upload ảnh và trả về URL
            return $this->uploadImage($imgFile, 'routes');
        }

        return null;
    }

    // Hàm thay đổi ảnh lộ trình
    public function updateImagesRoute(Request $request, $route_id)
    {
        try {
            // Tìm khóa học theo ID
            $route = Route::findOrFail($route_id);

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'img_route' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'img_route.required' => 'Vui lòng chọn ảnh.',
                'img_route.image' => 'Tệp phải là một hình ảnh.',
                'img_route.mimes' => 'Chỉ hỗ trợ định dạng: jpeg, png, jpg, gif.',
                'img_route.max' => 'Dung lượng tối đa của hình ảnh là 2MB.',
            ]);

            // Upload ảnh mới lên Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('img_route')->getRealPath(), [
                'folder' => 'routes',
                'public_id' => pathinfo($request->file('img_route')->getClientOriginalName(), PATHINFO_FILENAME)
            ])->getSecurePath();

            // Cập nhật ảnh mới cho khóa học
            $route->update(['img_route' => $uploadedFileUrl]);

            // Ghi log thành công
            LogActivityService::log(
                'cap_nhat_hinh_anh_khoa_hoc',
                "Đã cập nhật hình ảnh cho khóa học '{$route->name_route}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => "Hình ảnh khóa học '{$route->name_route}' đã được cập nhật thành công.",
                'data' => new AdminRouteResource($route),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy khóa học.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ghi log khi xác thực thất bại
            LogActivityService::log(
                'cap_nhat_hinh_anh_khoa_hoc',
                "Lỗi xác thực khi cập nhật hình ảnh: " . json_encode($e->errors(), JSON_UNESCAPED_UNICODE),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi hệ thống
            LogActivityService::log(
                'cap_nhat_hinh_anh_khoa_hoc',
                "Đã xảy ra lỗi khi cập nhật hình ảnh cho khóa học: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi trong quá trình cập nhật hình ảnh.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
