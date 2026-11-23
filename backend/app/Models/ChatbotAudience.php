<?php

namespace App\Models;

class ChatbotAudience extends LookupModel
{
    protected $table = 'chatbot_audiences';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
