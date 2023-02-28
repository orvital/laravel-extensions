<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Groups Attributes
    |--------------------------------------------------------------------------
    |
    | The middleware assigned to the routes.
    | The authentication guard that should be used while authenticating users.
    |
    */

    'schema' => [
        'default_string_length' => 192,
    ],

    /**
     * toBinary  string(16) raw binary                   "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
     * toBase58  string(22) case sensitive.              "1BKocMc5BnrVcuq2ti4Eqm"
     * toBase32  string(26) case insensitive             "01E439TP9XJZ9RPFH3T1PYBCR8"
     * toRfc4122 string(36) case insensitive             "0171069d-593d-97d3-8b3e-23d06de5b308"
     * toHex     string(34) case insensitive, prefixed   "0x0171069d593d97d38b3e23d06de5b308"
     */
    'ulid' => [
        'format' => 'toBase32',
        'lowercase' => false, // applies only on toBase32, toRfc4122 and toHex
    ],

];
