<?php

namespace App\Models;

use App\ApplicationCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'category',
        'source_conversation_id',
        'name',
        'short_description',
        'overview',
        'is_automated',
        'status',
        'url',
        'repo',
    ];

    protected function casts(): array
    {
        return [
            'category' => ApplicationCategory::class,
            'is_automated' => 'boolean',
        ];
    }

    public function canHaveFeaturesRequested(): bool
    {
        return $this->category === ApplicationCategory::Internal;
    }

    public function isProposal(): bool
    {
        return $this->category === ApplicationCategory::Proposed;
    }

    public function isExternal(): bool
    {
        return $this->category === ApplicationCategory::External;
    }

    public function isInternal(): bool
    {
        return $this->category === ApplicationCategory::Internal;
    }

    public function promoteToInternal(): void
    {
        if (!$this->isProposal()) {
            throw new \Exception('Only proposed applications can be promoted');
        }

        $this->category = ApplicationCategory::Internal;
        $this->save();
    }

    public function sourceConversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'source_conversation_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
