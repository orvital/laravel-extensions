<?php

namespace Orvital\Extensions\Tests\Unit;

use Orvital\Extensions\Tests\Models\TestModel;
use Orvital\Extensions\Tests\TestCase;

class SluggableTest extends TestCase
{
    public function test_slug_attributes_are_set_when_creating_model(): void
    {
        $model = TestModel::create(['name' => 'John Doe']);

        $this->assertEquals('john-doe', $model->slug);
    }

    public function test_slug_attributes_are_set_when_filling_model(): void
    {
        $model = TestModel::make(['name' => 'John Doe']);

        $model->fillSlugs();

        $this->assertEquals('john-doe', $model->slug);
    }
}
