<?php

return [

    /**
     * Request settings
     *
     * Allows to set paths and default namespace that will be used during conversion command
     */

    'request' => [
        'schema_path' => resource_path('schemas'),
        'publish_path' => app_path('Requests'),
        'namespace' => 'App\Requests',
    ],

    /**
     * API serializer
     *
     * Allows to set up default settings used by JMS serializer
     */

    'serializer' => [
        'cache' => env('SERIALIZER_STORAGE_PATH', null),
    ],

];
