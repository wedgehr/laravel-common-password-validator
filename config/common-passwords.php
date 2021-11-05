<?php

return [

    /**
     * Table name of the Common Passwords Table
     * @var string
     */
    'table' => 'common_passwords',

    /**
     * Minimum password length to validate or store
     * @var int
     */
    'minlength' => 8,

    /**
     * Maximum number of common passwords to store
     * @var int
     */
    'count' => 10000,

    /**
     * A list of urls of common password text files which we need to fetch and load
     * @var array
     *
     * ref: https://github.com/danielmiessler/SecLists/tree/master/Passwords/Common-Credentials
     */
    'urls' => [

        /**
         * top 100000 password dictionary
         *
         * We use top 100,000 so we can apply minlength filtering
         * and still meet our desired count of passwords
         */
        'https://raw.githubusercontent.com/danielmiessler/SecLists/5e1dc9cc79aac54b373349e2a97bbb22f1b63bb3/Passwords/Common-Credentials/10-million-password-list-top-100000.txt',

    ],
];
