<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Http\JsonResponse;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        // Aggregate Statistics
        $questions = Question::with(['answers', 'questionGroup'])->get();
        $aggregateStats = [];

        foreach ($questions as $question) {
            $totalAnswers = $question->answers->count();
            $answerCounts = $question->answers->groupBy('answer')->map->count();

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

            $aggregateStats[] = [
                'question_id' => $question->id,
                'question_title' => $question->title,
                'question_group' => $question->questionGroup->title,
                'total_answers' => $totalAnswers,
                'options' => $optionStats,
            ];
        }

        // Individual Summaries
        $answers = Answer::with('question.questionGroup')
            ->orderBy('created_at', 'desc')
            ->get();

        $individualSummaries = $answers->groupBy(function ($answer) {
            return $answer->created_at->format('Y-m-d H:i:s');
        })->map(function ($group, $timestamp) {
            $groupedByCategory = $group->groupBy(function ($answer) {
                return $answer->question->questionGroup->title;
            })->map(function ($categoryAnswers) {
                return $categoryAnswers->mapWithKeys(function ($answer) {
                    return [$answer->question->title => $answer->answer];
                });
            });

            return [
                'submitted_at' => $timestamp,
                'answers' => $groupedByCategory,
            ];
        })->values();

        return response()->json([
            'aggregate_statistics' => $aggregateStats,
            'individual_summaries' => $individualSummaries,
            'total_submissions' => $individualSummaries->count(),
        ]);
    }
}
