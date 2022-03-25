<?php

namespace app\modules\admin\modules\om\controllers;

/**
 * Default controller for the `om` module
 */
class DefaultController extends Controller
{

    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
