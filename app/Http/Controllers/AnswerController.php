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

            return response()->json([
                'message' => 'Answers saved successfully',
                'submission_id' => $submissionId,
                'answers' => $savedAnswers,
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

        $groupedAnswers = $answers->groupBy('submission_id')->map(function ($group) {
            $firstAnswer = $group->first();
            return [
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

        return response()->json([
            'id' => (string) Str::uuid(),
            'answers' => $groupedAnswers,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $answer = Answer::with('question.questionGroup')->find($id);

        if (! $answer) {
            return response()->json([
                'error' => 'Answer not found',
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
}
