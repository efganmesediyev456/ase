<?php

return [
    'meta'      => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'       => "AseShop", // set false to total remove
            'description' => 'AseShop', // set false to total remove
            'separator'   => ' - ',
            'keywords'    => [
                'Turkiyeden sifaris',
                'Turkiyeden catdirilma',
                'Amerikadan çatdırılma',
                'Turkiyeden kargo',
                'Amerikadan kargo',
                'Turkiyeden alver',
                'Turkiyeden derman sifarisi',
                'Turkiyeden azerbaycana catdirilma',
                'Amerikadan azerbaycana kargo',
                'Turkiyeden azerbaycana kitab sifarisi',
                'Trendyoldan bakiya sifaris',
                'En serfeli kargo sirketi',
                'Zaradan bakiya sifaris',
            ],
            'canonical'   => false, // Set null for using Url::current(), set false to total remove
        ],

        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
        ],
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title'       => 'AseShop', // set false to total remove
            'description' => 'AseShop', // set false to total remove
            'url'         => false, // Set null for using Url::current(), set false to total remove
            'type'        => false,
            'site_name'   => false,
            'images'      => [],
        ],
    ],
    'twitter'   => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            //'card'        => 'summary',
            //'site'        => '@LuizVinicius73',
        ],
    ],
];
