<?php

namespace app\modules\api\modules\hub;

use Yii;

/**
 * hub module definition class
 */
class Module extends \app\modules\api\Module
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\modules\hub\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\api\modules\hub\extensions\Formatter',
            ],
        ]);

        $this->setModule('shopify', [
            'class' => modules\shopify\Module::class
        ]);
    }

}
