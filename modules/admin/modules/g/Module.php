<?php

namespace app\modules\admin\modules\g;

use Yii;

/**
 * `g` 子模块
 */
class Module extends \app\modules\admin\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\modules\g\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\admin\modules\g\extensions\Formatter',
            ],
        ]);
    }

}
