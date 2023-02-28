<?php

namespace Orvital\Extensions\Support\Uid;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasUlidKey
{
    /**
     * Bootstraper called once on the static model.
     */
    public static function bootHasUlidKey(): void
    {
        static::creating(function (self $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) new Ulid();
            }
        });

        /**
         * Prevent changing the model key manually by always keeping the original value.
         * Done this way instead of using the model $guarded property as it triggers an additional database query.
         */
        static::saving(function (self $model) {
            $originalKey = $model->getOriginal($model->getKeyName());
            if ($originalKey !== $model->getKey()) {
                $model->{$model->getKeyName()} = $originalKey;
            }
        });
    }

    /**
     * On each new model instance, the $keyType and $incrementing properties are set by calling ther respective methods.
     * Setting the properties directly is not possible as traits can't override class properties,
     * and overriding the getKeyType() and getIncrementing() getter methods won't update the underlying class properties.
     */
    public function initializeHasUlidKey(): void
    {
        $this->setKeyType('string')->setIncrementing(false);
    }
}
