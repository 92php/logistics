<?php

namespace app\commands;

use app\models\Constant;
use yii\console\ExitCode;

class HelloController extends Controller
{

    public function actionIndex()
    {
        $this->stdout("World (" . time() . ")");
        return ExitCode::OK;
    }

}