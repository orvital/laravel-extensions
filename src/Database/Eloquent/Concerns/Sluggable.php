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
            foreach ($model->sluggable() as $key => $options) {
                $model->{$key} = Str::slug($model->{$options['source']});
            }
        });
    }
}
