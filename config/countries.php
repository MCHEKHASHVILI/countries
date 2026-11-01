<?php

return [

    /* The table, and the connection it lives on (null: the default connection). */
    'table' => 'countries',
    'connection' => env('COUNTRIES_DB_CONNECTION'),

    /* Run the package migration with yours. Set false and publish it to change it first. */
    'run_migrations' => true,

    /* Locale used when a name is missing in the app's locale. */
    'fallback_locale' => 'en',

];
