<?php

namespace App\Models;

use App\Enums\ChatbotAudience;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatbotSession extends Model
{

    use Auditable, SoftDeletes;

    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;



    protected $table = 'chatbot_sessions';

    protected $guarded = [];

    protected $casts = [
        'audience' => ChatbotAudience::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'session_id');
    }

    public function chatbotMessages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'session_id');
    }

    protected function lookups(): array
    {
        return [
            'audience' => ['chatbot_audience_id', ChatbotAudience::class, 'candidate'],
        ];
    }

    public function chatbotAudience(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChatbotAudience::class, 'chatbot_audience_id');
    }
}
