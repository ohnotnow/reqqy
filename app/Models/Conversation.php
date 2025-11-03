<?php

namespace App\Models;

use App\ConversationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'signed_off_at',
        'status',
        'title',
    ];

    protected function casts(): array
    {
        return [
            'signed_off_at' => 'datetime',
            'status' => ConversationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function isSignedOff(): bool
    {
        return $this->signed_off_at !== null;
    }
}
