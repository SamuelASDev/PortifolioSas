<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiAccessToken extends Model
{
    public const PREFIX = 'vtx_';

    /** Validades oferecidas no painel, em dias (null = sem validade). */
    public const DURATIONS = [1 => '1 dia', 7 => '7 dias', 30 => '30 dias', 90 => '90 dias', 0 => 'Sem validade'];

    protected $fillable = ['name', 'token_hash', 'last_used_at', 'expires_at'];

    protected $hidden = ['token_hash'];

    protected $casts = ['last_used_at' => 'datetime', 'expires_at' => 'datetime'];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array{0: self, 1: string} O registro e o token em texto puro (exibido uma única vez). */
    public static function issue(User $user, string $name, int $days = 30): array
    {
        $plain = self::PREFIX.Str::random(48);
        $token = $user->aiAccessTokens()->create([
            'name' => $name, 'token_hash' => hash('sha256', $plain), 'expires_at' => $days > 0 ? now()->addDays($days) : null,
        ]);

        return [$token, $plain];
    }

    public static function findByPlain(string $plain): ?self
    {
        return str_starts_with($plain, self::PREFIX)
            ? self::with('user')->where('token_hash', hash('sha256', $plain))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->first()
            : null;
    }
}
