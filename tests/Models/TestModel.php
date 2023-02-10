<?php

namespace Orvital\Extensions\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Orvital\Extensions\Database\Eloquent\Concerns\Sluggable;

class TestModel extends Model
{
    use Sluggable;

    protected $fillable = ['name'];

    public function sluggable(): array
    {
        return [
            'slug' => 'name',
        ];
    }
}
