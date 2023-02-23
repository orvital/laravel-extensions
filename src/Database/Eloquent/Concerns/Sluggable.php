<?php

namespace Orvital\Extensions\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Sluggable
{
    /**
     * Return the sluggable configuration array for this model.
     */
    abstract public function sluggable(): array;

    // public static function sluggableOptions(): array
    // {
    //     return [
    //         'separator' => '-',
    //         'language' => 'en',
    //         'dictionary' => ['@' => 'at'],
    //     ];
    // }

    /**
     * Boot the trait for the static model.
     *
     * @return void
     */
    public static function bootSluggable()
    {
        static::saving(function (Model $model) {
            $model->slugify();
        });
    }

    public function slugify(): self
    {
        foreach ($this->sluggable() as $key => $column) {
            $this->{$key} = $this->makeSlug($this->{$column});
        }

        return $this;
    }

    public static function makeSlug(string $value): string
    {
        return Str::slug($value);
    }
}
