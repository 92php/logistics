<?php

namespace app\modules\api\modules\g\controllers;

use app\modules\api\extensions\BaseController;
use Yii;

class DefaultController extends BaseController
{

    /**
     * @throws \yii\db\Exception
     */
    public function actionMonitor()
    {
        $db = Yii::$app->getDb();
        $packagesCount = $db->createCommand('SELECT COUNT(*) FROM {{%g_package}}')->queryScalar();
        $noWaybillNumberPackagesCount = $db->createCommand("SELECT COUNT(*) FROM {{%g_package}} WHERE waybill_number IS NULL OR waybill_number = ''")->queryScalar();
        $ordersCount = $db->createCommand('SELECT COUNT(*) FROM {{%g_order}}')->queryScalar();

        return [
            'packages' => $packagesCount,
            'orders' => $ordersCount,
            'noWaybillNumberPackages' => $noWaybillNumberPackagesCount,
            'packageDifferenceValue' => $ordersCount - $packagesCount,
        ];
    }

}