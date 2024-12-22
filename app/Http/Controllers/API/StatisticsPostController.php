<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment_Post;
use App\Models\Post;
use App\Models\Post_Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsPostController extends Controller
{
    public function index(): JsonResponse
    { // thông kê tất cả
        try {
            return response()->json([
                'total_posts' => Post::count(),
                'total_categories' => Post_Category::count(),
                'total_comments' => Comment_Post::count(),
                'total_views' => Post::sum('views_post'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thống kê tổng bài viết, tổng comment, tổng danh mục thuộc bài viết, tổng lượt xem bài viết của người dùng đăng nhập
    public function statisticalPostByUser(): JsonResponse
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            $totalPosts = Post::where('user_id', $user->id)->count();

            $totalComments = Post::where('user_id', $user->id)
                ->withCount('comments_post')
                ->get()
                ->sum('comments_post_count');

            $totalCategories = Post::where('user_id', $user->id)
                ->with('category')
                ->distinct('category_id')
                ->count('category_id');

            $totalViews = Post::where('user_id', $user->id)
                ->sum('views_post');

            return response()->json([
                'total_posts' => $totalPosts,
                'total_comments' => $totalComments,
                'total_categories' => $totalCategories,
                'total_views' => $totalViews,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function getTotalViewMouth()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
            }
            $monthlyViews = Post::select(
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('SUM(views_post) as total_views')
            )->where('user_id', $user->id)
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $monthlyViews,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    private function postStatistics()
    {
        // Sử dụng Eloquent để lấy thông tin thống kê
        return [
            'total_posts' => Post::count(),
        ];
    }

    private function postCategoryStatistics()
    {
        $totalCategories = Post_Category::count();
        // $categoriesWithPosts = Post_Category::has('posts')->count();

        return [
            'total_categories' => $totalCategories,
            // 'categories_with_posts' => $categoriesWithPosts,
        ];
    }

    private function commentStatistics()
    {
        $totalComments = Comment_Post::count();
        // $approvedComments = Comment::where('status', 'approved')->count();
        // $pendingComments = Comment::where('status', 'pending')->count();

        return [
            'total_comments' => $totalComments,
            // 'approved_comments' => $approvedComments,
            // 'pending_comments' => $pendingComments,
        ];
    }

    // private function viewPostStatistics()
    // {
    //     $viewsByPost = Post::select('post_id', DB::raw('count(*) as total_views'))
    //         ->groupBy('post_id')
    //         ->get();

    //     return [
    //         'total_views' => $totalViews,
    //         'views_by_post' => $viewsByPost,
    //     ];
    // }
}
