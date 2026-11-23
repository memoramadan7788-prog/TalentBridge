<?php

namespace App\Models;

class ChatbotSenderType extends LookupModel
{
    protected $table = 'chatbot_sender_types';
    protected static string $keyColumn = 'code';
    protected static bool $autoCreate = false;
}
