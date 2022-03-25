<?php

return [
    'api_key' => [
        'label' => 'API Key',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 100
    ],
    'api_password' => [
        'label' => 'API 密码',
        'required' => true,
        'valueType' => 'string',
        'min' => 10,
        'max' => 100
    ],
    'api_shared_secret' => [
        'label' => 'API Shared Secret',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 100
    ],
    'webhooks_shared_secret' => [
        'label' => 'Webhooks Shared Secret',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 100
    ],
];