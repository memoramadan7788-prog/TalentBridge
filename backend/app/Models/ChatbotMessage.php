<?php

namespace App\Models;

use App\Enums\ChatbotSender;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatbotMessage extends Model
{

    use Auditable, SoftDeletes;

    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, Auditable;



    protected $table = 'chatbot_messages';

    protected $guarded = [];

    protected $casts = [
        'sender_type' => ChatbotSender::class,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatbotSession::class, 'session_id');
    }

    protected function lookups(): array
    {
        return [
            'sender_type' => ['chatbot_sender_type_id', ChatbotSenderType::class, null],
        ];
    }

    public function chatbotSenderType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChatbotSenderType::class, 'chatbot_sender_type_id');
    }
}
