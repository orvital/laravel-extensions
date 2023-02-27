<?php

namespace Orvital\Extensions\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Orvital\Extensions\Auth\Concerns\Authenticable;
use Orvital\Extensions\Auth\Contracts\Authenticable as AuthenticableContract;
use Orvital\Extensions\Database\Eloquent\Concerns\HasUlidKey;

class UserUlid extends Model implements AuthenticableContract
{
    use HasUlidKey;
    use Authenticable;

    protected $fillable = ['name'];

    protected function ulidFormat(): string
    {
        return 'toBase58';
    }
}
