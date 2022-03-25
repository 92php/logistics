<?php

/**
 * 燕文配置
 */
return [
    'name' => '燕文',
    'version' => 'v1.4-20200327',
    'identity' => [
        'userId' => '100000',
        'token' => 'D6140AA383FD8515B09028C586493DDB',
    ],
    'endpoint' => [
        'dev' => 'http://47.96.220.163:802/service/Users/{userId}',
        'prod' => 'http://online.yw56.com.cn/service/Users/{userId}'
    ],
    'actions' => [
        'channels' => 'GET: /GetChannels',
    ],
    'validators' => [
        // 全局验证
        'global' => [
            'number' => [
                'label' => '运单号',
                'rules' => [
                    ['notBlank'],
                    ['in', 'range' => [1, 2]],
                    ['string', 'min' => 1, 'max' => 100],
                    ['default', 'value' => 1],
                ],
            ],
        ],
        // createOrder 方法
        'create-order' => [
            'items' => [
                'label' => '订单商品列表',
                'rules' => [
                    ['notBlank'],
                    ['type', 'array'],
                    ['count', 'min' => 1],
                ],
            ]
        ]
    ],
];
