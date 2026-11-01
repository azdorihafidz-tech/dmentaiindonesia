<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'user_name', 'action', 'subject_type', 'subject_name', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public static function record(string $action, string $subjectType, string $subjectName): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        static::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'user_name' => $user->name,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_name' => $subjectName,
            'created_at' => now(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
