<?php

return [
    'adminEmail' => 'admin@example.com',
    // 系统用户
    'user' => [
        'fakeMember' => 'tmp', // 后台发起 api 请求模拟的用户
    ],
    'fromMailAddress' => [
        'admin@example.com' => 'you name',
    ],
    'api' => require(__DIR__ . '/api.php'),  // API 设置
    'business' => require(__DIR__ . '/business.php'), // 业务逻辑设置
    'identity' => require(__DIR__ . '/identity.php'),  // 认证处理设置
    'member' => require(__DIR__ . '/member.php'),  // 会员设置
    'modules' => require(__DIR__ . '/modules.php'),  // 模块设置
    'private' => require(__DIR__ . '/private.php'), // 私有设置
    'rbac' => require(__DIR__ . '/rbac.php'),  // 权限认证设置
    'sms' => require(__DIR__ . '/sms.php'), // 短信设置
    'upload' => require(__DIR__ . '/upload.php'),  // 上传设置
    'wechat' => require(__DIR__ . '/wechat.php'),  // 微信公众号设置
    'trackingmore' => require(__DIR__ . '/trackingmore.php'),  // trackingmore.com 物流查询设置
    'sf' => require(__DIR__ . '/sf.php'),  // 顺风快递设置
    'yundama' => require(__DIR__ . '/yundama.php'),  // 云打码设置
    'platform' => require(__DIR__ . '/platform.php'),  // 第三方平台设置
    'order' => require(__DIR__ . '/order.php'),  // 订单设置
    'orderItem' => require(__DIR__ . '/orderItem.php'),  // 订单详情设置
    'package' => require(__DIR__ . '/package.php'),  // 包裹设置
];
