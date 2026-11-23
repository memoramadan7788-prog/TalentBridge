<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordVisibility;

class ContactMessage extends Model
{
    use \App\Models\Concerns\HasLookupAttributes;

    use SoftDeletes, HasRecordVisibility;

    protected $table = 'contact_messages';
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    protected function lookups(): array
    {
        return [
            'status' => ['status_id', Status::class, 'new', 'contact_message'],
        ];
    }

    public function contactMessageStatus(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
