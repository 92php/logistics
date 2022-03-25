<?php

namespace app\modules\api\modules\om;

use Yii;

/**
 * om module definition class
 */
class Module extends \app\modules\api\Module
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\modules\om\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\api\modules\om\extensions\Formatter',
            ],
        ]);
    }

}
