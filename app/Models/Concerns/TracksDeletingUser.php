<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait TracksDeletingUser
{
    public static function bootTracksDeletingUser(): void
    {
        static::deleting(function ($model) {
            if (Auth::guard('web')->check() && ! $model->isForceDeleting()) {
                $model->deleted_by = Auth::guard('web')->id();
                $model->saveQuietly();
            }
        });
    }

    public function excluidoPor()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }
}
