<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Phase F — token lifecycle for the welcome-mail password setup flow.
 *
 * Two-layer security:
 *   1. The URL itself is signed with `URL::temporarySignedRoute(...)` —
 *      Laravel rejects any tampered query string AND any access after
 *      the 48h expiry encoded in the signature.
 *   2. The `token` query parameter is a 64-hex random string. We store
 *      its sha256 hash in `tenant_password_setup_tokens.token` and
 *      compare hashes on lookup, so a database leak never reveals a
 *      working token.
 *
 * Lifecycle:
 *   issue($user)            → returns a fresh signed URL, replaces any
 *                             existing row for that email (one-shot link)
 *   verify($email, $token)  → bool; only true when both layers pass
 *   consume($email)         → deletes the row after a successful setup
 *
 * Only one outstanding token per email — re-issuing replaces the prior
 * row. This means a user re-triggering "resend setup link" invalidates
 * the previous email's URL automatically.
 */
final class TenantPasswordSetupService
{
    public const ROUTE_NAME    = 'tenant.password.setup';
    public const VALID_HOURS   = 48;
    private const TABLE        = 'tenant_password_setup_tokens';

    /**
     * Issue a fresh signed URL for the user. Returns the absolute URL
     * the welcome mail should embed.
     */
    public function issue(User $user): string
    {
        $plainToken = bin2hex(random_bytes(32));   // 64 hex chars

        DB::table(self::TABLE)->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => hash('sha256', $plainToken),
                'created_at' => now(),
            ],
        );

        return URL::temporarySignedRoute(
            self::ROUTE_NAME,
            CarbonImmutable::now()->addHours(self::VALID_HOURS),
            [
                'token' => $plainToken,
                'email' => $user->email,
            ],
        );
    }

    /**
     * Hash-compare the plaintext token from the URL against the stored
     * hash. Returns true only when the row exists, the hash matches,
     * AND the row is younger than the configured expiry. (The signed
     * URL also enforces expiry, but a defensive double check guards
     * against any signed-URL middleware bypass.)
     */
    public function verify(string $email, string $token): bool
    {
        $row = DB::table(self::TABLE)->where('email', $email)->first();

        if (! $row) {
            return false;
        }

        if (! hash_equals((string) $row->token, hash('sha256', $token))) {
            return false;
        }

        $createdAt = $row->created_at instanceof \DateTimeInterface
            ? CarbonImmutable::instance($row->created_at)
            : CarbonImmutable::parse((string) $row->created_at);

        return $createdAt->isAfter(CarbonImmutable::now()->subHours(self::VALID_HOURS));
    }

    /**
     * Single-use guarantee — call this AFTER the user successfully sets
     * the new password. Deletes the row so the same URL can never reach
     * the form again.
     */
    public function consume(string $email): void
    {
        DB::table(self::TABLE)->where('email', $email)->delete();
    }

    /**
     * Helper used by tests to seed a known token without dispatching the
     * job. Returns the PLAINTEXT token (the one that goes in the URL).
     */
    public function forceIssueForTesting(string $email): string
    {
        $plain = bin2hex(random_bytes(32));

        DB::table(self::TABLE)->updateOrInsert(
            ['email' => $email],
            [
                'token'      => hash('sha256', $plain),
                'created_at' => now(),
            ],
        );

        return $plain;
    }

    /**
     * Build a *random* placeholder password to satisfy the NOT NULL
     * constraint on `users.password` when a user is created via the new
     * flow. This password is NEVER shared — the only way the user can
     * authenticate is through the signed setup link.
     *
     * 64 random bytes → bcrypt → ~60-char hash. Effectively unreachable
     * via guesswork or brute force.
     */
    public static function placeholderPasswordHash(): string
    {
        return Hash::make(Str::random(64));
    }
}
