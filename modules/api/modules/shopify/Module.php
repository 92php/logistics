<?php

namespace app\modules\api\modules\shopify;

/**
 * Shopify Module
 *
 * @package app\modules\api\modules\shopify
 */
class Module extends \app\modules\api\Module
{

    public $controllerNamespace = 'app\modules\api\modules\shopify\controllers';

    public function init()
    {
        parent::init();
        $this->setModule('webhook', [
            'class' => 'app\modules\api\modules\shopify\modules\webhook\Module',
        ]);
    }

}