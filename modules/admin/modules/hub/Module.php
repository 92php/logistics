<?php

namespace app\modules\admin\modules\hub;

use Yii;

/**
 * `hub` 子模块
 *

 */
class Module extends \app\modules\admin\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\modules\hub\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'app\modules\admin\modules\hub\extensions\Formatter',
            ],
        ]);
        
        $this->setModule('shopify', [
            'class' => modules\shopify\Module::class,
            'layout' => '@app/modules/admin/views/layouts/main.php',
        ]);

    }

}
