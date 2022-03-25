<?php
/**
 * 收集字段类型设置
 */
return [
    // 店小秘
    1 => [
        'dianxiaomi' => require(__DIR__ . '/patterns/dianxiaomi.php')
    ],
    // 通途
    2 => [
        'tongtool' => require(__DIR__ . '/patterns/tongtool.php'),
    ],
    // 积加
    3 => [
        'gerpgo' => require(__DIR__ . '/patterns/gerpgo.php')
    ],
    // Shopify
    4 => [
        'shopify' => require(__DIR__ . '/patterns/shopify.php')
    ],
];
