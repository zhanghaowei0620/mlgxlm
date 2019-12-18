<?php

return [

    'common' => [
        'app_key' => 'd383f272-38fa-4d61-9260-fc6369fa61cb', //AppKey
        'e_business_id' => '1609892', //商户ID
        'data_type' => '2', //默认值2 JSON
    ],

    'api' => [

        //即时查询API
        'track' => [
            'url' => 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx',
            'type' => '1002',
        ],

        //物流跟踪API
        'follow' => [
            'url' => 'http://api.kdniao.cc/api/dist',
            'type' => '1008',
        ],


    ],
];