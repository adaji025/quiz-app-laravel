# Quiz API Documentation

A Laravel-based REST API for managing quiz questions, collecting answers, and generating statistics.

## Table of Contents

- [Overview](#overview)
- [Base URL](#base-url)
- [Setup](#setup)
- [API Endpoints](#api-endpoints)
  - [Get Questions](#1-get-questions)
  - [Submit Answers](#2-submit-answers)
  - [Get All Answers](#3-get-all-answers)
  - [Get Answer(s) by ID](#4-get-answers-by-id)
  - [Get Statistics](#5-get-statistics)
- [Error Handling](#error-handling)
- [Data Models](#data-models)

## Overview

This API provides endpoints for:
- Retrieving quiz questions grouped by categories
- Submitting quiz answers (each submission gets a unique UUID)
- Viewing all submitted answers grouped by submission
- Retrieving answers by ID (supports both individual answer IDs and submission UUIDs)
- Generating statistics per submission (all submissions or by submission ID)

## Base URL

```
http://quiz-app.test/api
```

All endpoints are prefixed with `/api`.

## Setup

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Run Migrations**
   ```bash
   php artisan migrate
   ```

3. **Seed Database**
   ```bash
   php artisan db:seed --class=QuestionSeeder
   ```

4. **Start Server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### 1. Get Questions

Retrieve all quiz questions grouped by category.

**Endpoint:** `GET /api/questions`

**Description:** Returns all question groups with their associated questions and available options.

**Request:**
```http
GET /api/questions
Content-Type: application/json
```

**Response:** `200 OK`
```json
[
  {
    "title": "Tech Skill Acquired",
    "questions": [
      {
        "id": 1,
        "title": "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)",
        "options": ["yes", "no"]
      },
      {
        "id": 2,
        "title": "Which career path?",
        "options": [
          "Data Analytics",
          "Data Science",
          "Data Engineering",
          "SOC Analyst",
          "GRC",
          "Ethical Hacking",
          "Business Analysis",
          "Project Management"
        ]
      }
    ]
  },
  {
    "title": "Portfolio",
    "questions": [
      {
        "id": 3,
        "title": "Do you have a professional portfolio showcasing your work?",
        "options": ["yes", "no"]
      },
      {
        "id": 4,
        "title": "How many projects are currently in your portfolio?",
        "options": ["0 Project", "1-5 Projects", "5-10 Projects", "10+ Projects"]
      }
    ]
  }
]
```

---

### 2. Submit Answers

Submit answers to quiz questions.

**Endpoint:** `POST /api/answers`

**Description:** Accepts answers in a nested structure grouped by question category. Validates that answers match available options for each question.

**Request:**
```http
POST /api/answers
Content-Type: application/json
```

**Request Body:**
```json
{
  "Tech Skill Acquired": {
    "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)": "yes",
    "Which career path?": "Data Engineering"
  },
  "Portfolio": {
    "Do you have a professional portfolio showcasing your work?": "yes",
    "How many projects are currently in your portfolio?": "0 Project"
  },
  "CV (ATS Compliance)": {
    "Is your CV keyword-optimized for Applicant Tracking Systems (ATS)?": "yes",
    "On a scale of 1–5, how confident are you that your CV matches job descriptions in your field?": "4"
  },
  "LinkedIn Optimization": {
    "Do you have an optimized LinkedIn profile that highlights your skills and achievements in your preferred career path selected in question 1?": "yes",
    "Do recruiters reach out to you on LinkedIn?": "yes"
  },
  "References": {
    "Do you have at least one professional/organizational reference in your preferred career path?": "yes"
  },
  "Interview Readiness – SEAT": {
    "Do you know how to use the SEAT (Skills, Experience, Achievements, Traits) approach to answer 'Tell me about yourself'?": "yes",
    "On a scale of 1–5, how confident are you in applying SEAT during interviews?": "4"
  },
  "Interview Readiness – STAR": {
    "Do you know how to use the STAR (Situation, Task, Action, Result) method to answer competency-based questions?": "yes",
    "On a scale of 1–5, how confident are you in applying STAR during interviews?": "4"
  }
}
```

**Response:** `201 Created`
```json
{
  "message": "Answers saved successfully",
  "submission_id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
  "answers": [
    {
      "id": 1,
      "question_id": 1,
      "question_title": "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)",
      "answer": "yes",
      "created_at": "2025-12-27T01:45:00.000000Z"
    },
    {
      "id": 2,
      "question_id": 2,
      "question_title": "Which career path?",
      "answer": "Data Engineering",
      "created_at": "2025-12-27T01:45:00.000000Z"
    }
  ]
}
```

**Note:** The response includes a `submission_id` (UUID) that uniquely identifies this entire submission. All answers in the response share the same `submission_id`.

**Error Response:** `422 Unprocessable Entity`
```json
{
  "error": "Invalid answer 'maybe' for question 'Have You Taken a Course...'. Valid options are: yes, no"
}
```

**Validation Rules:**
- Each top-level key must be a valid question group title
- Each nested key must be a valid question title
- Each answer value must match one of the question's available options
- Question group titles and question titles must match exactly (case-sensitive)

---

### 3. Get All Answers

Retrieve all submitted answers grouped by submission.

**Endpoint:** `GET /api/answers`

**Description:** Returns an array of all submissions, each with its submission ID, timestamp, and answers grouped by question category.

**Request:**
```http
GET /api/answers
Content-Type: application/json
```

**Response:** `200 OK`
```json
[
  {
    "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
    "submitted_at": "2025-12-27 04:57:01",
    "answers": {
      "Tech Skill Acquired": {
        "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)": "yes",
        "Which career path?": "Data Science"
      },
      "Portfolio": {
        "Do you have a professional portfolio showcasing your work?": "yes",
        "How many projects are currently in your portfolio?": "5-10 Projects"
      }
    }
  },
  {
    "id": "another-submission-uuid",
    "submitted_at": "2025-12-27 05:00:00",
    "answers": {
      "Tech Skill Acquired": {
        "Have You Taken a Course in any of these Career Paths...": "no",
        "Which career path?": "Data Engineering"
      }
    }
  }
]
```

**Note:** Returns an array of submissions, each with a unique `id` (submission_id UUID), `submitted_at` timestamp, and `answers` grouped by question category.

---

### 4. Get Answer(s) by ID

Retrieve a specific answer record or an entire submission by ID.

**Endpoint:** `GET /api/answers/{id}`

**Description:** 
- If `id` is a numeric value: Returns a single answer record
- If `id` is a UUID: Returns all answers for that submission as an array

**Request:**
```http
GET /api/answers/1
Content-Type: application/json
```

**URL Parameters:**
- `id` (integer or UUID string, required) - The ID of the answer record (integer) or submission (UUID)

**Response for Numeric ID (Single Answer):** `200 OK`
```json
{
  "id": 1,
  "question_id": 1,
  "question_title": "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)",
  "question_group": "Tech Skill Acquired",
  "answer": "yes",
  "created_at": "2025-12-27T01:45:00.000000Z",
  "updated_at": "2025-12-27T01:45:00.000000Z"
}
```

**Response for UUID (Submission):** `200 OK`
```json
{
  "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
  "submitted_at": "2025-12-27 04:57:01",
  "answers": [
    {
      "id": 1,
      "question_id": 1,
      "question_title": "Have You Taken a Course...",
      "question_group": "Tech Skill Acquired",
      "answer": "yes",
      "created_at": "2025-12-27T04:57:01.000000Z",
      "updated_at": "2025-12-27T04:57:01.000000Z"
    },
    {
      "id": 2,
      "question_id": 2,
      "question_title": "Which career path?",
      "question_group": "Tech Skill Acquired",
      "answer": "Data Science",
      "created_at": "2025-12-27T04:57:01.000000Z",
      "updated_at": "2025-12-27T04:57:01.000000Z"
    }
  ]
}
```

**Error Response:** `404 Not Found`
```json
{
  "error": "Answer not found",
  "message": "No answer record found with ID: 999",
  "searched_id": "999",
  "id_type": "answer_id (integer)",
  "hint": "Make sure the answer ID exists. Answer IDs are auto-incrementing integers."
}
```

**Error Response for Invalid Format:** `422 Unprocessable Entity`
```json
{
  "error": "Invalid ID format",
  "message": "The provided ID 'invalid-id' is not a valid UUID or numeric ID",
  "searched_id": "invalid-id",
  "expected_formats": {
    "UUID format": "e.g., 93d896a5-dec0-41f1-b068-c6edbed3b186 (for submission_id)",
    "Numeric ID": "e.g., 1, 2, 3 (for individual answer record ID)"
  }
}
```

---

### 5. Get Statistics

Retrieve statistics for all submissions or a specific submission.

**Endpoint:** `GET /api/statistics` or `GET /api/statistics/{id}`

**Description:** 
- `GET /api/statistics`: Returns an array of statistics for all submissions
- `GET /api/statistics/{id}`: Returns statistics for a specific submission by submission ID (UUID)

**Request:**
```http
GET /api/statistics
Content-Type: application/json
```

**Response:** `200 OK` (All Submissions)
```json
[
  {
    "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
    "submitted_at": "2025-12-27 04:57:01",
    "statistics": [
      {
        "question_id": 1,
        "question_title": "Have You Taken a Course in any of these Career Paths...",
        "question_group": "Tech Skill Acquired",
        "total_answers": 1,
        "options": [
          {
            "option": "yes",
            "count": 1,
            "percentage": 100.0
          },
          {
            "option": "no",
            "count": 0,
            "percentage": 0.0
          }
        ]
      },
      {
        "question_id": 2,
        "question_title": "Which career path?",
        "question_group": "Tech Skill Acquired",
        "total_answers": 1,
        "options": [
          {
            "option": "Data Science",
            "count": 1,
            "percentage": 100.0
          }
        ]
      }
    ],
    "answers": {
      "Tech Skill Acquired": {
        "Have You Taken a Course in any of these Career Paths...": "yes",
        "Which career path?": "Data Science"
      },
      "Portfolio": {
        "Do you have a professional portfolio showcasing your work?": "yes",
        "How many projects are currently in your portfolio?": "5-10 Projects"
      }
    }
  }
]
```

**Request for Specific Submission:**
```http
GET /api/statistics/93d896a5-dec0-41f1-b068-c6edbed3b186
Content-Type: application/json
```

**Response:** `200 OK` (Single Submission)
```json
{
  "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
  "submitted_at": "2025-12-27 04:57:01",
  "statistics": [
    {
      "question_id": 1,
      "question_title": "Have You Taken a Course in any of these Career Paths...",
      "question_group": "Tech Skill Acquired",
      "total_answers": 1,
      "options": [
        {
          "option": "yes",
          "count": 1,
          "percentage": 100.0
        }
      ]
    }
  ],
  "answers": {
    "Tech Skill Acquired": {
      "Have You Taken a Course in any of these Career Paths...": "yes",
      "Which career path?": "Data Science"
    }
  }
}
```

**Response Fields:**
- `id`: Submission ID (UUID)
- `submitted_at`: Timestamp when the submission was made
- `statistics`: Array of statistics for each question in this submission
  - `question_id`: Unique question identifier
  - `question_title`: Full question text
  - `question_group`: Category name
  - `total_answers`: Total number of answers for this question in this submission
  - `options`: Array of statistics for each option (count and percentage)
- `answers`: Answers grouped by question category

**Error Response:** `404 Not Found`
```json
{
  "error": "Submission not found",
  "message": "No submission found with ID: 93d896a5-dec0-41f1-b068-c6edbed3b186",
  "searched_id": "93d896a5-dec0-41f1-b068-c6edbed3b186"
}
```

---

## Error Handling

The API uses standard HTTP status codes:

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

**Error Response Format:**
```json
{
  "error": "Error message description"
}
```

Or for validation errors:
```json
{
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## Data Models

### Question Group
- `id` (integer) - Primary key
- `title` (string) - Category name
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Question
- `id` (integer) - Primary key
- `question_group_id` (integer) - Foreign key to question_groups
- `title` (text) - Question text
- `options` (JSON array) - Available answer options
- `created_at` (timestamp)
- `updated_at` (timestamp)

### Answer
- `id` (integer) - Primary key
- `submission_id` (string, UUID) - Unique identifier for the submission (all answers in one POST request share the same submission_id)
- `question_id` (integer) - Foreign key to questions
- `answer` (string) - Selected answer option
- `created_at` (timestamp)
- `updated_at` (timestamp)

---

## Question Categories

The API includes the following question categories:

1. **Tech Skill Acquired** - Questions about career path courses
2. **Portfolio** - Questions about professional portfolio
3. **CV (ATS Compliance)** - Questions about CV optimization
4. **LinkedIn Optimization** - Questions about LinkedIn profile
5. **References** - Questions about professional references
6. **Interview Readiness – SEAT** - Questions about SEAT interview method
7. **Interview Readiness – STAR** - Questions about STAR interview method

---

## Notes

- All endpoints return JSON responses
- Question group titles and question titles must match exactly (case-sensitive) when submitting answers
- Answers are validated against available options for each question
- Statistics are calculated in real-time based on all submitted answers
- Timestamps are in UTC format
