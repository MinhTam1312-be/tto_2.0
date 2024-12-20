<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment_Post;
use App\Models\Post;
use Illuminate\Http\Request;

class Comment_PostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    // Lấy ra các bình luận thuộc bài viết
    public function getCommentPost($post_id)
    {
        try {

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
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'fullname' => $comment->user->fullname,
                    'avatar' => $comment->user->avatar,
                    'role' => $comment->user->role,
                    'comment_to' => $comment->comment_to
                ];
            });

            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Lồng các bình luận trả lời vào bình luận chính
            $finalComments = $comments->filter(function ($comment) {
                return is_null($comment['comment_to']);
            })->map(function ($comment) use ($groupedComments) {
                $comment['replies'] = $groupedComments->get($comment['id'], collect())->map(function ($reply) use ($groupedComments) {
                    $reply['replies'] = $groupedComments->get($reply['id'], collect());
                    return $reply;
                });
                return $comment;
            });

            // Nếu không có comment nào được tìm thấy
            if ($finalComments->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy bình luận nào.'], 404);
            }

            // Trả về dữ liệu các comment cùng với post_id
            return response()->json([
                'post_id' => $post->id,
                'comments' => $finalComments
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
                    return response()->json(['message' => 'Không tìm thấy bình luận để trả lời.'], 404);
                }

                // Xác định cấp độ của bình luận cha
                if ($parentComment->comment_to) {
                    // Nếu bình luận cha là cấp 2 hoặc cấp 3, tức là có `comment_to`
                    $grandParentComment = Comment_Post::find($parentComment->comment_to);

                    if ($grandParentComment && $grandParentComment->comment_to) {
                        // Nếu có `grandParentComment->comment_to`, tức là bình luận cha là cấp 3
                        // => Gán `comment_to` của bình luận mới là bình luận cấp 2
                        $comment->comment_to = $grandParentComment->id;
                    } else {
                        // Bình luận cha là cấp 2, gán `comment_to` của bình luận mới là bình luận cấp 3
                        $comment->comment_to = $parentComment->id;
                    }
                } else {
                    // Bình luận cha là cấp 1, gán `comment_to` của bình luận mới là bình luận cấp 2
                    $comment->comment_to = $parentComment->id;
                }
            }


            // Lưu bình luận vào cơ sở dữ liệu
            $comment->save();

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được thêm thành công.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
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

            // Tìm bình luận theo $comment_id và kiểm tra xem bình luận đó có thuộc bài post không
            $comment = Comment_Post::where('id', $comment_id)->where('post_id', $post_id)->first();
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận cần chỉnh sửa.'], 404);
            }

            // Kiểm tra xem người dùng có phải là chủ sở hữu của bình luận hay không
            if ($comment->user_id !== $user->id) {
                return response()->json(['message' => 'Bạn không có quyền chỉnh sửa bình luận này.'], 403);
            }

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'comment_text' => 'required|string|max:255',
            ]);

            // Cập nhật nội dung bình luận
            $comment->comment_text = $request->input('comment_text');
            $comment->updated_at = now(); // Cập nhật thời gian chỉnh sửa

            $comment->save();

            return response()->json(['message' => 'Bình luận đã được cập nhật thành công.', 'comment' => $comment], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Chức năng xóa bình luận bài viết
    public function deleteComment($post_id, $comment_id)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Tìm bình luận theo $comment_id và kiểm tra xem bình luận đó có thuộc bài post không
            $comment = Comment_Post::where('id', $comment_id)->where('post_id', $post_id)->first();
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận để xóa.'], 404);
            }

            // Kiểm tra xem người dùng có phải là chủ sở hữu của bình luận hay không
            if ($comment->user_id !== $user->id) {
                return response()->json(['message' => 'Bạn không có quyền xóa bình luận này.'], 403);
            }

            // Xóa bình luận
            $comment->delete();

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi xóa bình luận.', 'error' => $e->getMessage()], 500);
        }
    }


}
