<?php

namespace Orvital\Extensions\Session;

use Illuminate\Session\Store as BaseStore;
use Orvital\Extensions\Support\Uid\Ulid;

class Store extends BaseStore
{
    /**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    public function isValidId($id)
    {
        return is_string($id) ? Ulid::isValid($id) : false;
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return (string) new Ulid();
    }
}
