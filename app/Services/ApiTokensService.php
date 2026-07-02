<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Creates, lists and revokes Laravel Sanctum personal access tokens.
 *
 * Implemented against Sanctum's PersonalAccessToken model directly (rather than
 * the HasApiTokens trait) so the capability stays self-contained and never has
 * to modify the shared App\Models\User class.
 */
class ApiTokensService
{
    /**
     * List every personal access token that belongs to the given user.
     *
     * @return Collection<int, PersonalAccessToken>
     */
    public function listFor(Model $user): Collection
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->getKey())
            ->latest()
            ->get();
    }

    /**
     * Create a new personal access token for the user.
     *
     * Mirrors Sanctum\HasApiTokens::createToken so the resulting plain-text
     * token authenticates against the `auth:sanctum` guard.
     *
     * @param  array<int, string>  $abilities
     */
    public function createTokenFor(
        Model $user,
        string $name,
        array $abilities = ['*'],
        ?DateTimeInterface $expiresAt = null
    ): NewAccessToken {
        $plainTextToken = sprintf(
            '%s%s%s',
            config('sanctum.token_prefix', ''),
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );

        $token = PersonalAccessToken::forceCreate([
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id' => $user->getKey(),
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Revoke (delete) a token, scoped to its owner so users cannot delete
     * tokens that belong to someone else.
     */
    public function revoke(Model $user, int|string $tokenId): bool
    {
        return (bool) PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->getKey())
            ->whereKey($tokenId)
            ->delete();
    }
}
