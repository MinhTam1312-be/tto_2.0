<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminComment_DocumentResource;
use App\Models\Comment_Document;
use App\Models\Document;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class CommentsInstructorApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $comments_doc = Comment_Document::with('user', 'comment', 'document')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => AdminComment_DocumentResource::collection($comments_doc),
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

    // Gọi ra các bình luận của bài học
    public function getCommentDoc($doc_id)
    {
        try {
            // Tìm tài liệu theo doc_id và lấy các bình luận liên quan
            $document = Document::with(['comments_document.user:fullname,avatar,id'])
                ->find($doc_id);

            // Kiểm tra nếu không tìm thấy tài liệu
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Lấy tất cả các bình luận
            $comments = $document->comments_document->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment_title' => $comment->comment_title,
                    'comment_text' => $comment->comment_text,
                    'del_flag' => $comment->del_flag,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user_id' => $comment->user_id,
                    'fullname' => $comment->user->fullname, // Lấy fullname từ user
                    'role' => $comment->user->role, // Lấy role từ user
                    'avatar' => $comment->user->avatar, // Lấy avatar từ user
                    'comment_to' => $comment->comment_to // Lưu comment_to để kiểm tra khi lồng
                ];
            });


            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Lồng các bình luận trả lời vào bình luận chính
            $finalComments = $comments->filter(function ($comment) {
                return is_null($comment['comment_to']); // Lọc ra các bình luận chính (comment_to = null)
            })->map(function ($comment) use ($groupedComments) {
                // Lấy các bình luận trả lời tương ứng (cấp 1)
                $comment['replies'] = $groupedComments->get($comment['id'], collect())->map(function ($reply) use ($groupedComments) {
                    // Lấy các bình luận trả lời của reply (cấp 2)
                    $reply['replies'] = $groupedComments->get($reply['id'], collect());
                    return $reply;
                });
                return $comment;
            });

            // Nếu không có comment nào được tìm thấy
            if ($finalComments->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy bình luận nào.'], 404);
            }

            // Trả về dữ liệu các comment có status "active" cùng với document_id
            return response()->json([
                'document_id' => $document->id, // Trả về document_id làm cha
                'comments' => $finalComments
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Chức năng bình luận, trả lời bình luận
    public function commentDoc(Request $request, $doc_id, $comment_id = null)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'comment_title' => 'required|string|max:255',
                'comment_text' => 'required|string|max:255',
            ]);

            // Tìm tài liệu theo $doc_id để lấy tên tài liệu
            $document = Document::find($doc_id);
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Tạo một bình luận mới
            $comment = new Comment_Document();
            $comment->comment_title = $request->input('comment_title');
            $comment->comment_text = $request->input('comment_text');
            $comment->document_id = $doc_id;
            $comment->del_flag = true;
            $comment->user_id = $user->id;

            // Nếu có $comment_id, tức là đang trả lời bình luận
            if ($comment_id) {
                // Tìm bình luận cha cho bình luận hiện tại
                $parentComment = Comment_Document::find($comment_id);
                if (!$parentComment) {
                    return response()->json(['message' => 'Không tìm thấy bình luận để trả lời.'], 404);
                }

                // Kiểm tra cấp độ của bình luận cha để xác định comment_to cho bình luận mới
                if ($parentComment->comment_to) {
                    // Nếu bình luận cha đã có comment_to, tức là bình luận cấp 2 hoặc cấp 3
                    $grandParentComment = Comment_Document::find($parentComment->comment_to);

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

            // Ghi log hoạt động
            LogActivityService::log(
                'them_binh_luan_bai_hoc',
                "Thêm bình luận: '{$comment->comment_text}' cho tài liệu: {$document->name_document}",
                'success'
            );

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được thêm thành công.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'them_binh_luan_bai_hoc',
                "Lỗi khi thêm bình luận cho tài liệu: {$document->name_document}: " . $e->getMessage(),
                'fail'
            );

            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi thêm bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Chức năng sửa bình luận (user_id đăng nhập === user_id của bình luận)
    public function updateCommentDoc(Request $request, $doc_id, $comment_id)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Kiểm tra và xác thực dữ liệu đầu vào
            $request->validate([
                'comment_title' => 'required|string|max:255',
                'comment_text' => 'required|string|max:255',
            ]);

            // Tìm bình luận muốn sửa dựa trên doc_id và comment_id
            $comment = Comment_Document::where('id', $comment_id)
                ->where('document_id', $doc_id)
                ->first();

            // Kiểm tra nếu bình luận không tồn tại
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận trong document này.'], 404);
            }

            // Kiểm tra xem người dùng có quyền sửa bình luận không
            if ($comment->user_id !== $user->id) {
                return response()->json(['message' => 'Bình luận này không phải của bạn.'], 403);
            }

            // Lấy tên tài liệu để ghi log
            $document = Document::find($doc_id);
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Cập nhật bình luận hoặc trả lời bình luận
            $comment->comment_title = $request->input('comment_title');
            $comment->comment_text = $request->input('comment_text');
            $comment->save();

            // Ghi log khi cập nhật bình luận
            LogActivityService::log(
                'cap_nhat_binh_luan_bai_hoc',
                "Cập nhật bình luận: '{$comment->comment_text}' cho tài liệu: {$document->name_document}",
                'success'
            );

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được cập nhật thành công.', 'comment' => $comment], 200);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'cap_nhat_binh_luan_bai_hoc',
                "Lỗi khi cập nhật bình luận cho tài liệu: {$document->name_document}: " . $e->getMessage(),
                'fail'
            );

            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Ẩn, hiện bình luận của khóa học
    public function statusCommentDoc($doc_id, $comment_id)
    {
        try {
            // Tìm bình luận theo $comment_id và kiểm tra xem bình luận đó có thuộc tài liệu không
            $comment = Comment_Document::where('id', $comment_id)->where('document_id', $doc_id)->first();
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận cần chỉnh sửa.'], 404);
            }

            // Tìm tài liệu liên quan để lấy tên tài liệu
            $document = Document::find($doc_id);
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Tìm Người dùng theo ID, nếu không có sẽ throw ModelNotFoundException
            $comment_doc = Comment_Document::findOrFail($comment_id);

            // Lưu trạng thái cũ của del_flag để log
            $previous_del_flag = $comment_doc->del_flag;

            // Xác thực dữ liệu đầu vào và thay đổi trạng thái
            $comment_doc->update(['del_flag' => !$comment_doc->del_flag]);

            // Ghi log khi thay đổi trạng thái
            LogActivityService::log(
                'thay_doi_trang_thai_binh_luan',
                "Đã " . ($comment_doc->del_flag ? 'hiện' : 'ẩn') . " bình luận: '{$comment_doc->comment_text}' cho tài liệu: {$document->name_document} " . ($previous_del_flag ? 'hiện' : 'ẩn'),
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái bình luận thành công.',
                'data' => new AdminComment_DocumentResource($comment_doc),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'thay_doi_trang_thai_binh_luan',
                "Lỗi khi thay đổi trạng thái bình luận cho tài liệu: {$document->name_document}: " . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
