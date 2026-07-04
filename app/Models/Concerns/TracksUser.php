<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait TracksUser
{
    public static function bootTracksUser(): void
    {
        static::creating(function ($model) {
            if (Auth::guard('web')->check()) {
                $model->created_by = $model->created_by ?? Auth::guard('web')->id();
                $model->updated_by = Auth::guard('web')->id();
            }
        });

        static::updating(function ($model) {
            if (Auth::guard('web')->check()) {
                $model->updated_by = Auth::guard('web')->id();
            }
        });
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
