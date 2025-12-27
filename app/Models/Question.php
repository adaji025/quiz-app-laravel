<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['question_group_id', 'title', 'options'];

    protected $casts = [
        'options' => 'array',
    ];

    public function questionGroup(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
