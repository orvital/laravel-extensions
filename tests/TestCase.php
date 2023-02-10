<?php

namespace Orvital\Extensions\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
    }

    /**
     * @param  Application  $app
     */
    protected function setUpDatabase(Application $app)
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }
}
