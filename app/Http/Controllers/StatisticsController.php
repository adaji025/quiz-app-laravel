<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $answers = Answer::with(['question.questionGroup'])
            ->whereNotNull('submission_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by submission_id and calculate statistics for each submission
        $submissionStats = $answers->groupBy('submission_id')->map(function ($group, $submissionId) {
            $firstAnswer = $group->first();
            
            // Calculate statistics for this submission
            $questionStats = [];
            $questions = Question::with('questionGroup')->get();
            
            foreach ($questions as $question) {
                $submissionAnswers = $group->where('question_id', $question->id);
                $totalAnswers = $submissionAnswers->count();
                
                if ($totalAnswers > 0) {
                    $answerCounts = $submissionAnswers->groupBy('answer')->map->count();
                    
                    $optionStats = [];
                    foreach ($question->options as $option) {
                        $count = $answerCounts->get($option, 0);
                        $percentage = $totalAnswers > 0 ? round(($count / $totalAnswers) * 100, 2) : 0;
                        
                        $optionStats[] = [
                            'option' => $option,
                            'count' => $count,
                            'percentage' => $percentage,
                        ];
                    }
                    
                    $questionStats[] = [
                        'question_id' => $question->id,
                        'question_title' => $question->title,
                        'question_group' => $question->questionGroup->title,
                        'total_answers' => $totalAnswers,
                        'options' => $optionStats,
                    ];
                }
            }
            
            // Group answers by category for this submission
            $groupedAnswers = $group->groupBy(function ($answer) {
                return $answer->question->questionGroup->title;
            })->map(function ($categoryAnswers) {
                return $categoryAnswers->mapWithKeys(function ($answer) {
                    return [$answer->question->title => $answer->answer];
                });
            });
            
            return [
                'id' => $submissionId,
                'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                'statistics' => $questionStats,
                'answers' => $groupedAnswers,
            ];
        })->values();

        return response()->json($submissionStats);
    }

    public function show(string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return response()->json([
                'error' => 'Invalid submission ID format',
                'message' => "The provided ID '{$id}' is not a valid UUID",
                'searched_id' => $id,
                'expected_format' => 'UUID format: e.g., 93d896a5-dec0-41f1-b068-c6edbed3b186',
            ], 422);
        }

        $answers = Answer::with(['question.questionGroup'])
            ->where('submission_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($answers->isEmpty()) {
            return response()->json([
                'error' => 'Submission not found',
                'message' => "No submission found with ID: {$id}",
                'searched_id' => $id,
            ], 404);
        }

        $firstAnswer = $answers->first();
        
        // Calculate statistics for this specific submission
        $questionStats = [];
        $questions = Question::with('questionGroup')->get();
        
        foreach ($questions as $question) {
            $submissionAnswers = $answers->where('question_id', $question->id);
            $totalAnswers = $submissionAnswers->count();
            
            if ($totalAnswers > 0) {
                $answerCounts = $submissionAnswers->groupBy('answer')->map->count();
                
                $optionStats = [];
                foreach ($question->options as $option) {
                    $count = $answerCounts->get($option, 0);
                    $percentage = $totalAnswers > 0 ? round(($count / $totalAnswers) * 100, 2) : 0;
                    
                    $optionStats[] = [
                        'option' => $option,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
                }
                
                $questionStats[] = [
                    'question_id' => $question->id,
                    'question_title' => $question->title,
                    'question_group' => $question->questionGroup->title,
                    'total_answers' => $totalAnswers,
                    'options' => $optionStats,
                ];
            }
        }
        
        // Group answers by category
        $groupedAnswers = $answers->groupBy(function ($answer) {
            return $answer->question->questionGroup->title;
        })->map(function ($categoryAnswers) {
            return $categoryAnswers->mapWithKeys(function ($answer) {
                return [$answer->question->title => $answer->answer];
            });
        });

        return response()->json([
            'id' => $id,
            'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
            'statistics' => $questionStats,
            'answers' => $groupedAnswers,
        ]);
    }
}
