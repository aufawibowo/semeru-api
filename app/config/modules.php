<?php

return [
    'vendor' => [
        'namespace' => 'Semeru\Account',
        'className' => Semeru\Account\Module::class,
        'path' => APP_PATH . '/modules/vendor/Module.php',
        'controllerNamespace' => 'Semeru\Account\Presentation\Controllers',
        'routePath' => APP_PATH . '/modules/vendor/Presentation/routes/api.php'
    ],
    'apitu' => [
        'namespace' => 'Semeru\Apitu',
        'className' => Semeru\Apitu\Module::class,
        'path' => APP_PATH . '/modules/apitu/Module.php',
        'controllerNamespace' => 'Semeru\apitu\Presentation\Controllers',
        'routePath' => APP_PATH . '/modules/apitu/Presentation/routes/api.php'
    ]
];
