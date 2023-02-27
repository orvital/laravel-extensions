<?php

namespace Orvital\Extensions\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Orvital\Extensions\Auth\Concerns\Authenticable;
use Orvital\Extensions\Auth\Contracts\Authenticable as AuthenticableContract;

class User extends Model implements AuthenticableContract
{
    use Authenticable;

    protected $fillable = ['name'];
}
