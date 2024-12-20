<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminComment_PostResource;
use App\Models\Comment_Post;
use App\Models\Post;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AdminComment_PostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $comments_post = Comment_Post::with('user', 'comment', 'post')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminComment_PostResource::collection($comments_post),
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getCommentPost($post_id)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();

            // Tìm bài viết theo post_id và lấy các bình luận liên quan
            $post = Post::with(['comments_post.user:fullname,avatar,role,id'])->find($post_id);

            // Kiểm tra nếu không tìm thấy bài viết
            if (!$post) {
                return response()->json(['message' => 'Không tìm thấy bài viết.'], 404);
            }

            // Lấy tất cả các bình luận
            $comments = $post->comments_post->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment_text' => $comment->comment_text,
                    'del_flag' => $comment->del_flag,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user_id' => $comment->user_id,
                    'fullname' => $comment->user->fullname,
                    'avatar' => $comment->user->avatar,
                    'role' => $comment->user->role,
                    'comment_to' => $comment->comment_to,
                ];
            });

            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Lồng các bình luận trả lời vào bình luận chính
            $finalComments = array_values($comments->filter(function ($comment) {
                return is_null($comment['comment_to']);
            })->map(function ($comment) use ($groupedComments) {
                $comment['replies'] = array_values($groupedComments->get($comment['id'], collect())->map(function ($reply) use ($groupedComments) {
                    $reply['replies'] = array_values($groupedComments->get($reply['id'], collect())->toArray());
                    return $reply;
                })->toArray());
                return $comment;
            })->toArray());

            // Nếu không có comment nào được tìm thấy
            if (empty($finalComments)) {
                return response()->json(['message' => 'Không tìm thấy bình luận nào.'], 404);
            }
            // dd($finalComments);
            // Trả về dữ liệu các comment cùng với post_id
            return response()->json([
                'post_id' => $post->id,
                'comments' => $finalComments,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy bình luận.', 'error' => $e->getMessage()], 500);
        }
    }


    //Chức năng bình luận
    public function commentPost(Request $request, $post_id, $comment_id = null)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'comment_text' => 'required|string|max:255',
            ]);

            // Tạo một bình luận mới
            $comment = new Comment_Post();
            $comment->comment_text = $request->input('comment_text');
            $comment->post_id = $post_id;
            $comment->user_id = $user->id;
            $comment->del_flag = true;

            // Nếu có $comment_id, tức là đang trả lời bình luận
            if ($comment_id) {
                // Kiểm tra xem bình luận cha có tồn tại không
                $parentComment = Comment_Post::find($comment_id);
                if (!$parentComment) {
                    return response()->json(
                        ['message' => 'Không tìm thấy bình luận để trả lời.'],
                        404
                    );
                }

                // Xác định cấp độ của bình luận cha
                if ($parentComment->comment_to) {
                    // Nếu bình luận cha là cấp 2 hoặc cấp 3
                    $grandParentComment = Comment_Post::find($parentComment->comment_to);
                    if ($grandParentComment && $grandParentComment->comment_to) {
                        // Bình luận cha là cấp 3
                        $comment->comment_to = $grandParentComment->id;
                    } else {
                        // Bình luận cha là cấp 2
                        $comment->comment_to = $parentComment->id;
                    }
                } else {
                    // Bình luận cha là cấp 1
                    $comment->comment_to = $parentComment->id;
                }
            }

            // Lưu bình luận vào cơ sở dữ liệu
            $comment->save();

            // Ghi log bình luận thành công
            LogActivityService::log(
                'thao_tac_binh_luan',
                "Đã thêm bình luận cho bài viết {$post_id}: '{$comment->comment_text}'.",
                'success'
            );

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được thêm thành công.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            // Ghi log lỗi khi thêm bình luận
            LogActivityService::log(
                'thao_tac_binh_luan',
                "Lỗi khi thêm bình luận cho bài viết {$post_id}: " . $e->getMessage(),
                'fail'
            );
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi thêm bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Sửa bình luận
    public function updatePost(Request $request, $post_id, $comment_id)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();

            // Tìm bài viết theo $post_id để lấy tên bài viết
            $post = Post::find($post_id);
            if (!$post) {
                return response()->json(['message' => 'Không tìm thấy bài viết.'], 404);
            }

            // Tìm bình luận theo $comment_id và kiểm tra xem bình luận đó có thuộc bài post không
            $comment = Comment_Post::where('id', $comment_id)->where('post_id', $post_id)->first();
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận cần chỉnh sửa.'], 404);
            }

            // Kiểm tra xem người dùng có phải là chủ sở hữu của bình luận hay không
            if ($comment->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Bạn không có quyền chỉnh sửa bình luận này.'
                ], 403);
            }

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'comment_text' => 'required|string|max:255',
            ]);

            // Cập nhật nội dung bình luận
            $comment->comment_text = $request->input('comment_text');
            $comment->updated_at = now(); // Cập nhật thời gian chỉnh sửa

            $comment->save();

            // Ghi log khi cập nhật thành công
            LogActivityService::log(
                'cap_nhat_binh_luan',
                "Cập nhật bình luận {$comment->comment_text} thành công cho bài viết: {$post->title}: ",
                'success'
            );

            return response()->json(['message' => 'Bình luận đã được cập nhật thành công.', 'comment' => $comment], 200);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log('cap_nhat_binh_luan', "Cập nhật bình luận thất bại cho bài viết ID: {$post_id}: " . $e->getMessage(), 'fail');
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Ẩn, hiện bình luận bài viết
    public function statusCommentPost($post_id, $comment_id)
    {
        try {
            // Tìm bài viết theo $post_id để lấy tên bài viết
            $post = Post::find($post_id);
            if (!$post) {
                return response()->json(['message' => 'Không tìm thấy bài viết.'], 404);
            }

            // Tìm bình luận theo $comment_id và kiểm tra xem bình luận đó có thuộc bài post không
            $comment = Comment_Post::where('id', $comment_id)->where('post_id', $post_id)->first();
            if (!$comment) {
                // Ghi log khi không tìm thấy bình luận
                LogActivityService::log('thay_doi_trang_thai_binh_luan', "Không tìm thấy bình luận cần chỉnh sửa cho bài viết: {$post->title}", 'fail');
                return response()->json(['message' => 'Không tìm thấy bình luận cần chỉnh sửa.'], 404);
            }

            // Tìm bình luận chính theo ID
            $comment_post = Comment_Post::findOrFail($comment_id);

            // Kiểm tra nếu bình luận chính đang bị ẩn (del_flag = false)
            if (!$comment_post->del_flag) {
                // Nếu bình luận chính bị ẩn, hiển thị bình luận chính và tất cả bình luận con liên quan
                $comment_post->update(['del_flag' => true]);
                Comment_Post::where('comment_to', $comment_id)->update(['del_flag' => true]);

                // Ghi log khi hiển thị bình luận
                LogActivityService::log('thay_doi_trang_thai_binh_luan', "Đã hiện bình luận $comment->comment_text và tất cả bình luận con cho bài viết: {$post->title}", 'success');
            } else {
                // Nếu bình luận chính đang hiện, ẩn bình luận chính và tất cả bình luận con liên quan
                $comment_post->update(['del_flag' => false]);
                Comment_Post::where('comment_to', $comment_id)->update(['del_flag' => false]);

                // Ghi log khi ẩn bình luận
                LogActivityService::log('thay_doi_trang_thai_binh_luan', "Đã ẩn bình luận $comment->comment_text và tất cả bình luận con cho bài viết: {$post->title}", 'success');
            }

            // Trả về phản hồi thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái bình luận thành công.',
                'data' => new AdminComment_PostResource($comment_post),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log('thay_doi_trang_thai_binh_luan', "Đã xảy ra lỗi khi thay đổi trạng thái bình luận cho bài viết: {$post->title}: " . $e->getMessage(), 'fail');
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getCommentPostAll()
    {
        try {
            $comments_post = Comment_Post::with('user', 'comment', 'post')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminComment_PostResource::collection($comments_post),
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
