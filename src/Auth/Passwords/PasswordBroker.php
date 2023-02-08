<?php

namespace Orvital\Extensions\Auth\Passwords;

use Closure;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Support\Arr;
use UnexpectedValueException;

class PasswordBroker implements PasswordBrokerContract
{
    /**
     * The password token repository.
     */
    protected TokenRepositoryInterface $tokens;

    /**
     * The user provider implementation.
     */
    protected UserProviderContract $users;

    /**
     * Create a new password broker instance.
     *
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens, UserProviderContract $users)
    {
        $this->users = $users;
        $this->tokens = $tokens;
    }

    /**
     * Send a password reset link to a user.
     */
    public function sendResetLink(array $credentials, Closure $callback = null): string
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($this->tokenRecentlyCreated($user)) {
            return static::RESET_THROTTLED;
        }

        $token = $this->createToken($user);

        if ($callback) {
            $callback($user, $token);
        } else {
            // Once we have the reset token, we are ready to send the message out to this
            // user with a link to reset their password. We will then redirect back to
            // the current URI having nothing set in the session to indicate errors.
            $user->sendPasswordResetNotification($token);
        }

        return static::RESET_LINK_SENT;
    }

    /**
     * Reset the password for the given token.
     */
    public function reset(array $credentials, Closure $callback): mixed
    {
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if (! $this->tokenExists($user, $credentials['token'])) {
            return static::INVALID_TOKEN;
        }

        // Once the reset has been validated, we'll call the given callback with the
        // new password. This gives the user an opportunity to store the password
        // in their persistent storage. Then we'll delete the token and return.
        $callback($user, $credentials['password']);

        $this->deleteToken($user);

        return static::PASSWORD_RESET;
    }

    /**
     * Get the user for the given credentials.
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials): ?CanResetPasswordContract
    {
        $user = $this->users->retrieveByCredentials(Arr::except($credentials, ['token']));

        if ($user && ! $user instanceof CanResetPasswordContract) {
            throw new UnexpectedValueException('User must implement CanResetPassword interface.');
        }

        return $user;
    }

    /**
     * Create a new password reset token for the given user.
     */
    public function createToken(CanResetPasswordContract $user): string
    {
        return $this->tokens->create($user);
    }

    /**
     * Delete password reset tokens of the given user.
     */
    public function deleteToken(CanResetPasswordContract $user): void
    {
        $this->tokens->delete($user);
    }

    /**
     * Determine if the given password reset token exists and is valid.
     */
    public function tokenExists(CanResetPasswordContract $user, string $token): bool
    {
        return $this->tokens->exists($user, $token);
    }

    /**
     * Determine if the given user recently created a password reset token.
     */
    public function tokenRecentlyCreated(CanResetPasswordContract $user): bool
    {
        return $this->tokens->recentlyCreatedToken($user);
    }

    /**
     * Get the password reset token repository implementation.
     */
    public function getRepository(): TokenRepositoryInterface
    {
        return $this->tokens;
    }
}
