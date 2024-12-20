<?php

namespace App\Http\Controllers\API;

use App\Events\MyEvent;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Events\NotificationUserRegisterCourse;

class QuestionApiController extends Controller
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
    public function checkAnswer(Request $request, $id)
    {
        try {
            // Validate the request input
            $request->validate([
                'user_answer' => 'required',
            ]);

            // Find the question by ID, throw error if not found
            $question = Question::find($id);
            $userAnswer = $request->input('user_answer');
            $isCorrect = false;


            // Check answer based on question type
            switch ($question->type_question) {
                case 'multiple_choice':
                    // Convert the answer_question field into an array (assuming it's stored as a comma-separated string in DB)
                    $correctAnswers = explode('|', $question->answer_question);
                    // Check if user's answers match the correct answers
                    sort($userAnswer);
                    sort($correctAnswers);

                    // Compare both arrays, and return true if they are the same
                    $isCorrect = $userAnswer === $correctAnswers;
                    break;

                case 'fill':
                    $correctAnswersArray = explode('|', $question->answer_question);

                    // Sắp xếp mảng để không phụ thuộc thứ tự
                    sort($userAnswer);
                    sort($correctAnswersArray);
                    // Chuyển mảng đã sắp xếp thành chuỗi
                    $arrayQuestion = array_map('trim', $correctAnswersArray);

                    $userAnswerString = implode('|', $userAnswer);
                    $correctAnswerString = implode('|', $arrayQuestion);
                    

                    
                    // So sánh không phân biệt chữ hoa/chữ thường, và bỏ khoảng trắng thừa
                    $isCorrect = strtolower(trim($userAnswerString)) === strtolower(trim($correctAnswerString));
                    break;

                case 'true_false':

                    $correctAnswersTrueFalse = explode('/', $question->answer_question);
                    // $correctAnswersTrueFalse = explode(',', $question->answer_question);
                    // Check if user's answers match the correct answers
                    sort($userAnswer);
                    sort($correctAnswersTrueFalse);

                    $isCorrect = $userAnswer === $correctAnswersTrueFalse;
                    break;

                default:
                    // If question type is invalid
                    return response()->json([
                        'error' => 'Invalid question type'
                    ], 400);
            }

            // Return a success response with the result
            return response()->json([
                'question_id' => $id,
                'type_question' => $question->type_question,
                'is_correct' => $isCorrect,
            ]);
        } catch (ValidationException $e) {
            // If validation fails
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function notification()
    {
        $data = [
            'title' => 'Thông báo từ backend',
            'content' => 'Dữ liệu này được gửi qua Pusher!'
        ];

        event(new MyEvent($data));
        return [];
    }
}
