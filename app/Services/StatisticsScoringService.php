<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Question;

class StatisticsScoringService
{
    /**
     * Calculate the score for a category based on its answers
     *
     * @param \Illuminate\Support\Collection $categoryAnswers Answers for this category
     * @param \Illuminate\Support\Collection $categoryQuestions Questions for this category
     * @return array ['score' => int, 'maxScore' => int, 'percentage' => float]
     */
    public function calculateCategoryScore($categoryAnswers, $categoryQuestions): array
    {
        $questions = $categoryQuestions->sortBy('id')->values();
        $answers = $categoryAnswers->keyBy('question_id');
        
        $totalScore = 0;
        $maxScore = 0;
        
        // Handle single question categories
        if ($questions->count() === 1) {
            $question = $questions->first();
            $answer = $answers->get($question->id);
            
            if ($answer) {
                $score = $this->getQuestionScore($answer->answer, $question, null);
                $totalScore = $score;
                $maxScore = 5; // Single question categories are out of 5
            } else {
                $maxScore = 5;
            }
        } else {
            // Two-question categories (out of 10)
            $maxScore = 10;
            
            $firstQuestion = $questions->first();
            $secondQuestion = $questions->last();
            
            $firstAnswer = $answers->get($firstQuestion->id);
            $secondAnswer = $answers->get($secondQuestion->id);
            
            // Score first question (yes/no)
            if ($firstAnswer) {
                $firstScore = $this->getQuestionScore($firstAnswer->answer, $firstQuestion, null);
                $totalScore += $firstScore;
            }
            
            // Score second question (depends on first answer)
            if ($secondAnswer && $firstAnswer) {
                $secondScore = $this->getQuestionScore($secondAnswer->answer, $secondQuestion, $firstAnswer->answer);
                $totalScore += $secondScore;
            }
        }
        
        $percentage = $this->calculatePercentage($totalScore, $maxScore);
        
        return [
            'score' => $totalScore,
            'maxScore' => $maxScore,
            'percentage' => $percentage,
        ];
    }
    
    /**
     * Get the score for an individual question
     *
     * @param string $answer The answer value
     * @param Question $question The question model
     * @param string|null $firstAnswer The answer to the first question (for conditional scoring)
     * @return int The score (0-5)
     */
    public function getQuestionScore(string $answer, Question $question, ?string $firstAnswer): int
    {
        $options = $question->options;
        
        // Yes/No questions
        if (in_array('yes', $options) && in_array('no', $options)) {
            return $answer === 'yes' ? 5 : 0;
        }
        
        // 1-5 Scale questions
        if (in_array('1', $options) && in_array('5', $options)) {
            // If first answer is no, second question gets 0
            if ($firstAnswer === 'no') {
                return 0;
            }
            // Otherwise use the direct value
            return (int) $answer;
        }
        
        // Portfolio projects question
        if (str_contains($question->title, 'How many projects')) {
            return $this->mapPortfolioScore($answer);
        }
        
        // Career path selection (any selection = 5 if first is yes)
        if (str_contains($question->title, 'Which career path') || str_contains($question->title, 'career path')) {
            if ($firstAnswer === 'no') {
                return 0;
            }
            // Any career path selection = 5 points
            return 5;
        }
        
        // Default: return 0 if we can't determine the score
        return 0;
    }
    
    /**
     * Map portfolio project count answer to points
     *
     * @param string $answer The answer value
     * @return int The score (0-5)
     */
    public function mapPortfolioScore(string $answer): int
    {
        return match ($answer) {
            '0 Project' => 0,
            '1-5 Projects' => 1,
            '5-10 Projects' => 3,
            '10+ Projects' => 5,
            default => 0,
        };
    }
    
    /**
     * Calculate percentage from score and max score
     *
     * @param int $score The actual score
     * @param int $maxScore The maximum possible score
     * @return float The percentage (0-100)
     */
    public function calculatePercentage(int $score, int $maxScore): float
    {
        if ($maxScore === 0) {
            return 0.0;
        }
        
        return round(($score / $maxScore) * 100, 2);
    }
}

