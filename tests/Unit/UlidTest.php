<?php

namespace Orvital\Extensions\Tests\Unit;

use Orvital\Extensions\Support\Uid\Ulid;
use Orvital\Extensions\Tests\TestCase;

class UlidTest extends TestCase
{
    public function test_ulid_creation(): void
    {
        $ulid = new Ulid();

        $this->assertEquals(true, $ulid instanceof Ulid);
    }

    public function test_ulid_get_format_method(): void
    {
        $ulid = new Ulid();

        $this->assertContains($ulid->getFormat(), ['toBinary', 'toBase32', 'toBase58', 'toRfc4122', 'toHex']);
    }
}
