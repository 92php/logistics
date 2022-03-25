<?php

namespace app\modules\admin\modules\om;

use Yii;

/**
 * om module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\modules\om\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // custom initialization code goes here
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\admin\modules\om\extensions\Formatter',
            ],
        ]);
    }
}
