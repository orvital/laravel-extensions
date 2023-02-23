<?php

namespace Orvital\Extensions\Auth\Contracts;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;

interface Authenticable extends Authenticatable, Authorizable, CanResetPassword
{
    /**
     * Set the user password.
     */
    public function setAuthPassword(string $value): void;

    /**
     * Check if the user password matches the provided value.
     */
    public function checkAuthPassword(string $value): bool;
}
