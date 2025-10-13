<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    | The default group settings for the elFinder routes.
    |
    */
    'route' => [
        'prefix' => 'translations',
        'middleware' => ['web','auth:admin', 'panel'],
    ],

    /**
     * Enable deletion of translations
     *
     * @type boolean
     */
    'delete_enabled' => false,

    /**
     * Exclude specific groups from Laravel Translation Manager.
     * This is useful if, for example, you want to avoid editing the official Laravel language files.
     *
     * @type array
     *
     *    array(
     *        'pagination',
     *        'reminders',
     *        'validation',
     *    )
     */
    'exclude_groups' => [
        'saysay/az/backup',
        'saysay/az/base',
        'saysay/az/crud',
        'saysay/az/logmanager',
        'saysay/az/permissionmanager',
        'saysay/az/settings',

        'saysay/en/backup',
        'saysay/en/base',
        'saysay/en/crud',
        'saysay/en/logmanager',
        'saysay/en/permissionmanager',
        'saysay/en/settings',

        'saysay/ru/backup',
        'saysay/ru/base',
        'saysay/ru/crud',
        'saysay/ru/logmanager',
        'saysay/ru/permissionmanager',
        'saysay/ru/settings',
    ],

    /**
     * Export translations with keys output alphabetically.
     */
    'sort_keys ' => false,

];
