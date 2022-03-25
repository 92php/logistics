<?php

namespace app\modules\admin\modules\wuliu\controllers;

/**
 * `wuliu` 子模块
 */
class DefaultController extends Controller
{

    /**
     * 首页
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['package/index']);
    }

}
