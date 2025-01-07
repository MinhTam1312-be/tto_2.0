<?php

namespace App\Http\Controllers;

use App\Mail\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use Exception;
use Illuminate\Http\Request;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Mail;

class CertificateController extends Controller
{
    // Cấp chứng chỉ khóa học
    public function sendMailCertificate(Request $request, $course_id)
    {
        try {
            // Lấy thông tin người dùng hiện tại
            $user = auth('api')->user();

            // Lấy thông tin khóa học
            $course = Course::findOrFail($course_id);

            // Kiểm tra trạng thái Enrollment
            $enrollment = Enrollment::where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->where('status_course', 'completed')
                ->where('enroll', true)
                ->where('del_flag', true)
                ->first();

            // Kiểm tra nếu không có Enrollment phù hợp
            if (!$enrollment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin đăng ký hoặc khóa học chưa hoàn thành.'
                ], 404);
            }

            // Validate file chứng chỉ
            $validatedData = $request->validate([
                'certificate_file' => 'required|file|mimes:jpg,png,pdf|max:10240',
            ], [
                'certificate_file.required' => 'Chứng chỉ là bắt buộc.',
                'certificate_file.file' => 'File không hợp lệ.',
                'certificate_file.mimes' => 'File phải có định dạng jpg, png hoặc pdf.',
                'certificate_file.max' => 'File không được lớn hơn 10MB.',
            ]);

            // Lấy thông tin từ request
            $userName = preg_replace('/\s+/', '_', $user->fullname); // Xóa khoảng trắng trong tên người dùng
            $courseName = preg_replace('/\s+/', '_', $course->name_course); // Xóa khoảng trắng trong tên khóa học

            // Kết hợp tên file
            $fileName = "{$userName}_{$courseName}";

            // Upload file lên Cloudinary
            $file = $request->file('certificate_file');
            $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'certificates', // Thư mục lưu file trên Cloudinary
                'public_id' => $fileName, // Đặt tên file là tên người dùng + tên khóa học
                'resource_type' => 'auto', // Tự động nhận diện loại file
                'type' => 'upload', // Đảm bảo file được public
            ]);

            // Lấy URL file sau khi upload
            $uploadedFileUrl = $uploadedFile->getSecurePath();

            // Lưu URL chứng chỉ vào cơ sở dữ liệu
            $enrollment->certificate_course = $uploadedFileUrl;
            $enrollment->save();

            // Gửi email chứng chỉ
            Mail::to($enrollment->user->email)->send(new Certificate(
                $uploadedFileUrl,
                $enrollment->user->fullname,
                $enrollment->updated_at,
                $course->name_course
            ));

            // Phản hồi thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Cấp chứng chỉ thành công.',
                'data' => $uploadedFileUrl,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Check chứng chỉ khóa học 
    public function checkCertificate($course_id)
    {
        try {
            // Lấy thông tin người dùng hiện tại
            $user = auth('api')->user();

            // Lấy danh sách module_id liên quan đến course_id
            $module_ids = Module::where('course_id', $course_id)->pluck('id');

            // Kiểm tra xem người dùng đã đăng ký các module này chưa
            $enrollment = Enrollment::where('user_id', $user->id)
                ->whereIn('module_id', $module_ids)
                ->first();

            // Nếu không tìm thấy bản ghi, trả về false
            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin đăng ký khóa học.'
                ], 404);
            }

            // Kiểm tra giá trị của cột certificate_course
            if ($enrollment->certificate_course) {
                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng đã có chứng chỉ.',
                    'data' => true
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng chưa có chứng chỉ.',
                    'data' => false
                ]);
            }

        } catch (Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình xử lý.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




}
