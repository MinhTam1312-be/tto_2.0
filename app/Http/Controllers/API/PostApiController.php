<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $posts = Post::where('del_flag', true)->where('del_flag', true)->where('status_post', 'success')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => PostResource::collection($posts),
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
            $post = Post::where('del_flag', true)
                ->where('del_flag', true)->where('status_post', 'success')
                ->findOrFail($id);

            // Kiểm tra nếu khóa học không tồn tại hoặc có del_flag là false
            if (!$post) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'ID không hợp lệ hoặc khóa học đã bị ẩn.',
                    'data' => null,
                ], 404);
            }

            Post::where('id', $id)->increment('views_post');
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => new PostResource($post)
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


    public function postHighestView($limit)
    {
        try {
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số limit không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Lấy các bài viết có del_flag là true, sắp xếp theo views_post giảm dần
            $posts = Post::where('del_flag', true) // Điều kiện del_flag
                ->where('status_post', 'success')
                ->orderBy('views_post', 'desc')
                ->limit($limit)
                ->get();

            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có dữ liệu phù hợp',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => $posts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    // Bài viết nổi bật lấy theo bài viết nhiều bình luận nhất
    public function getPostsHighestComment($limit)
    {
        try {
            // Kiểm tra tính hợp lệ của tham số limit
            if (!is_numeric($limit) || $limit <= 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tham số limit không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Đếm số lượng bình luận theo post_id và sắp xếp giảm dần
            $posts = Post::where('del_flag', true)->where('status_post', 'success') // Lọc bài viết có del_flag = true
                ->with([
                    'comments_post' => function ($query) {
                        $query->where('del_flag', true); // Điều kiện del_flag = true cho bảng comments
                    }
                ])
                ->get()
                ->sortByDesc(function ($post) {
                    return $post->comments_post->count(); // Đếm số lượng bình luận từ mối quan hệ
                })
                ->take($limit);

            // Kiểm tra nếu không có dữ liệu phù hợp
            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có dữ liệu phù hợp',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lấy dữ liệu thành công',
                'data' => PostResource::collection($posts),
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    public function filterTagPost(Request $request)
    {
        try {
            // Lấy tham số tags từ request (có thể là chuỗi hoặc mảng)
            $tagsInput = $request->input('tags');

            // Kiểm tra nếu không có tags
            if (empty($tagsInput)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Vui lòng cung cấp ít nhất một tag để lọc bài viết.',
                    'data' => null,
                ], 400); // 400 Bad Request
            }

            // Tách tags thành mảng nếu là chuỗi
            $tags = is_array($tagsInput) ? $tagsInput : $this->extractTags($tagsInput);

            // Kiểm tra nếu không có tags hợp lệ
            if (empty($tags)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy tags hợp lệ trong dữ liệu cung cấp.',
                    'data' => null,
                ], 400); // 400 Bad Request
            }

            // Tạo truy vấn để lọc bài viết
            $posts = Post::where('del_flag', true)->where('status_post', 'success') // Chỉ lấy bài viết có del_flag = true
                ->whereHas('category', function ($query) use ($tags) {
                    $query->where(function ($query) use ($tags) {
                        foreach ($tags as $tag) {
                            $query->orWhere('tags', 'LIKE', '%' . $tag . '%'); // Lọc theo từng tag gần giống
                        }
                    });
                })
                ->get();

            // Kiểm tra nếu không tìm thấy bài viết nào
            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy bài viết nào phù hợp với tags.',
                    'data' => null,
                ], 404); // 404 Not Found
            }

            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'message' => 'Lọc bài viết thành công.',
                'tag' => $tagsInput,
                'data' => PostResource::collection($posts),
            ], 200); // 200 OK
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi trong quá trình lọc: ' . $e->getMessage(),
                'data' => null,
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Tách chuỗi tags thành mảng các tags.
     *
     * @param string $tagsString
     * @return array
     */
    private function extractTags($tagsString)
    {
        // Sử dụng regex để tách các từ sau dấu #
        preg_match_all('/#(\w+)/u', $tagsString, $matches);
        return $matches[1]; // Trả về mảng tags
    }

    public function getPostTopEngarang($slug_post)
    {
        try {
            $post = Post::where('slug_post', $slug_post)
                ->where('del_flag', true)
                ->where('status_post', 'success')
                ->with('user') // Giả sử `user` có chứa fullname
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bài viết không tồn tại.',
                ], 404); // 404 Not Found
            }

            // Lấy các bài viết cùng user nhưng không phải chính nó
            $posts = Post::where('user_id', $post->user_id)
                ->where('id', '!=', $post->id)
                ->where('del_flag', true)
                ->where('status_post', 'success')
                ->get();

            // Chuyển đổi $post thành mảng với cấu trúc yêu cầu
            $postArray = [
                'id' => $post->id,
                'title_post' => $post->title_post,
                'slug_post' => $post->slug_post,
                'content_post' => $post->content_post,
                'img_post' => $post->img_post, // Tách chuỗi ảnh thành mảng
                'views_post' => $post->views_post,
                'status_post' => $post->status_post,
                'del_flag' => $post->del_flag,
                'user_id' => $post->user_id,
                'category_id' => $post->category_id,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'fullname' => $post->user->fullname ?? null, // Lấy fullname từ quan hệ user
            ];

            // Chuyển đổi $posts thành mảng với cấu trúc tương tự
            $postsArray = $posts->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title_post' => $p->title_post,
                    'slug_post' => $p->slug_post,
                    'content_post' => $p->content_post,
                    'img_post' => $p->img_post, // Tách chuỗi ảnh thành mảng
                    'views_post' => $p->views_post,
                    'status_post' => $p->status_post,
                    'del_flag' => $p->del_flag,
                    'user_id' => $p->user_id,
                    'category_id' => $p->category_id,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                    'fullname' => $p->user->fullname ?? null, // Lấy fullname từ quan hệ user
                ];
            })->toArray();

            // Gộp $postArray và $postsArray
            $postMerge = array_merge([$postArray], $postsArray);
            // Trả về kết quả
            return response()->json([
                'status' => 'success',
                'message' => 'Lọc bài viết thành công.',
                'data' => $postMerge,
            ], 200); // 200 OK
        } catch (ModelNotFoundException $e) {
            // Xử lý khi không tìm thấy bài viết
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy bài viết.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi trong quá trình lọc: ' . $e->getMessage(),
                'data' => null,
            ], 500); // 500 Internal Server Error
        }
    }
}
