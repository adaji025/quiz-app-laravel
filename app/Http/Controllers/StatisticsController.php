<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use App\Services\StatisticsScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $scoringService = new StatisticsScoringService();
        
        $answers = Answer::with(['question.questionGroup'])
            ->whereNotNull('submission_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all question groups with their questions
        $questionGroups = QuestionGroup::with('questions')->get();

        // Group by submission_id and calculate category scores for each submission
        $submissionStats = $answers->groupBy('submission_id')->map(function ($group, $submissionId) use ($scoringService, $questionGroups) {
            $firstAnswer = $group->first();
            
            // Calculate category percentages
            $categories = [];
            
            foreach ($questionGroups as $questionGroup) {
                $categoryAnswers = $group->filter(function ($answer) use ($questionGroup) {
                    return $answer->question->question_group_id === $questionGroup->id;
                });
                
                if ($categoryAnswers->isNotEmpty()) {
                    $categoryScore = $scoringService->calculateCategoryScore(
                        $categoryAnswers,
                        $questionGroup->questions
                    );
                    
                    $categories[$questionGroup->title] = $categoryScore['percentage'];
                }
            }
            
            return [
                'id' => $submissionId,
                'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                'categories' => $categories,
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

        $scoringService = new StatisticsScoringService();

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
        
        // Get all question groups with their questions
        $questionGroups = QuestionGroup::with('questions')->get();
        
        // Calculate category percentages
        $categories = [];
        
        foreach ($questionGroups as $questionGroup) {
            $categoryAnswers = $answers->filter(function ($answer) use ($questionGroup) {
                return $answer->question->question_group_id === $questionGroup->id;
            });
            
            if ($categoryAnswers->isNotEmpty()) {
                $categoryScore = $scoringService->calculateCategoryScore(
                    $categoryAnswers,
                    $questionGroup->questions
                );
                
                $categories[$questionGroup->title] = $categoryScore['percentage'];
            }
        }

        return response()->json([
            'id' => $id,
            'submitted_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
            'categories' => $categories,
        ]);
    }
}
