<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivitiesLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivitiesLogController extends Controller
{
    // Gọi ra lịch sử hoạt động
    public function getActivitiesLog($parse = 'asc', $limit = null)
    {
        try {
            // Kiểm tra parse, mặc định là 'asc' nếu không có giá trị
            $parse = in_array(strtolower($parse), ['asc', 'desc']) ? strtolower($parse) : 'asc';

            // Nếu không truyền limit, lấy tất cả (null thì không limit)
            $query = ActivityLog::orderBy('created_at', $parse);

            // Nếu có limit thì áp dụng limit, nếu không thì lấy hết
            if ($limit !== null && is_numeric($limit) && $limit > 0) {
                $query = $query->limit($limit);
            }

            // Lấy các hoạt động từ bảng activities
            $activities = $query->get();

            // Trả về dữ liệu hoạt động
            return response()->json([
                'status' => 'success',
                'data' => ActivitiesLogResource::collection($activities)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi khi lấy thông tin hoạt động: ' . $e->getMessage(),
            ], 500);
        }
    }

}
