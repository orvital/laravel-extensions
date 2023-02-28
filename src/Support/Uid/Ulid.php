<?php

namespace Orvital\Extensions\Support\Uid;

class Ulid extends \Symfony\Component\Uid\Ulid
{
    public function getFormat(): string
    {
        $format = config('extensions.ulid.format');

        return in_array($format, ['toBinary', 'toBase32', 'toBase58', 'toRfc4122', 'toHex'])
            ? $format
            : 'toBase32';
    }

    public function length(): int
    {
        return match ($this->getFormat()) {
            'toBinary' => 16,
            'toBase32' => 26,
            'toBase58' => 22,
            'toRfc4122' => 36,
            'toHex' => 34,
        };
    }

    public function toString(): string
    {
        $format = $this->getFormat();

        if (config('extensions.ulid.lowercase') && in_array($format, ['toBase32', 'toRfc4122', 'toHex'])) {
            return strtolower($this->{$format}());
        }

        return $this->{$format}();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
