# Statistics Logic Documentation

This document explains how statistics are calculated for quiz submissions. The system uses a scoring mechanism that converts answers into category-level percentages.

## Overview

Each submission receives a percentage score (0-100%) for each category. Categories are scored based on their question structure:
- **Two-question categories**: Scored out of 10 points
- **Single-question categories**: Scored out of 5 points

## Scoring Rules

### Two-Question Categories (Out of 10 Points)

Categories with two questions follow this pattern:

#### First Question (Yes/No) - 5 Points
- **Answer: `yes`** → 5 points
- **Answer: `no`** → 0 points

#### Second Question - 5 Points (Conditional)

The second question's score depends on the first question's answer:

1. **If first answer is `no`**: Second question automatically gets **0 points**
2. **If first answer is `yes`**: Second question is scored based on its answer type:

   **a) 1-5 Scale Questions:**
   - Direct value mapping: `1` = 1 point, `2` = 2 points, `3` = 3 points, `4` = 4 points, `5` = 5 points
   - Examples:
     - "On a scale of 1–5, how confident are you..." → Answer `4` = 4 points
     - "On a scale of 1–5, how confident are you in applying SEAT..." → Answer `5` = 5 points

   **b) Portfolio Projects Question:**
   - "How many projects are currently in your portfolio?"
   - Mapping:
     - `"0 Project"` → 0 points
     - `"1-5 Projects"` → 1 point
     - `"5-10 Projects"` → 3 points
     - `"10+ Projects"` → 5 points

   **c) Career Path Selection:**
   - "Which career path?"
   - Any selection (Data Analytics, Data Science, Data Engineering, etc.) → **5 points** (if first answer is yes)
   - If first answer is no → 0 points

   **d) Yes/No Questions (LinkedIn Optimization):**
   - Second question is also yes/no
   - `yes` = 5 points, `no` = 0 points
   - Both questions are scored independently

### Single-Question Categories (Out of 5 Points)

Categories with only one question:

#### References
- **Answer: `yes`** → 5 points (100%)
- **Answer: `no`** → 0 points (0%)

## Category Breakdown

### 1. Tech Skill Acquired (10 points)
- **Q1**: "Have You Taken a Course..." → `yes` = 5, `no` = 0
- **Q2**: "Which career path?" → Any selection = 5 (if Q1 is yes), 0 (if Q1 is no)

**Example:**
- Q1: `yes` (5 points) + Q2: `Data Science` (5 points) = **10/10 = 100%**
- Q1: `no` (0 points) + Q2: `Data Science` (0 points) = **0/10 = 0%**

### 2. Portfolio (10 points)
- **Q1**: "Do you have a professional portfolio..." → `yes` = 5, `no` = 0
- **Q2**: "How many projects..." → Mapped to 0, 1, 3, or 5 points (if Q1 is yes)

**Example:**
- Q1: `yes` (5 points) + Q2: `5-10 Projects` (3 points) = **8/10 = 80%**
- Q1: `yes` (5 points) + Q2: `10+ Projects` (5 points) = **10/10 = 100%**
- Q1: `no` (0 points) + Q2: `10+ Projects` (0 points) = **0/10 = 0%**

### 3. CV (ATS Compliance) (10 points)
- **Q1**: "Is your CV keyword-optimized..." → `yes` = 5, `no` = 0
- **Q2**: "On a scale of 1–5, how confident..." → Direct value 1-5 (if Q1 is yes)

**Example:**
- Q1: `yes` (5 points) + Q2: `4` (4 points) = **9/10 = 90%**
- Q1: `yes` (5 points) + Q2: `5` (5 points) = **10/10 = 100%**
- Q1: `no` (0 points) + Q2: `5` (0 points) = **0/10 = 0%**

### 4. LinkedIn Optimization (10 points)
- **Q1**: "Do you have an optimized LinkedIn profile..." → `yes` = 5, `no` = 0
- **Q2**: "Do recruiters reach out to you..." → `yes` = 5, `no` = 0

**Example:**
- Q1: `yes` (5 points) + Q2: `yes` (5 points) = **10/10 = 100%**
- Q1: `yes` (5 points) + Q2: `no` (0 points) = **5/10 = 50%**
- Q1: `no` (0 points) + Q2: `yes` (5 points) = **5/10 = 50%**

### 5. References (5 points)
- **Q1**: "Do you have at least one professional reference..." → `yes` = 5, `no` = 0

**Example:**
- Q1: `yes` = **5/5 = 100%**
- Q1: `no` = **0/5 = 0%**

### 6. Interview Readiness – SEAT (10 points)
- **Q1**: "Do you know how to use the SEAT approach..." → `yes` = 5, `no` = 0
- **Q2**: "On a scale of 1–5, how confident..." → Direct value 1-5 (if Q1 is yes)

**Example:**
- Q1: `yes` (5 points) + Q2: `5` (5 points) = **10/10 = 100%**
- Q1: `yes` (5 points) + Q2: `3` (3 points) = **8/10 = 80%**
- Q1: `no` (0 points) + Q2: `5` (0 points) = **0/10 = 0%**

### 7. Interview Readiness – STAR (10 points)
- **Q1**: "Do you know how to use the STAR method..." → `yes` = 5, `no` = 0
- **Q2**: "On a scale of 1–5, how confident..." → Direct value 1-5 (if Q1 is yes)

**Example:**
- Q1: `yes` (5 points) + Q2: `4` (4 points) = **9/10 = 90%**
- Q1: `yes` (5 points) + Q2: `3` (3 points) = **8/10 = 80%**
- Q1: `no` (0 points) + Q2: `5` (0 points) = **0/10 = 0%**

## Percentage Calculation

The percentage is calculated using the formula:

```
Percentage = (Total Score / Maximum Score) × 100
```

Where:
- **Total Score**: Sum of points from all questions in the category
- **Maximum Score**: 10 for two-question categories, 5 for single-question categories

The result is rounded to 2 decimal places.

## API Response Format

### GET /api/statistics
Returns an array of submissions with category percentages:

```json
[
  {
    "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
    "submitted_at": "2025-12-27 04:57:01",
    "categories": {
      "Tech Skill Acquired": 100.0,
      "Portfolio": 80.0,
      "CV (ATS Compliance)": 90.0,
      "LinkedIn Optimization": 100.0,
      "References": 100.0,
      "Interview Readiness – SEAT": 100.0,
      "Interview Readiness – STAR": 80.0
    }
  }
]
```

### GET /api/statistics/{id}
Returns category percentages for a specific submission:

```json
{
  "id": "93d896a5-dec0-41f1-b068-c6edbed3b186",
  "submitted_at": "2025-12-27 04:57:01",
  "categories": {
    "Tech Skill Acquired": 100.0,
    "Portfolio": 80.0,
    "CV (ATS Compliance)": 90.0,
    "LinkedIn Optimization": 100.0,
    "References": 100.0,
    "Interview Readiness – SEAT": 100.0,
    "Interview Readiness – STAR": 80.0
  }
}
```

## Edge Cases

1. **Missing Answers**: If a question is not answered, it receives 0 points
2. **Invalid Answers**: Invalid answers are treated as 0 points
3. **Conditional Scoring**: Second questions always get 0 points if the first question is answered `no`, regardless of the second answer value
4. **Single Question Categories**: Categories with only one question are scored out of 5 points maximum

## Implementation Details

The scoring logic is implemented in `app/Services/StatisticsScoringService.php` with the following key methods:

- `calculateCategoryScore()`: Calculates the total score and percentage for a category
- `getQuestionScore()`: Determines the score for an individual question based on its type and the first question's answer
- `mapPortfolioScore()`: Maps portfolio project count answers to points
- `calculatePercentage()`: Converts score to percentage

The service is used by `StatisticsController` to generate category-level statistics for submissions.

