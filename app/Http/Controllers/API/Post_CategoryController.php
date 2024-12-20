<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post_Category;
use Illuminate\Http\Request;

class Post_CategoryController extends Controller
{
    public function getCategoriesByLimit($limit){
        try {
            // Kiểm tra và giới hạn giá trị limit hợp lệ
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số limit không hợp lệ',
                    'data' => null,
                ], 400);
            }
    
            // Lấy danh sách danh mục bài viết theo giới hạn
            $categories = Post_Category::where('del_flag', true)->limit($limit)->get();
    
            // Kiểm tra nếu không có kết quả
            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có danh mục bài viết nào.',
                ], 404);
            }
    
            // Trả về kết quả
            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh mục bài viết.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
