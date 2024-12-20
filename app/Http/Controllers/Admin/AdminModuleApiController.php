<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class AdminModuleApiController extends Controller
{
    public function addCourseToRoute(Request $request)
    {
        try {
            // Validate course_id và route_id
            $validatedData = $request->validate([
                'course_id' => 'required|array',
                'course_id.*' => 'exists:courses,id',
                'route_id' => 'required|exists:routes,id',
            ], [
                'course_id.required' => 'Khóa học là bắt buộc.',
                'course_id.*.exists' => 'Khóa học không tồn tại.',
                'route_id.required' => 'Lộ trình là bắt buộc.',
                'route_id.exists' => 'Lộ trình không tồn tại.',
            ]);
        
            $courseIds = $validatedData['course_id'];
            $routeId = $validatedData['route_id'];
        
            $moduleIds = [];
        
            foreach ($courseIds as $courseId) {
                // Kiểm tra xem cặp route_id và course_id đã tồn tại hay chưa
                $moduleExists = Module::where('route_id', $routeId)
                    ->where('course_id', $courseId)
                    ->exists();
        
                if ($moduleExists) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => "Khóa học ID đã tồn tại trong lộ trình ID.",
                        'data' => null,
                    ], 400);
                }
        
                // Tạo module nếu chưa tồn tại
                $module = Module::create([
                    'route_id' => $routeId,
                    'course_id' => $courseId,
                    'del_flag' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        
                // Lưu `module_id` vào mảng
                $moduleIds[] = $module->id;
            }
            
            LogActivityService::log('them_khoa_hoc_vao_lo_trinh', "Thêm khóa học vào lộ trình ID $routeId thành công" , 'success');

            return response()->json([
                'status' => 'success',
                'message' => 'Khóa học đã được thêm vào lộ trình thành công.',
                'data' => $moduleIds,
            ], 201);
        
        } catch (\Exception $e) {
            LogActivityService::log('them_khoa_hoc_vao_lo_trinh', "Thêm khóa học thất bạibại vào lộ trình ID $routeId " . $e->getMessage(), 'fail');
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }        
    }
}
