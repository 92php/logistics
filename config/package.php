<?php

return [
    /**
     * 平台包裹状态和系统自定义包裹状态映射关系设定（key => value）
     * 处于 value 第三方平台状态的则自动转换为 key 值
     *
     * 格式
     * platformId => [
     *     SystemPackageStatus => [platformPackageStatus1, platformPackageStatus2, ...]
     * ]
     */
    'statusMap' => [
        // 店小秘
        1 => [
            /**
             * @see \app\models\Option::thirdPartyPlatformDxmPackageStatusOptions()
             */
            0 => [], // 待处理 => []
            1 => [6], // 已接单 => [待打单（有货）]
            2 => [], // 运输途中 => []
            3 => [], // 到达待取 => []
            4 => [12], // 成功签收 => [已完成]
            5 => [], // 查询不到 => []
            6 => [], // 运输过久 => []
            7 => [], // 可能异常 => []
            8 => [], // 投递失败 => []
        ]
    ]
];