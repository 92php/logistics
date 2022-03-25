<?php

namespace app\modules\api\modules\wuliu\models;

use Yii;

class CompanyLine extends \app\modules\admin\modules\wuliu\models\CompanyLine
{

    public function fields()
    {
        return [
            'id',
            'company_id',
            'country_id',
            'name',
            'estimate_days' => function ($model) {
                $days = $model->estimate_days;
                if ($days == 0) {
                    // 自动计算
                    $db = Yii::$app->getDb();
                    $routes = $db->createCommand("SELECT [[id]] FROM {{%wuliu_company_line_route}} WHERE [[line_id]] = :lineId", [':lineId' => $model->id])->queryAll();
                    $hours = 0;
                    foreach ($routes as $route) {
                        $size = 1000;
                        $pickPackages = $db->createCommand("SELECT [[begin_datetime]], [[end_datetime]] FROM {{%wuliu_package_route}} WHERE [[line_route_id]] = :lineRouteId AND [[status]] IN (1, 2, 3) ORDER BY [[id]] DESC LIMIT $size", [
                            ':lineRouteId' => $route['id'],
                        ])->queryAll();
                        $totalSeconds = 0;
                        foreach ($pickPackages as $pickPackage) {
                            if ($pickPackage['begin_datetime'] && $pickPackage['end_datetime']) {
                                $totalSeconds += $pickPackage['end_datetime'] - $pickPackage['begin_datetime'];
                            }
                        }
                        if ($totalSeconds) {
                            $hours += round($totalSeconds / 3600) / count($pickPackages);
                        }
                    }
                    $hours && $days = round($hours / 24);
                }

                return $days;
            },
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    /**
     * 所属公司
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * 线路模板计费
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplateFee()
    {
        return $this->hasMany(FreightTemplateFee::class, ['line_id' => 'id']);
    }

    public function extraFields()
    {
        return ['company', 'routes', 'templateFee', 'country'];
    }

}