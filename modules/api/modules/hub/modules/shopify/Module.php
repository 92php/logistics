<?php

namespace app\modules\api\modules\hub\modules\shopify;


use app\modules\api\modules\hub\modules\shopify\controllers\ShopController;

/**
 * `api/hub/shopify` Module
 *
 * @package app\modules\api\modules\hub\modules\amazon
 */
class Module extends \app\modules\api\modules\hub\Module
{

    public $controllerNamespace = 'app\modules\api\modules\hub\modules\shopify\controllers';

    public $controllerMap = [
        'shop' => ShopController::class,
    ];


}