<?php

namespace Orvital\Extensions\Auth\Concerns;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

trait Authenticable
{
    use Notifiable; // required by MustVerifyEmail and CanResetPassword
    use Authorizable;
    use Authenticatable;
    use MustVerifyEmail;
    use CanResetPassword;

    public const VERIFIED_COLUMN = 'verified_at';

    /**
     * Initializer called on each new model instance.
     */
    public function initializeAuthenticable()
    {
        $this->mergeFillable(['email', 'password']);
        $this->makeHidden(['password', 'remember_token']);
        $this->mergeCasts([self::VERIFIED_COLUMN => 'datetime']);
    }

    /**
     * Interact with the user's password.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value,
            set: fn (string $value) => Hash::needsRehash($value) ? Hash::make($value) : $value,
        );
    }

    public function checkAuthPassword(string $value): bool
    {
        return Hash::check($value, $this->password);
    }

    public function setAuthPassword($value): void
    {
        $this->password = $value;
    }

    public function hasVerifiedEmail()
    {
        return ! is_null($this->{self::VERIFIED_COLUMN});
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            self::VERIFIED_COLUMN => $this->freshTimestamp(),
        ])->save();
    }
}
