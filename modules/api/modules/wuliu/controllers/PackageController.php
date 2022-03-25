<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\extensions\data\ArrayDataProvider;
use app\modules\api\modules\wuliu\extensions\Formatter;
use app\modules\api\modules\wuliu\forms\PackageBatchDeliveryForm;
use app\modules\api\modules\wuliu\forms\PackageDeliveryForm;
use app\modules\api\modules\wuliu\forms\PackageWeighDeliveryForm;
use app\modules\api\modules\wuliu\models\Package;
use app\modules\api\modules\wuliu\models\PackageSearch;
use DateTime;
use PHPExcel;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Color;
use PHPExcel_Writer_Exception;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;

/**
 * 包裹接口
 *
 * @package app\modules\api\modules\wuliu\controllers
 */
class PackageController extends Controller
{

    public $modelClass = Package::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delivery' => ['POST'],
                    'weight-delivery' => ['POST'],
                    'batch-delivery' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'delivery', 'batch-delivery', 'status-options', 'delivery-statistics', 'to-excel', 'weight-delivery', 'not-match-delivery'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return \yii\data\ActiveDataProvider
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        $search = new PackageSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 单个自动发货
     *
     * @return Package
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDelivery()
    {
        $model = new PackageDeliveryForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        return $model->save();
    }

    /**
     * 批量发货
     *
     * @throws \yii\base\InvalidConfigException
     * @throws Exception
     */
    public function actionBatchDelivery()
    {
        $model = new PackageBatchDeliveryForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->validate();

        return $model->save();
    }

    /**
     * 称重发货
     *
     * @return Package
     * @throws \yii\base\InvalidConfigException
     */
    public function actionWeightDelivery()
    {
        $model = new PackageWeighDeliveryForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        return $model->save();
    }

    /**
     * 包裹状态选项以及统计
     *
     * @return array
     * @throws Exception
     */
    public function actionStatusOptions()
    {
        $options = [];
        foreach (Package::statusOptions() as $key => $value) {
            if ($key == Package::STATUS_PENDING) {
                continue;
            }
            $options[$key] = [
                'key' => $key,
                'name' => $value,
                'count' => 0,
            ];
        }
        $n = 0;
        if ($options) {
            $packages = Yii::$app->getDb()->createCommand("SELECT [[status]], COUNT(*) AS [[count]] FROM {{%g_package}} WHERE [[status]] IN (" . implode(', ', array_keys($options)) . ")  GROUP BY [[status]]")->queryAll();
            foreach ($packages as $package) {
                if (isset($options[$package['status']])) {
                    $n += $package['count'];
                    $options[$package['status']]['count'] = $package['count'];
                }
            }
        }

        array_unshift($options, [
            'key' => 100,
            'name' => '全部',
            'count' => $n
        ]);

        return array_values($options);
    }

    /**
     * 发货统计
     *
     * @param null $beginDate
     * @param null $endDate
     * @return array
     * @throws Exception
     */
    public function actionDeliveryStatistics($beginDate = null, $endDate = null)
    {
        $items = [];
        $db = Yii::$app->getDb();
        try {
            $beginDate = (new DateTime($beginDate))->setTime(0, 0, 0);
            $endDate = (new DateTime($endDate))->setTime(23, 59, 59);
        } catch (\Exception $e) {
            $beginDate = (new DateTime())->setTime(0, 0, 0);
            $endDate = (new DateTime())->setTime(23, 59, 59);
        }
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        $rawItems = $db->createCommand("SELECT [[third_party_platform_id]], COUNT(*) AS [[count]] FROM {{%g_package}} WHERE [[delivery_datetime]] BETWEEN :begin AND :end GROUP BY [[third_party_platform_id]]", [
            ':begin' => $beginDate->getTimestamp(),
            ':end' => $endDate->getTimestamp(),
        ])->queryAll();
        foreach ($rawItems as $item) {
            $name = null;
            $platformId = $item['third_party_platform_id'];
            $name = $platformId ? $formatter->asPlatform($platformId) : '其他';
            if (!isset($items[$platformId])) {
                $items[$platformId] = [
                    'platform_id' => $platformId,
                    'platform_name' => $name,
                    'count' => 0,
                ];
            }
            $items[$platformId]['count'] += $item['count'];
        }

        return array_values($items);
    }

    /**
     * 导出为 Excel
     *
     * @param null $country_id 国家id
     * @param null $package_number 包裹号
     * @param null $order_number 订单号
     * @param null $waybill_number 运单号
     * @param null $shop_name 店铺名
     * @param null $delivery_begin_datetime 发货开始日期
     * @param null $delivery_end_datetime 发货结束日期
     * @param null $line_id 线路
     * @param null $organization_id 团队
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws \Exception
     */
    public function actionToExcel($country_id = null, $package_number = null, $order_number = null, $waybill_number = null, $shop_name = null, $delivery_begin_datetime = null, $delivery_end_datetime = null, $line_id = null, $organization_id = null)
    {
        $sql = "SELECT [[o.number]] AS [[order_number]], [[s.name]] AS [[shop_name]], p.*, [[cl.name]] AS [[line_name]], [[cl.name_prefix]] AS [[company_name]] FROM {{%g_package}} [[p]]
LEFT JOIN {{%wuliu_company_line}} cl ON [[cl.id]] = [[p.logistics_line_id]] 
INNER JOIN {{%g_package_order_item}} poi ON [[poi.package_id]] = [[p.id]]
INNER JOIN {{%g_order}} o ON [[o.id]] = [[poi.order_id]] 
LEFT JOIN {{%g_shop}} s ON [[o.shop_id]] = [[s.id]]";

        $conditions = [];
        $params = [];
        if ($country_id) {
            $conditions[] = "[[p.country_id]] = :countryId";
            $params[':countryId'] = $country_id;
        }
        if ($package_number) {
            $conditions[] = "[[p.number]] = :packageNumber";
            $params[':packageNumber'] = $package_number;
        }
        if ($order_number) {
            $conditions[] = "[[o.number]] = :orderNumber";
            $params[':orderNumber'] = $order_number;
        }
        if ($waybill_number) {
            $conditions[] = "[[p.waybill_number]] = :waybillNumber";
            $params[':waybillNumber'] = $waybill_number;
        }
        if ($shop_name) {
            $conditions[] = "[[s.name]] = :shopName";
            $params[':shopName'] = $shop_name;
        }
        if ($organization_id) {
            $conditions[] = "[[s.organization_id]] = :organizationId";
            $params[':organizationId'] = $organization_id;
        }

        $conditions[] = '[[p.delivery_datetime]] BETWEEN :begin AND :end';
        if ($delivery_begin_datetime &&
            $delivery_end_datetime &&
            IsHelper::datetime($delivery_begin_datetime) &&
            IsHelper::datetime($delivery_end_datetime)) {
            $params[':begin'] = (new DateTime($delivery_begin_datetime . ' 0:0:0'))->getTimestamp();
            $params[':end'] = (new DateTime($delivery_end_datetime . ' 23:59:59'))->getTimestamp();
        } else {
            $ymd = date('Y-m-d');
            $params[':begin'] = (new DateTime($ymd . ' 0:0:0'))->getTimestamp();
            $params[':end'] = (new DateTime($ymd . ' 23:59:59'))->getTimestamp();
        }
        if ($line_id) {
            $conditions[] = "[[p.logistics_line_id]] = :lineId";
            $params[':lineId'] = $line_id;
        }
        $conditions && $sql .= ' WHERE' . implode(' AND ', $conditions);
        $items = Yii::$app->db->createCommand($sql, $params)->queryAll();
        $packages = [];
        foreach ($items as $item) {
            if (!isset($packages[$item['number']])) {
                $packages[$item['number']] = $item;
            }
        }

        $phpExcel = new PHPExcel();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getProperties()->setCreator("Microsoft")
            ->setLastModifiedBy("Microsoft")
            ->setTitle("Office 2007 XLSX Finance Document")
            ->setSubject("Office 2007 XLSX Finance Document")
            ->setDescription("Finance document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("data");
        $phpExcel->setActiveSheetIndex(0);
        $activeSheet = $phpExcel->getActiveSheet();
        $phpExcel->getDefaultStyle()
            ->getFont()->setSize(14);

        $activeSheet->getDefaultRowDimension()->setRowHeight(25);
        $cols = ['A' => 20, 'B' => 20, 'C' => 20, 'D' => 10, 'E' => 10, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 10, 'J' => 50];
        foreach ($cols as $col => $width) {
            $activeSheet->getColumnDimension($col)->setWidth($width);
        }
        $activeSheet->getStyle('A1:J1')->getFont()->setBold(true);
        $activeSheet->setCellValue("A1", '包裹号')
            ->setCellValue("B1", '订单号')
            ->setCellValue("C1", '运单号')
            ->setCellValue("D1", '重量')
            ->setCellValue("E1", '运费')
            ->setCellValue("F1", '店铺信息')
            ->setCellValue("G1", '发货日期')
            ->setCellValue("H1", '发货时间')
            ->setCellValue("I1", '物流状态')
            ->setCellValue("J1", '物流负责商');
        $row = 2;
        $formatter = new Formatter();
        foreach ($packages as $item) {
            if ($item['delivery_datetime']) {
                $ymd = date('Y-m-d', $item['delivery_datetime']);
                $time = date('H:i', $item['delivery_datetime']);
            } else {
                $ymd = '暂无发货时间';
                $time = "暂无发货时间";
            }
            $activeSheet->setCellValue("A{$row}", $item['number'])
                ->setCellValue("B{$row}", $item['order_number'])
                ->setCellValue("C{$row}", $item['waybill_number'])
                ->setCellValue("D{$row}", $item['weight'])
                ->setCellValue("E{$row}", $item['freight_cost'])
                ->setCellValue("F{$row}", $item['shop_name'])
                ->setCellValue("G{$row}", $ymd)
                ->setCellValue("H{$row}", $time)
                ->setCellValue("I{$row}", $formatter->asPackageStatus($item['status']))
                ->setCellValue("J{$row}", $item['line_name'] . $item['company_name']);
            if (in_array($item['status'], [
                Package::STATUS_NOT_FOUND,
                Package::STATUS_DEFERRED,
                Package::STATUS_MAYBE_ABNORMAL,
                Package::STATUS_DELIVERY_FAILURE,
            ])) {
                $activeSheet->getStyle("I{$row}")->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            $row++;
        }
        $phpExcel->getActiveSheet()->setTitle('包裹数据');
        $phpExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $filename = 'Package_' . time() . rand(0, 999) . '.xlsx';
        $file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . urlencode($filename);
        $objWriter->save($file);
        Yii::$app->getResponse()->sendFile($file, $filename, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * 获取已发货系统未匹配数据
     *
     * @param null $waybill_number 运单号
     * @param null $delivery_begin_datetime 发货开始时间
     * @param null $delivery_end_datetime 发货结束时间
     * @param int $page
     * @param int $pageSize
     * @return ArrayDataProvider
     * @throws \Exception
     */
    public function actionNotMatchDelivery($waybill_number = null, $delivery_begin_datetime = null, $delivery_end_datetime = null, $page = 1, $pageSize = 20)
    {
        $query = (new Query())->from("{{%wuliu_package_not_match}}");

        if ($waybill_number) {
            $query->andWhere(['number' => $waybill_number]);
        }

        if ($delivery_begin_datetime &&
            $delivery_end_datetime &&
            IsHelper::datetime($delivery_begin_datetime) &&
            IsHelper::datetime($delivery_end_datetime)) {
            $query->andWhere([
                'BETWEEN', 'scan_at',
                (new DateTime($delivery_begin_datetime . ' 0:0:0'))->getTimestamp(),
                (new DateTime($delivery_end_datetime . ' 23:59:59'))->getTimestamp()
            ]);
        }

        return new ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination' => [
                'page' => (int) $page - 1,
                'pageSize' => (int) $pageSize ?: 20,
            ]
        ]);
    }

}
