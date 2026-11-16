<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiAccessToken extends Model
{
    public const PREFIX = 'vtx_';

    protected $fillable = ['name', 'token_hash', 'last_used_at'];

    protected $hidden = ['token_hash'];

    protected $casts = ['last_used_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array{0: self, 1: string} O registro e o token em texto puro (exibido uma única vez). */
    public static function issue(User $user, string $name): array
    {
        $plain = self::PREFIX.Str::random(48);
        $token = $user->aiAccessTokens()->create(['name' => $name, 'token_hash' => hash('sha256', $plain)]);

        return [$token, $plain];
    }

    public static function findByPlain(string $plain): ?self
    {
        return str_starts_with($plain, self::PREFIX)
            ? self::with('user')->where('token_hash', hash('sha256', $plain))->first()
            : null;
    }
}
