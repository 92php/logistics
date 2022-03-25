<?php

namespace app\modules\api\modules\wuliu;

use Yii;

/**
 * `wuliu` 模块
 *
 * @package app\modules\api\modules\wuliu
 */
class Module extends \app\modules\api\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\api\modules\wuliu\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\api\modules\wuliu\extensions\Formatter',
            ],
        ]);
    }

}
