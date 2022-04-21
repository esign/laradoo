<?php

return [
    'api-suffix' => env('ODOO_API_SUFFIX', 'xmlrpc'),     // 'xmlrpc' from version 7.0 and earlier, 'xmlrpc/2' from version 8.0 and above.

    //Credentials
    'host'       => env('ODOO_HOST', 'https://my-host.com'),  // should contain 'http://' or 'https://'
    'db'         => env('ODOO_DB', 'my-db'),
    'username'   => env('ODOO_USERNAME', 'my-username'),
    'password'   => env('ODOO_PASSWORD', 'my-password'),
];