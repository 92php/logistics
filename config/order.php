<?php

return [
    /**
     * 平台订单状态和系统自定义订单状态映射关系设定（key => value）
     * 处于 value 第三方平台状态的则自动转换为 key 值
     *
     * 格式
     * platformId => [
     *     SystemOrderStatus => [platformOrderStatus1, platformOrderStatus2, ...]
     * ]
     */
    'statusMap' => [
        // 店小秘
        1 => [
            /**
             * @see \app\models\Option::thirdPartyPlatformDxmOrderStatusOptions()
             */
            0 => [0, 2, 3], // 待处理 => [未知, 风控中, 待审核]
            1 => [1, 11], // 无效 => [未付款, 已忽略]
            2 => [], // 生产中 => []
            3 => [4, 5, 6, 7, 8, 9], // 处理中 => [待处理, 已处理, 待打单（有货）, 待打单（缺货）,待打单（有异常）,已交运]
            4 => [10], // 失败 => [已退款]
            5 => [12], // 完成 => [已完成]
        ],
        // 积加 ERP
        3 => [
            /**
             * @see \app\models\Option::thirdPartyPlatformGerpgoOrderStatusOptions()
             */
            0 => [0, 6], // 待处理 => [无状态, 预订订单]
            1 => [1], // 无效 => [未付款]
            2 => [], // 生产中 => []
            3 => [2, 3, 7], // 处理中 => [未发货, 拣货中, 部分发货]
            4 => [5], // 失败 => [已取消]
            5 => [4], // 完成 => [已发货]
        ],

        // shopify
        4 => [
            /**
             * @see \app\models\Option::thirdPartyPlatformShopifyOrderStatusOptions()
             */
            0 => [0, 1, 4], // 待处理 => [无状态]
            1 => [8], // 无效 => [无效]
            2 => [2], // 生产中 => [已付款]
            3 => [3,5], // 处理中 => [部分退款,待定]
            4 => [6], // 失败 => [已退款]
            5 => [], // 完成 => [已发货]
        ]

    ]
];