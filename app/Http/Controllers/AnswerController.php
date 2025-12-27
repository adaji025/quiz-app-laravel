<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AnswerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                '*' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $answersData = $request->all();
            $savedAnswers = [];
            
            // Generate a unique submission ID for this entire submission
            $submissionId = (string) Str::uuid();

            foreach ($answersData as $groupTitle => $questions) {
                $questionGroup = QuestionGroup::where('title', $groupTitle)->first();

                if (! $questionGroup) {
                    return response()->json([
                        'error' => "Question group '{$groupTitle}' not found",
                    ], 422);
                }

                foreach ($questions as $questionTitle => $answerValue) {
                    $question = Question::where('question_group_id', $questionGroup->id)
                        ->where('title', $questionTitle)
                        ->first();

                    if (! $question) {
                        return response()->json([
                            'error' => "Question '{$questionTitle}' not found in group '{$groupTitle}'",
                        ], 422);
                    }

                    // Validate that the answer is in the question's options
                    if (! in_array($answerValue, $question->options)) {
                        return response()->json([
                            'error' => "Invalid answer '{$answerValue}' for question '{$questionTitle}'. Valid options are: ".implode(', ', $question->options),
                        ], 422);
                    }

                    try {
                        $answer = Answer::create([
                            'submission_id' => $submissionId,
                            'question_id' => $question->id,
                            'answer' => $answerValue,
                        ]);

                        $savedAnswers[] = [
                            'id' => $answer->id,
                            'question_id' => $question->id,
                            'question_title' => $questionTitle,
                            'answer' => $answerValue,
                            'created_at' => $answer->created_at,
                        ];
                    } catch (\Exception $e) {
                        return response()->json([
                            'error' => 'Failed to save answer',
                            'message' => $e->getMessage(),
                        ], 500);
                    }
                }
            }

            if (empty($savedAnswers)) {
                return response()->json([
                    'error' => 'No valid answers were submitted',
                ], 422);
            }

            // Get the first answer's timestamp for submitted_at
            $firstAnswer = Answer::where('submission_id', $submissionId)->first();
            
            // Format the response with full answer details including question_group
            $formattedAnswers = Answer::with('question.questionGroup')
                ->where('submission_id', $submissionId)
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'question_id' => $answer->question_id,
                        'question_title' => $answer->question->title,
                        'question_group' => $answer->question->questionGroup->title,
                        'answer' => $answer->answer,
                        'created_at' => $answer->created_at,
                        'updated_at' => $answer->updated_at,
                    ];
                });

            return response()->json([
                'id' => $submissionId,
                'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                'answers' => $formattedAnswers,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request',
                'message' => config('app.debug') ? $e->getMessage() : 'Please check your request and try again',
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        $answers = Answer::with('question.questionGroup')
            ->whereNotNull('submission_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $submissions = $answers->groupBy('submission_id')->map(function ($group, $submissionId) {
            $firstAnswer = $group->first();
            return [
                'id' => $submissionId,
                'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                'answers' => $group->groupBy(function ($answer) {
                    return $answer->question->questionGroup->title;
                })->map(function ($groupAnswers) {
                    return $groupAnswers->mapWithKeys(function ($answer) {
                        return [$answer->question->title => $answer->answer];
                    });
                }),
            ];
        })->values();

        return response()->json($submissions);
    }

    public function show(string $id): JsonResponse
    {
        // Check if it's a UUID (submission_id) or numeric ID
        $isUuid = Str::isUuid($id);
        $isNumeric = is_numeric($id);
        
        // Try to find by submission_id if it's a UUID
        if ($isUuid) {
            $answers = Answer::with('question.questionGroup')
                ->where('submission_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($answers->isEmpty()) {
                return response()->json([
                    'error' => 'Submission not found',
                    'message' => "No submission found with submission_id: {$id}",
                    'searched_id' => $id,
                    'id_type' => 'submission_id (UUID)',
                    'hint' => 'Make sure the submission_id exists. You can get all submissions from GET /api/answers',
                ], 404);
            }
            
            // Return all answers as an array
            $firstAnswer = $answers->first();
            $answersArray = $answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'question_title' => $answer->question->title,
                    'question_group' => $answer->question->questionGroup->title,
                    'answer' => $answer->answer,
                    'created_at' => $answer->created_at,
                    'updated_at' => $answer->updated_at,
                ];
            })->values();
            
            return response()->json([
                'id' => $id,
                'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                'answers' => $answersArray,
            ]);
        }
        
        // Try to find by numeric ID (answer record ID)
        if ($isNumeric) {
            $answer = Answer::with('question.questionGroup')->find((int) $id);
            
            if (! $answer) {
                return response()->json([
                    'error' => 'Answer not found',
                    'message' => "No answer record found with ID: {$id}",
                    'searched_id' => $id,
                    'id_type' => 'answer_id (integer)',
                    'hint' => 'Make sure the answer ID exists. Answer IDs are auto-incrementing integers.',
                ], 404);
            }
            
            return response()->json([
                'id' => $answer->id,
                'question_id' => $answer->question_id,
                'question_title' => $answer->question->title,
                'question_group' => $answer->question->questionGroup->title,
                'answer' => $answer->answer,
                'created_at' => $answer->created_at,
                'updated_at' => $answer->updated_at,
            ]);
        }
        
        // Invalid ID format
        return response()->json([
            'error' => 'Invalid ID format',
            'message' => "The provided ID '{$id}' is not a valid UUID or numeric ID",
            'searched_id' => $id,
            'expected_formats' => [
                'UUID format' => 'e.g., 93d896a5-dec0-41f1-b068-c6edbed3b186 (for submission_id)',
                'Numeric ID' => 'e.g., 1, 2, 3 (for individual answer record ID)',
            ],
        ], 422);
    }
}
