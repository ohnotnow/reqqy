<?php

namespace App\Models;

use App\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'type',
        'name',
        'content',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isTechnicalAssessment(): bool
    {
        return $this->type === DocumentType::TechnicalAssessment;
    }

    public function isPrd(): bool
    {
        return $this->type === DocumentType::Prd;
    }

    public function isResearch(): bool
    {
        return $this->type === DocumentType::Research;
    }
}
