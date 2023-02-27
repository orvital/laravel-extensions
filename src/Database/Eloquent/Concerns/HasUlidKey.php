<?php

namespace Orvital\Extensions\Database\Eloquent\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

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
                $model->{$model->getKeyName()} = $this->formatUlid();
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

    public function generateUlid(): Ulid
    {
        return Str::ulid();
    }

    public function formatUlid(?Ulid $ulid = null): string
    {
        $ulid = $ulid ?? $this->generateUlid();

        return match ($this->ulidFormat()) {
            'toBinary' => $ulid->toBinary(),
            'toBase58' => $ulid->toBase58(),
            'toRfc4122' => $ulid->toRfc4122(),
            'toHex' => $ulid->toHex(),
            default => (string) $ulid,
        };
    }

    /**
     * toBinary  string(16) raw binary                   "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
     * toBase58  string(22) case sensitive.              "1BKocMc5BnrVcuq2ti4Eqm"
     * toBase32  string(26) case insensitive             "01E439TP9XJZ9RPFH3T1PYBCR8"
     * toRfc4122 string(36) case insensitive             "0171069d-593d-97d3-8b3e-23d06de5b308"
     * toHex     string(34) case insensitive, prefixed   "0x0171069d593d97d38b3e23d06de5b308"
     */
    protected function ulidFormat(): string
    {
        return 'toBase32';
    }
}
