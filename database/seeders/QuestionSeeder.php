<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => 'Tech Skill Acquired',
                'questions' => [
                    [
                        'title' => 'Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'Which career path?',
                        'options' => [
                            'Data Analytics',
                            'Data Science',
                            'Data Engineering',
                            'SOC Analyst',
                            'GRC',
                            'Ethical Hacking',
                            'Business Analysis',
                            'Project Management',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Portfolio',
                'questions' => [
                    [
                        'title' => 'Do you have a professional portfolio showcasing your work?',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'How many projects are currently in your portfolio?',
                        'options' => ['0 Project', '1-5 Projects', '5-10 Projects', '10+ Projects'],
                    ],
                ],
            ],
            [
                'title' => 'CV (ATS Compliance)',
                'questions' => [
                    [
                        'title' => 'Is your CV keyword-optimized for Applicant Tracking Systems (ATS)?',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'On a scale of 1–5, how confident are you that your CV matches job descriptions in your field?',
                        'options' => ['1', '2', '3', '4', '5'],
                    ],
                ],
            ],
            [
                'title' => 'LinkedIn Optimization',
                'questions' => [
                    [
                        'title' => 'Do you have an optimized LinkedIn profile that highlights your skills and achievements in your preferred career path selected in question 1?',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'Do recruiters reach out to you on LinkedIn?',
                        'options' => ['yes', 'no'],
                    ],
                ],
            ],
            [
                'title' => 'References',
                'questions' => [
                    [
                        'title' => 'Do you have at least one professional/organizational reference in your preferred career path?',
                        'options' => ['yes', 'no'],
                    ],
                ],
            ],
            [
                'title' => 'Interview Readiness – SEAT',
                'questions' => [
                    [
                        'title' => 'Do you know how to use the SEAT (Skills, Experience, Achievements, Traits) approach to answer \'Tell me about yourself\'?',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'On a scale of 1–5, how confident are you in applying SEAT during interviews?',
                        'options' => ['1', '2', '3', '4', '5'],
                    ],
                ],
            ],
            [
                'title' => 'Interview Readiness – STAR',
                'questions' => [
                    [
                        'title' => 'Do you know how to use the STAR (Situation, Task, Action, Result) method to answer competency-based questions?',
                        'options' => ['yes', 'no'],
                    ],
                    [
                        'title' => 'On a scale of 1–5, how confident are you in applying STAR during interviews?',
                        'options' => ['1', '2', '3', '4', '5'],
                    ],
                ],
            ],
        ];

        foreach ($data as $groupData) {
            $questionGroup = QuestionGroup::create([
                'title' => $groupData['title'],
            ]);

            foreach ($groupData['questions'] as $questionData) {
                Question::create([
                    'question_group_id' => $questionGroup->id,
                    'title' => $questionData['title'],
                    'options' => $questionData['options'],
                ]);
            }
        }
    }
}
