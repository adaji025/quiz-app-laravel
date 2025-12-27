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
  - [Get Single Answer by ID](#4-get-single-answer-by-id)
  - [Get Statistics](#5-get-statistics)
- [Error Handling](#error-handling)
- [Data Models](#data-models)

## Overview

This API provides endpoints for:
- Retrieving quiz questions grouped by categories
- Submitting quiz answers
- Viewing all submitted answers
- Retrieving a single answer by ID
- Generating aggregate statistics and individual submission summaries

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

Retrieve all submitted answers grouped by submission timestamp.

**Endpoint:** `GET /api/answers`

**Description:** Returns all answers grouped by submission time and question category.

**Request:**
```http
GET /api/answers
Content-Type: application/json
```

**Response:** `200 OK`
```json
{
  "answers": {
    "2025-12-27 01:45:00": {
      "Tech Skill Acquired": {
        "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)": "yes",
        "Which career path?": "Data Engineering"
      },
      "Portfolio": {
        "Do you have a professional portfolio showcasing your work?": "yes",
        "How many projects are currently in your portfolio?": "0 Project"
      }
    },
    "2025-12-27 02:30:00": {
      "Tech Skill Acquired": {
        "Have You Taken a Course in any of these Career Paths (Data Analytics, Data Science, Data Engineering, Ethical Hacking, SOC Analyst, GRC, Business Analysis, Project Management)": "no",
        "Which career path?": "Data Science"
      }
    }
  }
}
```

**Note:** Answers are grouped by submission timestamp (YYYY-MM-DD HH:MM:SS format) and then by question category.

---

### 4. Get Single Answer by ID

Retrieve a specific answer by its ID.

**Endpoint:** `GET /api/answers/{id}`

**Description:** Returns detailed information about a single answer including the question and question group details.

**Request:**
```http
GET /api/answers/1
Content-Type: application/json
```

**URL Parameters:**
- `id` (integer, required) - The ID of the answer to retrieve

**Response:** `200 OK`
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

**Error Response:** `404 Not Found`
```json
{
  "error": "Answer not found"
}
```

---

### 5. Get Statistics

Retrieve aggregate statistics and individual submission summaries.

**Endpoint:** `GET /api/statistics`

**Description:** Returns comprehensive statistics including:
- Aggregate statistics: Count and percentage of each answer option per question
- Individual summaries: All submissions with timestamps and answers
- Total number of submissions

**Request:**
```http
GET /api/statistics
Content-Type: application/json
```

**Response:** `200 OK`
```json
{
  "aggregate_statistics": [
    {
      "question_id": 1,
      "question_title": "Have You Taken a Course in any of these Career Paths...",
      "question_group": "Tech Skill Acquired",
      "total_answers": 10,
      "options": [
        {
          "option": "yes",
          "count": 7,
          "percentage": 70.0
        },
        {
          "option": "no",
          "count": 3,
          "percentage": 30.0
        }
      ]
    },
    {
      "question_id": 2,
      "question_title": "Which career path?",
      "question_group": "Tech Skill Acquired",
      "total_answers": 10,
      "options": [
        {
          "option": "Data Analytics",
          "count": 2,
          "percentage": 20.0
        },
        {
          "option": "Data Science",
          "count": 3,
          "percentage": 30.0
        },
        {
          "option": "Data Engineering",
          "count": 5,
          "percentage": 50.0
        }
      ]
    }
  ],
  "individual_summaries": [
    {
      "submitted_at": "2025-12-27 01:45:00",
      "answers": {
        "Tech Skill Acquired": {
          "Have You Taken a Course in any of these Career Paths...": "yes",
          "Which career path?": "Data Engineering"
        },
        "Portfolio": {
          "Do you have a professional portfolio showcasing your work?": "yes",
          "How many projects are currently in your portfolio?": "0 Project"
        }
      }
    }
  ],
  "total_submissions": 1
}
```

**Response Fields:**
- `aggregate_statistics`: Array of statistics for each question
  - `question_id`: Unique question identifier
  - `question_title`: Full question text
  - `question_group`: Category name
  - `total_answers`: Total number of answers received
  - `options`: Array of statistics for each option (count and percentage)
- `individual_summaries`: Array of all submissions
  - `submitted_at`: Timestamp of submission
  - `answers`: Answers grouped by category
- `total_submissions`: Total number of submissions

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
