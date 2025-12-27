<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            '*' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $answersData = $request->all();
        $savedAnswers = [];

        foreach ($answersData as $groupTitle => $questions) {
            $questionGroup = QuestionGroup::where('title', $groupTitle)->first();

            if (! $questionGroup) {
                continue;
            }

            foreach ($questions as $questionTitle => $answerValue) {
                $question = Question::where('question_group_id', $questionGroup->id)
                    ->where('title', $questionTitle)
                    ->first();

                if (! $question) {
                    continue;
                }

                // Validate that the answer is in the question's options
                if (! in_array($answerValue, $question->options)) {
                    return response()->json([
                        'error' => "Invalid answer '{$answerValue}' for question '{$questionTitle}'. Valid options are: ".implode(', ', $question->options),
                    ], 422);
                }

                $answer = Answer::create([
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
            }
        }

        return response()->json([
            'message' => 'Answers saved successfully',
            'answers' => $savedAnswers,
        ], 201);
    }

    public function index(): JsonResponse
    {
        $answers = Answer::with('question.questionGroup')
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedAnswers = $answers->groupBy(function ($answer) {
            return $answer->created_at->format('Y-m-d H:i:s');
        })->map(function ($group) {
            return $group->groupBy(function ($answer) {
                return $answer->question->questionGroup->title;
            })->map(function ($groupAnswers) {
                return $groupAnswers->mapWithKeys(function ($answer) {
                    return [$answer->question->title => $answer->answer];
                });
            });
        });

        return response()->json([
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
