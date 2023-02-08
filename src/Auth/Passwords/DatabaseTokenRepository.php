<?php

namespace Orvital\Extensions\Auth\Passwords;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     */
    protected ConnectionInterface $connection;

    /**
     * The Hasher implementation.
     */
    protected HasherContract $hasher;

    /**
     * The token database table.
     */
    protected string $table;

    /**
     * The hashing key.
     */
    protected string $hashKey;

    /**
     * The number of seconds a token should last.
     */
    protected int $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     */
    protected int $throttle;

    /**
     * Create a new token repository instance.
     *
     * @return void
     */
    public function __construct(ConnectionInterface $connection, HasherContract $hasher,
                                string $table, string $hashKey,
                                int $expires = 60, int $throttle = 60)
    {
        $this->connection = $connection;
        $this->hasher = $hasher;
        $this->table = $table;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->throttle = $throttle;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($user, $token));

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        $record = (array) $this->getTable()->where('email', $user->getEmailForPasswordReset())->first();

        return $record && ! $this->tokenExpired($record['created_at']) && $this->hasher->check($token, $record['token']);
    }

    /**
     * {@inheritdoc}
     */
    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = (array) $this->getTable()->where('email', $user->getEmailForPasswordReset())->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Determine if the token was recently created.
     */
    protected function tokenRecentlyCreated(string $createdAt): bool
    {
        if ($this->throttle <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds($this->throttle)->isFuture();
    }

    /**
     * Determine if the token has expired.
     */
    protected function tokenExpired(string $createdAt): bool
    {
        return Carbon::parse($createdAt)->addSeconds($this->expires)->isPast();
    }

    /**
     * Delete all existing reset tokens from the database.
     */
    protected function deleteExisting(CanResetPasswordContract $user): int
    {
        return $this->getTable()->where('email', $user->getEmailForPasswordReset())->delete();
    }

    /**
     * Build the record payload for the table.
     */
    protected function getPayload(CanResetPasswordContract $user, string $token): array
    {
        return [
            'id' => strtolower((string) Str::ulid()),
            'email' => $user->getEmailForPasswordReset(),
            'token' => $this->hasher->make($token),
            'created_at' => new Carbon(),
        ];
    }

    /**
     * Create a new token for the user.
     */
    public function createNewToken(): string
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Get the database connection instance.
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Begin a new database query against the table.
     */
    protected function getTable(): Builder
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the hasher instance.
     */
    public function getHasher(): HasherContract
    {
        return $this->hasher;
    }
}
