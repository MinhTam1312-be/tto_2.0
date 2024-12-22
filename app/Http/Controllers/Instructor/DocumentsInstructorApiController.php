<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCodeResource;
use App\Http\Resources\Admin\AdminDocumentResource;
use App\Http\Resources\Admin\AdminQuestionResource;
use App\Models\Chapter;
use App\Models\Code;
use App\Models\Document;
use App\Models\Question;
use App\Services\LogActivityService;
use Illuminate\Http\Request;

class DocumentsInstructorApiController extends Controller
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
        try {
            $user = auth('api')->user();

            // Kiểm tra đăng nhập
            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa đăng nhập.'], 401);
            }

            // Tìm tài liệu theo ID
            $document = Document::select(
                'id',
                'serial_document',
                'name_document',
                'discription_document',
                'url_video',
                'type_document',
                'del_flag',
                'updated_at'
            )
                ->where('id', $id)
                ->where('del_flag', true)
                ->first();

            // Nếu tài liệu không tồn tại
            if (!$document) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Tài liệu không tồn tại hoặc đã bị xóa.',
                    'data' => null,
                ], 404);
            }

            // Lấy chi tiết của tài liệu
            $documentDetails = [
                'document_id' => $document->id,
                'serial_document' => $document->serial_document,
                'name_document' => $document->name_document,
                'discription_document' => $document->discription_document,
                'url_video' => $document->url_video,
                'type_document' => $document->type_document,
                'updated_at' => $document->updated_at,
            ];

            // Lấy dữ liệu bổ sung dựa trên type_document
            if ($document->type_document === 'quiz') {
                $quizs = Question::where('id', $document->id)->get();
                $documentDetails['quizs'] = $quizs->map(function ($quiz) {
                    return [
                        'id' => $quiz->id,
                        'content_question' => $quiz->content_question,
                        'answer_question' => $quiz->answer_question,
                        'type_question' => $quiz->type_question,
                        'updated_at' => $quiz->updated_at,
                    ];
                });
            } elseif ($document->type_document === 'code') {
                $codes = Code::where('id', $document->id)->get();
                $documentDetails['codes'] = $codes->map(function ($code) {
                    return [
                        'id' => $code->id,
                        'question_code' => $code->question_code,
                        'answer_code' => $code->answer_code,
                        'tutorial_code' => $code->tutorial_code,
                        'updated_at' => $code->updated_at,
                    ];
                });
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => $documentDetails,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
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
    public function update(Request $request, $id)
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

    // Gọi ra các bài học thuộc chương
    public function getDocumentsByChapter($chapter_id)
    {
        try {
            // Tìm chương dựa trên chapter_id
            $chapter = Chapter::find($chapter_id);

            // Nếu không tìm thấy chương
            if (!$chapter) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy chương với ID đã cho.',
                    'data' => null,
                ], 404);
            }

            // Lấy các tài liệu thuộc chương và sắp xếp theo serial_document tăng dần
            $documents = Document::where('chapter_id', $chapter->id)
                ->where('del_flag', true)
                ->orderBy('serial_document', 'asc')
                ->get();

            // Nếu không có tài liệu nào
            if ($documents->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có tài liệu nào thuộc chương này.',
                    'data' => null,
                ], 404);
            }

            // Xử lý chi tiết cho từng tài liệu
            $documentsData = $documents->map(function ($document) {
                $documentDetails = [
                    'document_id' => $document->id,
                    'serial_document' => $document->serial_document,
                    'name_document' => $document->name_document,
                    'discription_document' => $document->discription_document,
                    'url_video' => $document->url_video,
                    'del_flag' => $document->del_flag,
                    'type_document' => $document->type_document,
                    'updated_at' => $document->updated_at,
                ];

                // Lấy dữ liệu bổ sung dựa trên type_document
                if ($document->type_document === 'quiz') {
                    $quizs = Question::where('id', $document->id)->get();
                    $documentDetails['quizs'] = $quizs->map(function ($quiz) {
                        return [
                            'id' => $quiz->id,
                            'content_question' => $quiz->content_question,
                            'answer_question' => $quiz->answer_question,
                            'type_question' => $quiz->type_question,
                            'updated_at' => $quiz->updated_at,
                        ];
                    });
                } elseif ($document->type_document === 'code') {
                    $codes = Code::where('id', $document->id)->get();
                    $documentDetails['codes'] = $codes->map(function ($code) {
                        return [
                            'id' => $code->id,
                            'question_code' => $code->question_code,
                            'answer_code' => $code->answer_code,
                            'tutorial_code' => $code->tutorial_code,
                            'updated_at' => $code->updated_at,
                        ];
                    });
                }

                return $documentDetails;
            });

            // Trả về dữ liệu JSON
            return response()->json([
                'status' => 'success',
                'chapter_id' => $chapter->id,
                'name_chapter' => $chapter->name_chapter,
                'data' => $documentsData,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Gọi các bài học thuộc chương, thuộc khóa học
    public function getDocumentsByCourseChapter($course_id, $chapter_id)
    {
        try {
            // Tìm chương dựa trên chapter_id
            $chapter = Chapter::find($chapter_id);

            // Nếu không tìm thấy chương
            if (!$chapter) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy chương với ID đã cho.',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra xem chapter_id có thuộc course_id không
            $isChapterBelongsToCourse = Chapter::where('course_id', $course_id)
                ->where('id', $chapter->id)
                ->exists();

            if (!$isChapterBelongsToCourse) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Chương không thuộc về khóa học này.',
                    'data' => null,
                ], 404);
            }

            // Lấy các tài liệu thuộc chương và sắp xếp theo serial_document tăng dần
            $documents = Document::where('chapter_id', $chapter->id)
                ->where('del_flag', true)
                ->orderBy('serial_document', 'asc')
                ->get();

            // Nếu không có tài liệu nào
            if ($documents->isEmpty()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không có tài liệu nào thuộc chương này.',
                    'data' => null,
                ], 404);
            }

            // Xử lý chi tiết cho từng tài liệu
            $documentsData = $documents->map(function ($document) {
                $documentDetails = [
                    'document_id' => $document->id,
                    'serial_document' => $document->serial_document,
                    'name_document' => $document->name_document,
                    'discription_document' => $document->discription_document,
                    'url_video' => $document->url_video,
                    'del_flag' => $document->del_flag,
                    'type_document' => $document->type_document,
                    'updated_at' => $document->updated_at,
                ];

                // Lấy dữ liệu bổ sung dựa trên type_document
                if ($document->type_document === 'quiz') {
                    $quizs = Question::where('id', $document->id)->get();
                    $documentDetails['quizs'] = $quizs->map(function ($quiz) {
                        return [
                            'id' => $quiz->id,
                            'content_question' => $quiz->content_question,
                            'answer_question' => $quiz->answer_question,
                            'type_question' => $quiz->type_question,
                            'updated_at' => $quiz->updated_at,
                        ];
                    });
                } elseif ($document->type_document === 'code') {
                    $codes = Code::where('id', $document->id)->get();
                    $documentDetails['codes'] = $codes->map(function ($code) {
                        return [
                            'id' => $code->id,
                            'question_code' => $code->question_code,
                            'answer_code' => $code->answer_code,
                            'tutorial_code' => $code->tutorial_code,
                            'updated_at' => $code->updated_at,
                        ];
                    });
                }

                return $documentDetails;
            });

            // Trả về dữ liệu JSON
            return response()->json([
                'status' => 'success',
                'course_id' => $course_id,
                'chapter_id' => $chapter->id,
                'name_chapter' => $chapter->name_chapter,
                'data' => $documentsData,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Thêm bài học dạng video
    public function storeVideoDocument(Request $request)
    {
        try {
            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'name_document' => 'required|string|max:255',
                'discription_document' => 'required|string',
                'serial_document' => 'required|min:1|max:100',
                'url_video' => 'required|url',
                'chapter_id' => 'required|exists:chapters,id'
            ], [
                'name_document.required' => 'Tên tài liệu là bắt buộc.',
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.required' => 'Mô tả tài liệu là bắt buộc.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'discription_document.max' => 'Mô tả tài liệu không được vượt quá 255 ký tự.',
                'serial_document.required' => 'Số thứ tự bài học là bắt buộc.',
                'serial_document.min' => 'Số thứ tự bài học nhỏ nhất là 1.',
                'serial_document.max' => 'Số thứ tự bài học không vượt quá 100.',
                'url_video.required' => 'Đường dẫn video là bắt buộc.',
                'url_video.url' => 'Đường dẫn video không hợp lệ.',
                'chapter_id.required' => 'ID chương là bắt buộc.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
            ]);

            // Tạo tài liệu mới
            $document = Document::create([
                'name_document' => $request->name_document,
                'discription_document' => $request->discription_document,
                'serial_document' => $request->serial_document,
                'url_video' => $request->url_video,
                'type_document' => 'video',
                'chapter_id' => $request->chapter_id,
                'del_flag' => true,
            ]);

            // Lấy thông tin tên chương và tên khóa học
            $chapter = Chapter::with('course')->find($document->chapter_id);
            $courseName = $chapter->course->name_course;
            $chapterName = $chapter->name_chapter;

            // Ghi log khi tài liệu được tạo thành công
            LogActivityService::log(
                'thao_tac_them_tai_lieu_video',
                "Đã thêm tài liệu video '{$document->name_document}' cho chương '{$chapterName}' của khóa học '{$courseName}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu đã được thêm thành công.',
                'document' => [
                    'name_document' => $document->name_document,
                    'chapter_name' => $chapterName,
                    'course_name' => $courseName,
                ]
            ], 201);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'thao_tac_them_tai_lieu_video',
                'Thêm tài liệu video thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thêm bài học dạng quiz
    public function storeQuizDocument(Request $request)
    {
        try {
            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'type_question' => 'in:multiple_choice,fill,true_false',
                'chapter_id' => 'required|exists:chapters,id',
                'name_document' => 'required|string|max:255',
                'discription_document' => 'required|string',
                'serial_document' => 'required|integer|min:1|max:100',
                'content_question' => 'required|string',
                'answer_question' => 'required|string',
            ], [
                'name_document.required' => 'Tên tài liệu là bắt buộc.',
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.required' => 'Mô tả tài liệu là bắt buộc.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'serial_document.required' => 'Số thứ tự bài học là bắt buộc.',
                'serial_document.integer' => 'Số thứ tự bài học phải là số nguyên.',
                'serial_document.min' => 'Số thứ tự bài học nhỏ nhất là 1.',
                'serial_document.max' => 'Số thứ tự bài học không vượt quá 100.',
                'chapter_id.required' => 'ID chương là bắt buộc.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
                'content_question.required' => 'Nội dung câu hỏi là bắt buộc.',
                'answer_question.required' => 'Nội dung câu trả lời là bắt buộc.',
            ]);

            // Tạo tài liệu quiz mới
            $document = Document::create([
                'name_document' => $validatedData['name_document'],
                'discription_document' => $validatedData['discription_document'],
                'serial_document' => $validatedData['serial_document'],
                'url_video' => null,
                'type_document' => 'quiz',
                'chapter_id' => $validatedData['chapter_id'],
                'del_flag' => true,
            ]);

            // Lấy thông tin tên chương và tên khóa học
            $chapter = Chapter::with('course')->findOrFail($document->chapter_id);
            $courseName = $chapter->course->name_course;
            $chapterName = $chapter->name_chapter;

            // Tạo câu hỏi quiz
            $question = Question::create([
                'content_question' => $validatedData['content_question'],
                'answer_question' => $validatedData['answer_question'],
                'type_question' => $validatedData['type_question'],
                'del_flag' => true,
                'id' => $document->id
            ]);

            // Ghi log khi tài liệu quiz được tạo thành công
            LogActivityService::log(
                'thao_tac_them_tai_lieu_quiz',
                "Đã thêm tài liệu quiz '{$document->name_document}' cho chương '{$chapterName}' của khóa học '{$courseName}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu đã được thêm thành công.',
                'document' => [
                    'name_document' => $document->name_document,
                    'chapter_name' => $chapterName,
                    'course_name' => $courseName,
                ],
                'question' => new AdminQuestionResource($question),
            ], 201);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'thao_tac_them_tai_lieu_quiz',
                'Thêm tài liệu quiz thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thêm bài học dạng code
    public function storeCodeDocument(Request $request)
    {
        try {
            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'chapter_id' => 'required|exists:chapters,id',
                'name_document' => 'required|string|max:255',
                'discription_document' => 'required|string',
                'serial_document' => 'required|min:1|max:100',
                'question_code' => 'required',
                'answer_code' => 'required',
                'tutorial_code' => 'required',
            ], [
                'name_document.required' => 'Tên tài liệu là bắt buộc.',
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.required' => 'Mô tả tài liệu là bắt buộc.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'discription_document.max' => 'Mô tả tài liệu không được vượt quá 255 ký tự.',
                'serial_document.required' => 'Số thứ tự bài học là bắt buộc.',
                'serial_document.min' => 'Số thứ tự bài học nhỏ nhất là 1.',
                'serial_document.max' => 'Số thứ tự bài học không vượt quá 100.',
                'chapter_id.required' => 'ID chương là bắt buộc.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
                'question_code.required' => 'Nội dung câu hỏi code là bắt buộc.',
                'answer_code.required' => 'Nội dung câu trả lời code là bắt buộc.',
                'tutorial_code.required' => 'Hướng dẫn code là bắt buộc.',
            ]);

            // Tạo tài liệu code mới
            $document = Document::create([
                'name_document' => $request->name_document,
                'discription_document' => $request->discription_document,
                'serial_document' => $request->serial_document,
                'url_video' => null,
                'type_document' => 'code',
                'chapter_id' => $request->chapter_id,
                'del_flag' => true,
            ]);

            // Lấy thông tin tên chương và tên khóa học
            $chapter = Chapter::with('course')->find($document->chapter_id);
            $courseName = $chapter->course->name_course;
            $chapterName = $chapter->name_chapter;

            // Tạo câu hỏi code
            $newDocument = new AdminDocumentResource($document);
            $newCode = Code::create([
                'question_code' => $request->question_code,
                'answer_code' => $request->answer_code,
                'tutorial_code' => $request->tutorial_code,
                'del_flag' => true,
                'id' => $newDocument->id
            ]);

            // Ghi log khi tài liệu code được tạo thành công
            LogActivityService::log(
                'thao_tac_them_tai_lieu_code',
                "Đã thêm tài liệu code '{$document->name_document}' cho chương '{$chapterName}' của khóa học '{$courseName}'.",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu đã được thêm thành công.',
                'document' => [
                    'name_document' => $document->name_document,
                    'chapter_name' => $chapterName,
                    'course_name' => $courseName,
                ],
                'code' => new AdminCodeResource($newCode),
            ], 201);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'thao_tac_them_tai_lieu_code',
                'Thêm tài liệu code thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Sửa bài học dạng video
    public function updateVideoDocument(Request $request, $id)
    {
        try {
            // Kiểm tra tài liệu tồn tại
            $document = Document::find($id);
            if (!$document) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy tài liệu với ID này.',
                    'data' => null,
                ], 404);
            }

            // Lưu lại dữ liệu cũ trước khi cập nhật
            $oldDocumentData = $document->toArray();

            // Xác thực dữ liệu đầu vào
            $validatedData = $request->validate([
                'name_document' => 'nullable|string|max:255',
                'discription_document' => 'nullable|string|max:255',
                'serial_document' => 'nullable|integer|max:100',
                'type_document' => 'in:video,quiz,code',
                'url_video' => 'nullable|url',
                'chapter_id' => 'nullable|exists:chapters,id'
            ], [
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'discription_document.max' => 'Mô tả tài liệu không được vượt quá 255 ký tự.',
                'serial_document.integer' => 'Thứ tự bài học phải là số nguyên.',
                'serial_document.max' => 'Thứ tự bài học không vượt quá 100.',
                'type_document.in' => 'Dạng bài học chỉ có 3 dạng: video, quiz, code',
                'url_video.url' => 'Đường dẫn video không hợp lệ.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
            ]);

            // Cập nhật dữ liệu chỉ khi có thay đổi
            $document->update(array_filter($validatedData));

            // Lấy thông tin chương và khóa học liên quan đến tài liệu
            $chapter = Chapter::with('course')->find($document->chapter_id);
            $courseName = $chapter ? $chapter->course->name_course : 'Không xác định';
            $chapterName = $chapter ? $chapter->name_chapter : 'Không xác định';

            // Kiểm tra và log những trường bị thay đổi
            $changes = [];
            foreach ($validatedData as $key => $newValue) {
                if (array_key_exists($key, $oldDocumentData) && $oldDocumentData[$key] != $newValue) {
                    $changes[] = [
                        'field' => $key,
                        'old_value' => $oldDocumentData[$key],
                        'new_value' => $newValue
                    ];
                }
            }

            // Nếu có thay đổi, log các trường bị thay đổi
            if (!empty($changes)) {
                foreach ($changes as $change) {
                    LogActivityService::log(
                        'thao_tac_cap_nhat_tai_lieu_video',
                        "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho tài liệu video '{$document->name_document}' thuộc chương '{$chapterName}' của khóa học '{$courseName}'.",
                        'success'
                    );
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu đã được cập nhật thành công.',
                'document' => new AdminDocumentResource($document),
            ], 200);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi xảy ra
            LogActivityService::log(
                'thao_tac_cap_nhat_tai_lieu_video',
                'Cập nhật tài liệu video thất bại: ' . $e->getMessage(),
                'fail'
            );

            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Sửa bài học dạng quiz
    public function updateQuizDocument(Request $request, $id)
    {
        try {
            // Kiểm tra tài liệu tồn tại
            $document = Document::find($id);
            if (!$document) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy tài liệu với ID này.',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra câu hỏi liên quan tới tài liệu
            $question = Question::where('id', $document->id)->first();
            if (!$question) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy câu hỏi liên quan tới tài liệu này.',
                    'data' => null,
                ], 404);
            }

            // Lưu lại dữ liệu cũ trước khi cập nhật
            $oldDocumentData = $document->toArray();
            $oldQuestionData = $question->toArray();

            // Xác thực dữ liệu
            $validatedData = $request->validate([
                'name_document' => 'nullable|string|max:255',
                'discription_document' => 'nullable|string|max:255',
                'serial_document' => 'nullable|integer|max:100',
                'type_document' => 'in:video,quiz,code',
                'chapter_id' => 'nullable|exists:chapters,id',
                'type_question' => 'nullable|in:multiple_choice,fill,true_false',
                'content_question' => 'nullable|string|max:255',
                'answer_question' => 'nullable',
            ], [
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'discription_document.max' => 'Mô tả tài liệu không được vượt quá 255 ký tự.',
                'serial_document.integer' => 'Thứ tự bài học phải là số nguyên.',
                'serial_document.max' => 'Thứ tự bài học không vượt quá 100.',
                'type_document.in' => 'Dạng bài học chỉ có 3 dạng: video, quiz, code.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
                'type_question.in' => 'Loại câu hỏi chỉ chấp nhận: multiple_choice, fill, true_false.',
                'content_question.string' => 'Nội dung câu hỏi phải là một chuỗi ký tự.',
                'content_question.max' => 'Nội dung câu hỏi không được vượt quá 255 ký tự.',
            ]);
            // Cập nhật tài liệu
            $document->update(array_filter([
                'name_document' => $validatedData['name_document'] ?? $document->name_document,
                'discription_document' => $validatedData['discription_document'] ?? $document->discription_document,
                'serial_document' => $validatedData['serial_document'] ?? $document->serial_document,
                'type_document' => $validatedData['type_document'] ?? $document->type_document,
                'chapter_id' => $validatedData['chapter_id'] ?? $document->chapter_id,
            ]));

            // Cập nhật câu hỏi
            $question->update(array_filter([
                'type_question' => $validatedData['type_question'] ?? null,
                'content_question' => $validatedData['content_question'] ?? null,
                'answer_question' => $validatedData['answer_question'] ?? null,
            ]));

            // Kiểm tra và log những trường bị thay đổi trong tài liệu
            $documentChanges = [];
            foreach ($validatedData as $key => $newValue) {
                if (array_key_exists($key, $oldDocumentData) && $oldDocumentData[$key] != $newValue) {
                    $documentChanges[] = [
                        'field' => $key,
                        'old_value' => $oldDocumentData[$key],
                        'new_value' => $newValue
                    ];
                }
            }

            // Kiểm tra và log những trường bị thay đổi trong câu hỏi
            $questionChanges = [];
            foreach (['type_question', 'content_question', 'answer_question'] as $key) {
                if (array_key_exists($key, $validatedData) && $oldQuestionData[$key] != $validatedData[$key]) {
                    $questionChanges[] = [
                        'field' => $key,
                        'old_value' => $oldQuestionData[$key],
                        'new_value' => $validatedData[$key]
                    ];
                }
            }

            // Ghi log các thay đổi tài liệu
            foreach ($documentChanges as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_tai_lieu_quiz',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho tài liệu quiz '{$document->name_document}'.",
                    'success'
                );
            }

            // Ghi log các thay đổi câu hỏi
            foreach ($questionChanges as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_cau_hoi_quiz',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho câu hỏi liên quan tới tài liệu '{$document->name_document}'.",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu và câu hỏi đã được cập nhật thành công.',
                'document' => new AdminDocumentResource($document),
                'question' => new AdminQuestionResource($question),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Sửa bài học dạng code
    public function updateCodeDocument(Request $request, $id)
    {
        try {
            // Kiểm tra tài liệu tồn tại
            $document = Document::find($id);
            if (!$document) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy tài liệu với ID này.',
                    'data' => null,
                ], 404);
            }

            // Kiểm tra bài học code liên quan tới tài liệu
            $code = Code::where('id', $document->id)->first();
            if (!$code) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Không tìm thấy bài học code liên quan tới tài liệu này.',
                    'data' => null,
                ], 404);
            }

            // Lưu lại dữ liệu cũ trước khi cập nhật
            $oldDocumentData = $document->toArray();
            $oldCodeData = $code->toArray();

            // Xác thực dữ liệu
            $validatedData = $request->validate([
                'name_document' => 'nullable|string|max:255',
                'discription_document' => 'nullable',
                'serial_document' => 'nullable|integer|max:100',
                'type_document' => 'in:video,quiz,code',
                'chapter_id' => 'nullable|exists:chapters,id',
                'question_code' => 'nullable',
                'answer_code' => 'nullable',
                'tutorial_code' => 'nullable',
            ], [
                'name_document.string' => 'Tên tài liệu phải là một chuỗi ký tự.',
                'name_document.max' => 'Tên tài liệu không được vượt quá 255 ký tự.',
                'discription_document.string' => 'Mô tả tài liệu phải là một chuỗi ký tự.',
                'discription_document.max' => 'Mô tả tài liệu không được vượt quá 255 ký tự.',
                'serial_document.integer' => 'Thứ tự bài học phải là số nguyên.',
                'serial_document.max' => 'Thứ tự bài học không vượt quá 100.',
                'type_document.in' => 'Dạng bài học chỉ có 3 dạng: video, quiz, code.',
                'chapter_id.exists' => 'ID chương không tồn tại trong cơ sở dữ liệu.',
                'question_code.string' => 'Câu hỏi code phải là một chuỗi ký tự.',
                'answer_code.string' => 'Đáp án code phải là một chuỗi ký tự.',
                'tutorial_code.string' => 'Hướng dẫn code phải là một chuỗi ký tự.',
            ]);

            // Cập nhật tài liệu
            $document->update(array_filter($validatedData));

            // Cập nhật bài học code
            $code->update(array_filter([
                'question_code' => $validatedData['question_code'] ?? null,
                'answer_code' => $validatedData['answer_code'] ?? null,
                'tutorial_code' => $validatedData['tutorial_code'] ?? null,
            ]));

            // Kiểm tra và log những trường bị thay đổi trong tài liệu
            $documentChanges = [];
            foreach ($validatedData as $key => $newValue) {
                if (array_key_exists($key, $oldDocumentData) && $oldDocumentData[$key] != $newValue) {
                    $documentChanges[] = [
                        'field' => $key,
                        'old_value' => $oldDocumentData[$key],
                        'new_value' => $newValue
                    ];
                }
            }

            // Kiểm tra và log những trường bị thay đổi trong bài học code
            $codeChanges = [];
            foreach (['question_code', 'answer_code', 'tutorial_code'] as $key) {
                if (array_key_exists($key, $validatedData) && $oldCodeData[$key] != $validatedData[$key]) {
                    $codeChanges[] = [
                        'field' => $key,
                        'old_value' => $oldCodeData[$key],
                        'new_value' => $validatedData[$key]
                    ];
                }
            }

            // Ghi log các thay đổi tài liệu
            foreach ($documentChanges as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_tai_lieu_code',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho tài liệu code '{$document->name_document}'.",
                    'success'
                );
            }

            // Ghi log các thay đổi bài học code
            foreach ($codeChanges as $change) {
                LogActivityService::log(
                    'thao_tac_cap_nhat_bai_hoc_code',
                    "Đã thay đổi trường '{$change['field']}' từ '{$change['old_value']}' thành '{$change['new_value']}' cho bài học code liên quan tới tài liệu '{$document->name_document}'.",
                    'success'
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tài liệu và bài học code đã được cập nhật thành công.',
                'document' => new AdminDocumentResource($document),
                'code' => new AdminCodeResource($code),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xảy ra: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Chức năng ẩn, hiện bài học
    public function statusDocument($doc_id)
    {
        try {
            // Tìm tài liệu theo ID, nếu không có sẽ throw ModelNotFoundException
            $document = Document::findOrFail($doc_id);

            // Cập nhật trạng thái del_flag (ẩn hoặc hiện tài liệu)
            $document->update(['del_flag' => !$document->del_flag]);

            // Ghi log trạng thái thay đổi thành công
            LogActivityService::log(
                'thay_doi_trang_thai_tai_lieu',
                "Đã thay đổi trạng thái tài liệu '{$document->name_document}' trong chương '{$document->chapter->name_chapter}' của khóa học '{$document->chapter->course->name_course}' thành " . ($document->del_flag ? 'hiện' : 'ẩn') . ".",
                'success'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thay đổi trạng thái tài liệu thành công.',
                'data' => new AdminDocumentResource($document),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Ghi log khi có lỗi
            LogActivityService::log(
                'thay_doi_trang_thai_tai_lieu',
                'Thay đổi trạng thái tài liệu thất bại: ' . $e->getMessage(),
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
