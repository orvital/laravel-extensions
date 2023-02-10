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

    /**
     * Boot the trait for the static model.
     *
     * @return void
     */
    public static function bootSluggable()
    {
        static::saving(function (Model $model) {
            $model->fillSlugs();
        });
    }

    public function fillSlugs(): self
    {
        foreach ($this->sluggable() as $key => $column) {
            $this->{$key} = $this->makeSlug($column);
        }

        return $this;
    }

    public function makeSlug(string $column): string
    {
        return Str::slug($this->{$column});
    }
}
