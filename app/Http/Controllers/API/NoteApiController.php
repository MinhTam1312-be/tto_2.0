<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Document;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NoteApiController extends Controller
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

    // Lấy ra các note theo người dùng
    public function getNoteByUser($parse = 'asc')
    {
        try {
            // Lấy thông tin user hiện tại
            $user_id = auth('api')->user()->id;

            // Kiểm tra giá trị của $parse (asc hoặc desc)
            $sortOrder = strtolower($parse) === 'desc' ? 'desc' : 'asc';

            // Lấy tất cả ghi chú của user hiện tại
            $notes = Note::where('user_id', $user_id)
                ->where('del_flag', true) // Kiểm tra del_flag cho Note
                ->orderBy('updated_at', $sortOrder) // Sắp xếp theo updated_at
                ->get();

            if ($notes->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có ghi chú nào cho người dùng này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $Notes = $notes->map(function ($note) {
                return [
                    'document_id' => $note->document->id,
                    'chapter_name' => $note->document->chapter->name_chapter,
                    'note_id' => $note->id,
                    'title_note' => $note->title_note,
                    'content_note' => $note->content_note,
                    'cache_time_note' => $note->cache_time_note,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $Notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }


    // Lấy ra các note theo khóa học
    public function getNoteByCourse($course_id, $parse = 'asc')
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của $course_id
            if (!Str::isUlid($course_id) || !Course::where('id', $course_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id khóa học không hợp lệ.',
                    'data' => null,
                ], 400);
            }

            // Kiểm tra nếu người dùng chưa đăng ký khóa học này
            $isEnrolled = Course::where('id', $course_id)
                ->whereHas('modules.enrollments', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('enroll', true);
                })
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Khóa học này bạn chưa đăng ký',
                    'data' => null,
                ], 403);
            }

            // Kiểm tra giá trị của $parse (asc hoặc desc)
            $sortOrder = strtolower($parse) === 'desc' ? 'desc' : 'asc';

            // Lấy tất cả ghi chú của user hiện tại liên quan đến course_id
            $notes = Note::whereHas('document.chapter.course', function ($query) use ($course_id) {
                $query->where('id', $course_id)
                    ->where('del_flag', true); // Kiểm tra del_flag cho Course
            })
                ->where('user_id', $user->id)
                ->where('del_flag', true) // Kiểm tra del_flag cho Note
                ->orderBy('updated_at', $sortOrder) // Sắp xếp theo updated_at
                ->get();

            if ($notes->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có ghi chú nào cho khóa học này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $Notes = $notes->map(function ($note) {
                return [
                    'document_id' => $note->document->id,
                    'chapter_name' => $note->document->chapter->name_chapter,
                    'note_id' => $note->id,
                    'title_note' => $note->title_note,
                    'content_note' => $note->content_note,
                    'cache_time_note' => $note->cache_time_note,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $Notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    // Lấy ra các note theo chương
    public function getNoteByChapter($chapter_id, $parse = 'asc')
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của $chapter_id
            if (!Str::isUlid($chapter_id) || !Chapter::where('id', $chapter_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id chương không hợp lệ.',
                    'data' => null,
                ], 400);
            }

            // Kiểm tra nếu người dùng chưa đăng ký khóa học liên quan đến chapter này
            $isEnrolled = Chapter::where('id', $chapter_id)
                ->whereHas('course.modules.enrollments', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('enroll', true);
                })
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Bạn chưa đăng ký khóa học liên quan đến chương này.',
                    'data' => null,
                ], 403);
            }

            // Kiểm tra giá trị của $parse (asc hoặc desc)
            $sortOrder = strtolower($parse) === 'desc' ? 'desc' : 'asc';

            // Lấy tất cả ghi chú của user liên quan đến chapter_id
            $notes = Note::whereHas('document.chapter', function ($query) use ($chapter_id) {
                $query->where('id', $chapter_id)
                    ->where('del_flag', true); // Kiểm tra del_flag cho Chapter
            })
                ->where('user_id', $user->id)
                ->where('del_flag', true) // Kiểm tra del_flag cho Note
                ->orderBy('updated_at', $sortOrder) // Sắp xếp theo updated_at
                ->get();

            if ($notes->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có ghi chú nào cho chương này.',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $Notes = $notes->map(function ($note) {
                return [
                    'document_id' => $note->document->id,
                    'chapter_name' => $note->document->chapter->name_chapter,
                    'note_id' => $note->id,
                    'title_note' => $note->title_note,
                    'content_note' => $note->content_note,
                    'cache_time_note' => $note->cache_time_note,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $Notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }


    // Gọi ra các ghi chú theo bài học
    public function getNoteByDoc($doc_id, $parse = 'asc')
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của $doc_id
            if (!Str::isUlid($doc_id) || !Document::where('id', $doc_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id tài liệu không hợp lệ.',
                    'data' => null,
                ], 400);
            }

            // Xác định thứ tự sắp xếp
            $sortOrder = strtolower($parse) === 'desc' ? 'desc' : 'asc';

            // Lấy ghi chú theo doc_id và user_id, sắp xếp theo cache_time_note
            $notes = Note::where('document_id', $doc_id)
                ->where('user_id', $user->id) // Chỉ lấy ghi chú của user hiện tại
                ->where('del_flag', true) // Kiểm tra del_flag cho Note
                ->orderBy('cache_time_note', $sortOrder) // Sắp xếp theo cache_time_note
                ->get();

            if ($notes->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có ghi chú nào cho tài liệu này',
                    'data' => null,
                ], 404);
            }

            // Định dạng dữ liệu trả về
            $Notes = $notes->map(function ($note) {
                return [
                    'document_id' => $note->document->id,
                    'chapter_name' => $note->document->chapter->name_chapter,
                    'note_id' => $note->id,
                    'title_note' => $note->title_note,
                    'content_note' => $note->content_note,
                    'cache_time_note' => $note->cache_time_note,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $Notes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }


    // Chức năng thêm ghi chú
    public function postNote(Request $request, $doc_id)
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của $doc_id
            if (!Str::isUlid($doc_id) || !Document::where('id', $doc_id)->where('del_flag', true)->exists()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Id tài liệu không hợp lệ.',
                    'data' => null,
                ], 400);
            }

            // Validate dữ liệu đầu vào
            $validatedData = $request->validate([
                'title_note' => 'required|string|max:255',
                'content_note' => 'required|string',
                'cache_time_note' => 'required|integer|min:0', // Thời gian có thể là số nguyên hoặc null
            ], [
                'title_note.required' => 'Tiêu đề ghi chú là bắt buộc.',
                'title_note.string' => 'Tiêu đề ghi chú phải là chuỗi ký tự.',
                'title_note.max' => 'Tiêu đề ghi chú không được vượt quá 255 ký tự.',
                'content_note.required' => 'Nội dung ghi chú là bắt buộc.',
                'content_note.string' => 'Nội dung ghi chú phải là chuỗi ký tự.',
                'cache_time_note.required' => 'Thời gian lưu phải là bắt buộc.',
                'cache_time_note.integer' => 'Thời gian lưu phải là số nguyên.',
                'cache_time_note.min' => 'Thời gian lưu phải lớn hơn hoặc bằng 0.',
            ]);

            // Đếm số lượng ghi chú của người dùng cho doc_id này
            $noteCount = Note::where('document_id', $doc_id)
                ->where('user_id', $user->id)
                ->count();

            // Kiểm tra nếu số ghi chú đã quá 5
            if ($noteCount >= 5) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Bạn chỉ được tạo 5 ghi chú trong 1 video',
                    'data' => null,
                ], 403);
            }

            // Tạo ghi chú mới
            $note = new Note();
            $note->document_id = $doc_id;
            $note->user_id = $user->id;
            $note->title_note = $validatedData['title_note'];
            $note->content_note = $validatedData['content_note'];
            $note->cache_time_note = $validatedData['cache_time_note'];
            $note->del_flag = true;
            $note->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Ghi chú đã được tạo thành công',
                'data' => $note,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }


    public function updateNote(Request $request, $doc_id, $note_id)
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của doc_id
            if (!Document::find($doc_id)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tài liệu không tồn tại',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra tính hợp lệ của note_id
            $note = Note::where('id', $note_id)
                ->where('document_id', $doc_id)
                ->first();

            if (!$note) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Ghi chú không tồn tại',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra quyền sở hữu ghi chú
            if ($note->user_id !== $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Bạn không có quyền sửa ghi chú này',
                    'data' => null,
                ], 403);
            }

            // So sánh cache_time truyền vào với cache_time_note trong cơ sở dữ liệu

            // Validate dữ liệu đầu vào
            $validatedData = $request->validate([
                'title_note' => 'required|string|max:255',
                'content_note' => 'required|string',
            ], [
                'title_note.required' => 'Tiêu đề ghi chú là bắt buộc.',
                'title_note.string' => 'Tiêu đề ghi chú phải là chuỗi ký tự.',
                'title_note.max' => 'Tiêu đề ghi chú không được vượt quá 255 ký tự.',
                'content_note.required' => 'Nội dung ghi chú là bắt buộc.',
                'content_note.string' => 'Nội dung ghi chú phải là chuỗi ký tự.',
            ]);

            // Cập nhật dữ liệu nếu hợp lệ
            $note->title_note = $validatedData['title_note'];
            $note->content_note = $validatedData['content_note'];
            $note->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Ghi chú đã được cập nhật thành công',
                'data' => $note,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    // Xóa ghi chú của người dùng
    public function deleteNote($note_id)
    {
        try {
            // Lấy thông tin user hiện tại
            $user = auth('api')->user();

            // Kiểm tra tính hợp lệ của note_id
            $note = Note::where('id', $note_id)
                ->first();

            if (!$note) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Ghi chú không tồn tại',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra quyền sở hữu ghi chú
            if ($note->user_id !== $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Bạn không có quyền xóa ghi chú này',
                    'data' => null,
                ], 403);
            }
            // Xóa ghi chú
            $note->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ghi chú đã được xóa thành công',
                'data' => null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }
}
