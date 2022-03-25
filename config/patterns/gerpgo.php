<?php

return [
    'username' => [
        'label' => '用户名',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 30
    ],
    'password' => [
        'label' => '密码',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 30
    ],
    'cookie' => [
        'label' => 'Cookie',
        'required' => false,
        'valueType' => 'string',
        'type' => 'textarea',
        'min' => 1,
    ],
];