<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    
    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (empty($model->UID)) {
                    $model->UID = Auth::id();
                }
                if (empty($model->LUID)) {
                    $model->LUID = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->LUID = Auth::id();
            }
        });
    }

   
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UID');
    }

    
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'LUID');
    }
}

