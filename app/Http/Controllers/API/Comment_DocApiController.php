<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment_Document;
use App\Models\Course;
use App\Models\Document;
use Auth;
use Illuminate\Http\Request;
use App\Models\Chapter;
use Illuminate\Support\Str;


class Comment_DocApiController extends Controller
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
    
    // CHƯA SẮP XẾP CHƯA THÊM USER_ID
    public function getCommentDocByCourse($course_id)
    {
        try {
            $user_id = auth('api')->user()->id;
            // Tìm khóa học theo course_id và lấy các bình luận liên quan
            $course = Course::with(['chapters.documents.comments_document.user:fullname,avatar,id']) // Chỉ lấy fullname, avatar, và id
                ->find($course_id);

            // Kiểm tra nếu không tìm thấy khóa học
            if (!$course) {
                return response()->json(['message' => 'Không tìm thấy khóa học.'], 404);
            }

            // Lấy tất cả các bình luận từ khóa học, chỉ lấy những bình luận có status là "active"
            $comments = $course->chapters->flatMap(function ($chapter) use ($user_id) {
                return $chapter->documents->flatMap(function ($document) use ($user_id) {
                    return $document->comments_document->filter(function ($comment) {
                        return $comment->del_flag === true; // Lọc comment có status là "active"
                    })->sortByDesc('updated_at')->map(function ($comment) use ($user_id) {
                        return [
                            'id' => $comment->id,
                            'comment_title' => $comment->comment_title,
                            'comment_text' => $comment->comment_text,
                            'status' => $comment->status,
                            'document_id' => $comment->document_id,
                            'created_at' => $comment->created_at,
                            'updated_at' => $comment->updated_at,
                            'comment_to' => $comment->comment_to,
                            'fullname' => $comment->user->fullname,
                            'user_id' => ($comment->user->id == $user_id) ? $user_id : null, // Lấy fullname từ user
                            'avatar' => $comment->user->avatar
                        ];
                    });
                });
            });

            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Lồng các bình luận trả lời vào bình luận chính (giới hạn ở 2 cấp)
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

            // Trả về dữ liệu các comment chính và các reply
            return response()->json(['comments' => $finalComments], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getCommentDoc($doc_id){
        try {
            $user_id = auth('api')->user()->id;

            // Tìm tài liệu theo doc_id và lấy các bình luận liên quan
            $document = Document::with(['comments_document.user:fullname,avatar,id'])
                ->find($doc_id);

            // Kiểm tra nếu không tìm thấy tài liệu
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Lấy tất cả các bình luận có del_flag = true
            $comments = $document->comments_document
                ->filter(function ($comment) {
                    return $comment->del_flag === true; // Lọc các comment có del_flag = true
                })
                ->map(function ($comment) use ($user_id) {
                    return [
                        'id' => $comment->id,
                        'comment_title' => $comment->comment_title,
                        'comment_text' => $comment->comment_text,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                        'fullname' => $comment->user->fullname, // Lấy fullname từ user
                        'user_id' => ($comment->user->id == $user_id) ? $user_id : null,
                        'avatar' => $comment->user->avatar, // Lấy avatar từ user
                        'comment_to' => $comment->comment_to // Lưu comment_to để kiểm tra khi lồng
                    ];
                })
                ->sortByDesc('created_at'); // Sắp xếp bình luận theo thứ tự mới nhất

            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Lồng các bình luận trả lời vào bình luận chính
            $finalComments = $comments->filter(function ($comment) {
                return is_null($comment['comment_to']); // Lọc ra các bình luận chính (comment_to = null)
            })->map(function ($comment) use ($groupedComments) {
                // Lấy các bình luận trả lời tương ứng (cấp 1)
                $comment['replies'] = $groupedComments->get($comment['id'], collect())->map(function ($reply) use ($groupedComments) {
                    // Lấy các bình luận trả lời của reply (cấp 2)
                    $reply['replies'] = $groupedComments->get($reply['id'], collect())->sortByDesc('created_at'); // Sắp xếp trả lời mới nhất
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
                'comments' => $finalComments->values() // Đảm bảo dữ liệu trả về là mảng tuần tự
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Lấy ra chi tiết các bình luận thuộc bài học
    public function getDetailCommentDoc($doc_id, $comment_id)
    {
        try {
            $user_id = auth('api')->user()->id;

            // Tìm tài liệu theo doc_id và lấy các bình luận liên quan
            $document = Document::with(['comments_document.user:fullname,avatar,id'])->find($doc_id);

            // Kiểm tra nếu không tìm thấy tài liệu
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Tìm bình luận cụ thể theo comment_id trong tài liệu
            $comment = $document->comments_document->filter(function ($comment) use ($comment_id) {
                return $comment->id === $comment_id && $comment->del_flag === true; // Lọc bình luận có del_flag = true
            })->first();

            // Nếu không tìm thấy bình luận
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận này.'], 404);
            }

            // Lấy chi tiết của bình luận
            $commentDetail = [
                'id' => $comment->id,
                'comment_title' => $comment->comment_title,
                'comment_text' => $comment->comment_text,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'fullname' => $comment->user->fullname,
                'avatar' => $comment->user->avatar,
                'comment_to' => $comment->comment_to,
                'user_id' => ($comment->user->id == $user_id) ? $user_id : null,
                'replies' => [] // Khởi tạo mảng trả lời
            ];

            // Hàm đệ quy để lấy các bình luận trả lời sâu hơn (không sắp xếp)
            $getReplies = function ($parentCommentId) use ($document, &$getReplies, $user_id) {
                return $document->comments_document->filter(function ($reply) use ($parentCommentId) {
                    return $reply->comment_to === $parentCommentId && $reply->del_flag === true;
                })->sortByDesc('created_at')->map(function ($reply) use ($getReplies, $user_id) {
                    return [
                        'id' => $reply->id,
                        'comment_title' => $reply->comment_title,
                        'comment_text' => $reply->comment_text,
                        'created_at' => $reply->created_at,
                        'updated_at' => $reply->updated_at,
                        'fullname' => $reply->user->fullname,
                        'avatar' => $reply->user->avatar,
                        'comment_to' => $reply->comment_to,
                        'user_id' => ($reply->user->id == $user_id) ? $user_id : null,
                        'replies' => $getReplies($reply->id) // Đệ quy
                    ];
                })->values()->toArray();
            };

            // Lấy các bình luận trả lời cấp 1 và sắp xếp tăng dần theo `created_at`
            $commentDetail['replies'] = $document->comments_document
                ->filter(fn($reply) => $reply->comment_to === $comment->id && $reply->del_flag === true)
                ->sortByDesc('created_at') // Sắp xếp tăng dần
                ->map(fn($reply) => [
                    'id' => $reply->id,
                    'comment_title' => $reply->comment_title,
                    'comment_text' => $reply->comment_text,
                    'created_at' => $reply->created_at,
                    'updated_at' => $reply->updated_at,
                    'fullname' => $reply->user->fullname,
                    'avatar' => $reply->user->avatar,
                    'comment_to' => $reply->comment_to,
                    'user_id' => ($reply->user->id == $user_id) ? $user_id : null,
                    'replies' => $getReplies($reply->id) // Đệ quy
                ])->values()->toArray(); // Reset chỉ mục

            // Trả về chi tiết bình luận và tài liệu
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy ra chi tiết bình luận theo bài học thành công.',
                'document_id' => $document->id,
                'comment' => $commentDetail
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy bình luận.', 'error' => $e->getMessage()], 500);
        }

    }

    // Lấy ra tiêu đề bình luận theo bài học
    public function getTitleCommentDoc($doc_id)
    {
        try {
            // Tìm tài liệu theo doc_id và lấy các bình luận liên quan
            $document = Document::with(['comments_document.user:fullname,avatar,id'])
                ->find($doc_id);

            // Kiểm tra nếu không tìm thấy tài liệu
            if (!$document) {
                return response()->json(['message' => 'Không tìm thấy tài liệu.'], 404);
            }

            // Lấy tất cả các bình luận có del_flag = true và sắp xếp theo created_at giảm dần
            $comments = $document->comments_document
                ->filter(fn($comment) => $comment->del_flag === true)
                ->sortByDesc('created_at') // Sắp xếp mới nhất
                ->map(fn($comment) => [
                    'id' => $comment->id,
                    'comment_title' => $comment->comment_title,
                    'comment_to' => $comment->comment_to // Lưu comment_to để kiểm tra khi lồng
                ]);

            // Nhóm các bình luận theo comment_to
            $groupedComments = $comments->groupBy('comment_to');

            // Xử lý bình luận gốc và đếm số trả lời
            $finalComments = $comments->filter(fn($comment) => is_null($comment['comment_to']))
                ->map(fn($comment) => [
                    'id' => $comment['id'],
                    'title' => $comment['comment_title'],
                    'replies_count' => $groupedComments->get($comment['id'], collect())->count()
                ])->values(); // Reset lại chỉ mục để trả về dữ liệu sạch

            // Nếu không có comment nào được tìm thấy
            if ($finalComments->isEmpty()) {
                return response()->json(['message' => 'Không tìm thấy bình luận nào.'], 404);
            }

            // Trả về dữ liệu
            return response()->json([
                'document_id' => $document->id, // Trả về document_id làm cha
                'comments' => $finalComments
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi lấy bình luận.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    // Lấy ra các bình luận thuộc khóa học
    public function geTotalCommentDoc($course_id)
    {
        try {
            $user_id = auth('api')->user()->id;

            // Kiểm tra xem $course_id có hợp lệ không
            if (!Str::isUlid($course_id) || !Course::where('id', $course_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ',
                    'data' => null,
                ], 400);
            }

            // Kiểm tra quyền truy cập khóa học dựa trên Enrollment và cờ del_flag
            $course = Course::whereHas('modules.enrollments', function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->where('enroll', true)
                    ->where('del_flag', true);
            })->where('id', $course_id)->where('del_flag', true)->first();

            if (is_null($course)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học không tồn tại hoặc bạn không có quyền truy cập.',
                    'data' => null,
                ], 403);
            }

            // Lấy các chapter của khóa học
            $chapters = Chapter::where('course_id', $course_id)->orderBy('serial_chapter', 'asc')->get();

            if ($chapters->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có chương nào thuộc khóa học này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $chapterData = $chapters->map(function ($chapter) {
                $documents = Document::select('id', 'name_document', 'serial_document', 'type_document', 'del_flag', 'updated_at')
                    ->where('chapter_id', $chapter->id)
                    ->where('del_flag', true)
                    ->orderBy('serial_document', 'asc')
                    ->get();

                $documentData = $documents->map(function ($document) {
                    $documentDetails = [
                        'document_id' => $document->id,
                        'name_document' => $document->name_document,
                        'type_document' => $document->type_document,
                        'comment_count' => $document->comments_document()->count(), // Đếm số lượng comments
                    ];
                    return $documentDetails; // Trả về dữ liệu đã xử lý
                });

                return [
                    'chapter_id' => $chapter->id,
                    'chapter_name' => $chapter->name_chapter,
                    'documents' => $documentData,
                ];
            });

            return response()->json([
                'course_id' => $course_id,
                'data' => $chapterData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Chức năng bình luận, trả lời bình luận
    public function commentDoc(Request $request, $doc_id, $comment_id = null)
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
            ],[
                'comment_title.required' => 'Tiêu đề bình luận không được để trống.',
                'comment_title.max' => 'Tiêu đ�� bình luận không quá 255 ký tự.',
                'comment_text.max' => 'Nội dung bình luận không quá 255 ký tự.',
                'comment_text.required' => 'Nội dung bình luận không được để trống.',
            ]);

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

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được thêm thành công.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
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

            // Cập nhật bình luận hoặc trả lời bình luận
            $comment->comment_title = $request->input('comment_title');
            $comment->comment_text = $request->input('comment_text');
            $comment->save();

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận đã được cập nhật thành công.', 'comment' => $comment], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật bình luận.', 'error' => $e->getMessage()], 500);
        }
    }

    // Chức năng xóa bình luận (user_id đăng nhập === user_id của bình luận)
    public function deleteCommentDoc($doc_id, $comment_id)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Tìm bình luận muốn xóa dựa trên doc_id và comment_id
            $comment = Comment_Document::where('id', $comment_id)
                ->where('document_id', $doc_id)
                ->first();

            // Kiểm tra nếu bình luận không tồn tại
            if (!$comment) {
                return response()->json(['message' => 'Không tìm thấy bình luận.'], 404);
            }

            // Kiểm tra xem người dùng có quyền xóa bình luận không
            if ($comment->user_id !== $user->id) {
                return response()->json(['message' => 'Bạn không có quyền xóa bình luận này.'], 403);
            }

            // Xóa tất cả các bình luận trả lời nếu có
            $replies = Comment_Document::where('comment_to', $comment_id)->get();
            foreach ($replies as $reply) {
                $reply->delete(); // Xóa từng bình luận trả lời
            }

            // Xóa bình luận chính
            $comment->delete();

            // Trả về phản hồi thành công
            return response()->json(['message' => 'Bình luận và các phản hồi đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json(['message' => 'Đã xảy ra lỗi khi xóa bình luận.', 'error' => $e->getMessage()], 500);
        }
    }
}
