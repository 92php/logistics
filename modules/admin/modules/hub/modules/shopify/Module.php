<?php

namespace app\modules\admin\modules\hub\modules\shopify;

use app\modules\admin\modules\hub\modules\shopify\controllers\ShopController;

/**
 * shopify module definition class
 */
class Module extends \app\modules\admin\Module
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\modules\hub\modules\shopify\controllers';

    public $controllerMap = [
        'shop' => ShopController::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\admin\modules\hub\modules\shopify\extensions\Formatter',
            ],
        ]);
    }

}
