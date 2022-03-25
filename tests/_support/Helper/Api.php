<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function seeResponseIsHello()
    {
        \Yii::$app->getDb();
        echo \Yii::$app->getDb()->createCommand("SELECT * FROM {{ABC}}")->rawSql;
//        $response = $this->getModule("REST")->response;
//        $this->assertRegExp('~[event].*~m', $response);
    }

}
