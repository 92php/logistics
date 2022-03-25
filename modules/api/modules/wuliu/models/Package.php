<?php

namespace app\modules\api\modules\wuliu\models;

use app\modules\api\models\Constant;
use Yii;

class Package extends \app\modules\api\modules\g\models\Package
{

    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'routes', 'line'
        ]);
    }

    /**
     * 快递线路
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLine()
    {
        return $this->hasOne(CompanyLine::class, ['id' => 'logistics_line_id']);
    }

    /**
     * 路由
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function getRoutes()
    {
        $routes = [];
        $db = Yii::$app->getDb();
        $lineRoutes = $db->createCommand("SELECT [[id]], [[step]], [[event]], [[estimate_days]] FROM {{%wuliu_company_line_route}} WHERE [[line_id]] = :lineId AND [[enabled]] = :enabled AND [[estimate_days]] >= 0 ORDER BY [[step]] ASC", [
            ':lineId' => $this->logistics_line_id,
            ':enabled' => Constant::BOOLEAN_TRUE,
        ])->queryAll();
        if ($lineRoutes) {
            $packageRoutes = [];
            $rawPackageRoutes = $db->createCommand("SELECT [[id]], [[line_route_id]], [[begin_datetime]], [[plan_datetime]], [[end_datetime]], [[status]], [[process_status]], [[process_member_id]], [[process_datetime]], [[remark]] FROM {{%wuliu_package_route}} WHERE [[package_id]] = :packageId", [':packageId' => $this->id])->queryAll();
            foreach ($rawPackageRoutes as $packageRoute) {
                $packageRoutes[$packageRoute['line_route_id']] = $packageRoute;
            }
            foreach ($lineRoutes as $route) {
                $id = 0;
                $beginDatetime = $planDatetime = $endDatetime = null;
                $status = PackageRoute::STATUS_UNKNOWN;
                $processStatus = PackageRoute::PROCESS_STATUS_NOTHING;
                $processMemberId = 0;
                $processDatetime = null;
                $remark = '';
                if (isset($packageRoutes[$route['id']])) {
                    $packageRoute = $packageRoutes[$route['id']];
                    $id = $packageRoute['id'];
                    $beginDatetime = $packageRoute['begin_datetime'] ? (int) $packageRoute['begin_datetime'] : null;
                    $planDatetime = $packageRoute['plan_datetime'] ? (int) $packageRoute['plan_datetime'] : null;
                    $endDatetime = $packageRoute['end_datetime'] ? (int) $packageRoute['end_datetime'] : null;
                    $status = (int) $packageRoute['status'];
                    $processStatus = (int) $packageRoute['process_status'];
                    $processMemberId = (int) $packageRoute['process_member_id'];
                    $processDatetime = $packageRoute['process_datetime'] ? (int) $packageRoute['process_datetime'] : null;
                    $remark = $packageRoute['remark'];
                }
                $routes[] = [
                    'id' => $id,
                    'step' => (int) $route['step'],
                    'event' => $route['event'],
                    'begin_datetime' => $beginDatetime,
                    'plan_datetime' => $planDatetime,
                    'end_datetime' => $endDatetime,
                    'status' => $status,
                    'process_status' => $processStatus,
                    'process_member_id' => $processMemberId,
                    'process_datetime' => $processDatetime,
                    'remark' => $remark,
                ];
            }
        }
        if ($routes) {
            array_unshift($routes, [
                'step' => 0,
                'event' => '发货',
                'begin_datetime' => $this->delivery_datetime,
                'plan_datetime' => $this->delivery_datetime,
                'end_datetime' => $this->delivery_datetime,
                'status' => PackageRoute::STATUS_NORMAL,
                'process_status' => PackageRoute::PROCESS_STATUS_NOTHING,
                'process_member_id' => 0,
                'process_datetime' => null,
                'remark' => '',
            ]);
        }
        // @todo Will remove if in product mode.
        if (Yii::$app->getRequest()->get('debug')) {
            foreach ($routes as $i => $route) {
                foreach (['begin_datetime', 'plan_datetime', 'end_datetime'] as $name) {
                    $routes[$i]["{$name}_formatted"] = $route[$name] ? date('Y-m-d H:i:s', $route[$name]) : null;
                }
            }
        }

        return $routes;
    }

}