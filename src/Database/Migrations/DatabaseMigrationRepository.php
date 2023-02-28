<?php

namespace Orvital\Extensions\Database\Migrations;

use Illuminate\Database\Migrations\DatabaseMigrationRepository as BaseDatabaseMigrationRepository;
use Illuminate\Support\Facades\Date;
use Orvital\Extensions\Support\Uid\Ulid;

class DatabaseMigrationRepository extends BaseDatabaseMigrationRepository
{
    public function log($file, $batch)
    {
        $this->table()->insert([
            'id' => (string) new Ulid(),
            'migration' => $file,
            'batch' => $batch,
            'created_at' => Date::now(),
        ]);
    }

    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            $table->ulid('id')->primary();
            $table->integer('batch');
            $table->string('migration');
            $table->dateTime('created_at');
        });
    }
}
