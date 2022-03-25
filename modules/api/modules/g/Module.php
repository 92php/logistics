<?php

namespace app\modules\api\modules\g;

use Yii;

/**
 * g module 接口
 *
 * @package app\modules\api\modules\g
 */
class Module extends \app\modules\api\Module
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\modules\g\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\api\modules\g\extensions\Formatter',
            ],
        ]);
    }

}
