<?php

namespace App\Http\Controllers;

use App\Models\QuestionGroup;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function index(): JsonResponse
    {
        $questionGroups = QuestionGroup::with('questions')->get();

        $data = $questionGroups->map(function ($group) {
            return [
                'title' => $group->title,
                'questions' => $group->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'title' => $question->title,
                        'options' => $question->options,
                    ];
                }),
            ];
        });

        return response()->json($data);
    }
}
