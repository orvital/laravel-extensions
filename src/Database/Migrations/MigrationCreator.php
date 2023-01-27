<?php

namespace Orvital\Extensions\Database\Migrations;

use Illuminate\Database\Migrations\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    protected function getPath($name, $path)
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }
}
