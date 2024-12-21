<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPostResource;
use App\Models\Course;
use App\Models\Post;
use Illuminate\Http\Request;

class StatisticsMarketingApiController extends Controller
{
    // Controller method to handle statistics
    public function statisticsPosts($limit = null)
    {
        try {
            $query = Post::query();

            $manyComments = (clone $query)->withCount('comments_post')
                ->orderByDesc('comments_post_count')
                ->limit($limit)
                ->get();

            $littleComments = (clone $query)->withCount('comments_post')
                ->orderBy('comments_post_count', 'asc')
                ->limit($limit)
                ->get();

            $manyViews = (clone $query)->orderByDesc('views_post')
                ->limit($limit)
                ->get();

            $littleViews = (clone $query)->orderBy('views_post', 'asc')
                ->limit($limit)
                ->get();

            $newPosts = (clone $query)->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => [
                    'manyComments' => AdminPostResource::collection($manyComments),
                    'littleComments' => AdminPostResource::collection($littleComments),
                    'manyViews' => AdminPostResource::collection($manyViews),
                    'littleViews' => AdminPostResource::collection($littleViews),
                    'newPosts' => AdminPostResource::collection($newPosts),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Lấy tổng bài viết danh mục bình luận, lượt xem của quyền makerting
    public function totalPostCategoryView()
    {
        try {
            $user_id = auth('api')->user()->id;
            // $user_id = '01JEQ8725SJS1QKH0N2Q52X72Y';
            // đếm bài viết của user
            $postCount = Post::where('user_id', $user_id)->count();
            $categoryCount = Post::where('user_id', $user_id)
                ->whereNotNull('category_id')
                ->distinct('category_id')
                ->count('category_id');
            $commentCount = Post::where('user_id', $user_id)
                ->withCount('comments_post')
                ->get()
                ->sum('comments_post_count');
            $totalViews = Post::where('user_id', $user_id)->sum('views_post');

            $result = [
                'post_count' => $postCount,
                'category_count' => $categoryCount,
                'comment_count' => $commentCount,
                'total_views' => $totalViews,
            ];

            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'message' => 'Thống kê thành công.',
                'data' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Hàm viết lấy ra bài viết có nhiều comment nhiều nhất
    public function statisticsPostManyComments($limit = null)
    {
        try {
            $topPosts = Post::withCount('comments_post')
                ->orderByDesc('comments_post_count')
                ->limit($limit) // Giới hạn số lượng kết quả
                ->get();
            $topViews = Post::orderByDesc('views_post')
                ->limit($limit) // Giới hạn số lượng kết quả
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'topComment' => AdminPostResource::collection($topPosts),
                'view' => AdminPostResource::collection($topViews),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Hàm viết lấy ra bài viết có ít comment nhiều nhất
    public function statisticsPostLittleComments($limit = null)
    {
        try {
            $botPosts = Post::withCount('comments_post')
                ->orderBy('comments_post_count', 'asc')
                ->limit($limit) // Giới hạn số lượng kết quả
                ->get();
            $botViews = Post::orderBy('views_post', 'asc')
                ->limit($limit) // Giới hạn số lượng kết quả
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'botComment' => AdminPostResource::collection($botPosts),
                'botView' => AdminPostResource::collection($botViews),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê bài viết mới nhất
    public function statisticsPostNew($limit = null)
    {
        try {
            $newPosts = Post::orderBy('updated_at', 'desc')
                ->limit($limit) // Giới hạn số lượng kết quả
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'new post' => AdminPostResource::collection($newPosts),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    
}
