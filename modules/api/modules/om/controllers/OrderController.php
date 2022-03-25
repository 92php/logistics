<?php

namespace app\modules\api\modules\om\controllers;

use app\models\Constant;
use app\modules\api\modules\g\models\Shop;
use app\modules\api\modules\om\models\Order;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\OrderSearch;
use DateTime;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet_Drawing;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * /api/om/order
 * 订单接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class OrderController extends Controller
{

    public $modelClass = Order::class;

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        unset($actions['delete']);

        return $actions;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['DELETE'],
                    'update' => ['PUT', 'PATCH'],
                    '*' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'status-options', 'to-excel'],
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
        $search = new OrderSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 商品状态选项以及统计
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionStatusOptions()
    {
        $options = [
            'payment' => [
                'key' => 1,
                'name' => '已付款',
                'count' => 0,
            ],
            'PlaceOrder' => [
                'key' => 2,
                'name' => '待下单',
                'count' => 0,
            ],
            'waitReceiving' => [
                'key' => 3,
                'name' => '等待供应商接单',
                'count' => 0,
            ],
            'placeOrderSuccess' => [
                'key' => 4,
                'name' => '下单成功',
                'count' => 0,
            ],
            'placeOrderFail' => [
                'key' => 5,
                'name' => '下单失败',
                'count' => 0,
            ],
            'InProduction' => [
                'key' => 6,
                'name' => '投入生产',
                'count' => 0,
            ],
            'CancelSuccess' => [
                'key' => 7,
                'name' => '取消订单',
                'count' => 0,
            ],
            'deliver' => [
                'key' => 8,
                'name' => '供应商已发货',
                'count' => 0,
            ],
            'complete' => [
                'key' => 9,
                'name' => '已完成',
                'count' => 0,
            ],

        ];
        $n = 0;
        if ($options) {
            $db = Yii::$app->getDb();
            $products = $db->createCommand("SELECT [[bi.status]], COUNT(bi.order_item_id) AS total, [[r.current_node]] FROM {{%om_order_item_business}} bi LEFT JOIN {{%om_order_item_route}} r ON r.order_item_id = bi.order_item_id LEFT JOIN {{%g_order_item}} oi ON oi.id = bi.order_item_id LEFT JOIN {{%g_order}} o ON o.id = oi.order_id WHERE [[o.platform_id]] = :platformId AND [[oi.ignored]] = :ignored AND [[o.product_type]] = :productType AND [[o.payment_at]] > 1590767999 GROUP BY [[bi.status]], [[r.current_node]]", [':platformId' => Constant::PLATFORM_SHOPIFY, ":ignored" => Constant::BOOLEAN_FALSE, ':productType' => Shop::PRODUCT_TYPE_CUSTOMIZED])->queryAll();
            // 获取取消总数
            $cancelCount = $db->createCommand("SELECT COUNT(*) FROM {{%om_order_item_route_cancel_log}}")->queryScalar();
            if ($cancelCount) {
                $options['CancelSuccess']['count'] = $cancelCount;
            }
            foreach ($products as $product) {
                if ($product['status'] == OrderItemBusiness::STATUS_STAY_CHECK && $product['current_node'] == null) {
                    // 付款订单
                    $options['payment']['count'] = $product['total'];
                    $n += $product['total'];
                }
                if ($product['status'] == OrderItemBusiness::STATUS_STAY_PLACE_ORDER && $product['current_node'] == null) {
                    // 待下单
                    $options['PlaceOrder']['count'] = $product['total'];
                    $n += $product['total'];
                }
                if ($product['current_node'] == OrderItemRoute::NODE_STAY_RECEIPT && $product['status'] == OrderItemBusiness::STATUS_IN_HANDLE) {
                    // 待接单
                    $options['waitReceiving']['count'] = $product['total'];
                    $n += $product['total'];
                }
                if ($product['current_node'] == OrderItemRoute::NODE_TO_PRODUCED && $product['status'] == OrderItemBusiness::STATUS_IN_HANDLE) {
                    // 已接单
                    $options['placeOrderSuccess']['count'] = $product['total'];
                    $n += $product['total'];
                }
                if ($product['status'] == OrderItemBusiness::STATUS_STAY_REJECT) {
                    // 拒接
                    $options['placeOrderFail']['count'] = $product['total'];
                }
                if ($product['current_node'] == OrderItemRoute::NODE_ALREADY_PRODUCED && $product['status'] == OrderItemBusiness::STATUS_IN_HANDLE) {
                    // 生产中
                    $options['InProduction']['count'] = $product['total'];
                    $n += $product['total'];
                }

                if ($product['current_node'] == OrderItemRoute::NODE_ALREADY_SHIPPED && $product['status'] == OrderItemBusiness::STATUS_IN_HANDLE) {
                    // 发货
                    $options['deliver']['count'] = $product['total'];
                    $n += $product['total'];
                }
                if ($product['current_node'] == OrderItemRoute::NODE_ALREADY_COMPLETE && $product['status'] == OrderItemBusiness::STATUS_STAY_COMPLETE) {
                    // 已完成
                    $options['complete']['count'] = $product['total'];
                    $n += $product['total'];
                }
            }
        }
        array_unshift($options, [
            'key' => "",
            'name' => '全部',
            'count' => $n
        ]);

        return array_values($options);
    }

    /**
     *  导出为excel
     *
     * @param $_status int 状态
     * @param $number string 订单号
     * @param $productName string 产品名
     * @param $shopId int 店铺id
     * @param $sku string sku
     * @param $vendorId int 供应id
     * @param $paymentBeginAt string 付款开始时间
     * @param $paymentEndAt string 付款结束时间
     * @param $placeOrderBeginAt string 下单开始时间
     * @param $placeOrderEndAt string 下单结束时间
     * @param $totalAmountBegin float 开始金额
     * @param $totalAmountEnd float 结束金额
     * @param $customized string 定制信息
     * @throws \Exception
     */
    public function actionToExcel($_status = null, $number = null, $productName = null, $shopId = null, $sku = null, $vendorId = null, $paymentBeginAt = null, $paymentEndAt = null, $placeOrderBeginAt = null, $placeOrderEndAt = null, $totalAmountBegin = null, $totalAmountEnd = null, $customized = null)
    {
        $query = (new Query())
            ->select(['oi.id', 'o.number', 'oi.sku', 'oi.product_name', 'oi.image', 'oi.extend', 'oi.quantity', 'o.payment_at', 'oi.remark', 'v.name AS vendor_name', 'oi.cost_price', 'o.total_amount', 'oir.place_order_at'])
            ->from("{{%g_order_item}} oi")
            ->innerJoin("{{%g_order}} o", 'oi.order_id = o.id')
            ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
            ->leftJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
            ->leftJoin("{{%g_vendor}} v", 'oi.vendor_id = v.id')
            ->where(['o.platform_id' => Constant::PLATFORM_SHOPIFY, 'o.product_type' => Shop::PRODUCT_TYPE_CUSTOMIZED])
            ->andWhere(['>', 'payment_at', '1590767999']);
        // 订单号
        if ($number) {
            $number = str_replace("?", " ", $number);
            $query->andWhere(['IN', 'o.number', explode(' ', $number)]);
        }
        // 产品名
        if ($productName) {
            $query->andWhere(['LIKE', 'oi.product_name', $productName]);
        }
        // sku
        if ($sku) {
            $query->andWhere(['LIKE', 'oi.sku', $sku]);
        }
        // 供应商
        if ($vendorId) {
            $query->andWhere(['oi.vendor_id' => $vendorId]);
        }
        // 店铺
        if ($shopId) {
            $query->andWhere(['o.shop_id' => $shopId]);
        }

        // 付款开始时间 付款结束时间
        if ($paymentBeginAt && $paymentEndAt) {
            $query->andWhere(['BETWEEN', 'o.payment_at',
                (new DateTime($paymentBeginAt))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($paymentEndAt))->setTime(23, 59, 59)->getTimestamp()]);
        }

        // 金额
        if ($totalAmountBegin && $totalAmountEnd) {
            $query->andWhere(['BETWEEN', 'o.total_amount',
                $totalAmountBegin,
                $totalAmountEnd]);
        }

        //　如果有定制信息
        if ($customized) {
            $jsonSql = "jSON_CONTAINS(LOWER(oi.extend->'$.names'), JSON_ARRAY(";
            $a = [];
            $paramsExtend = [];
            foreach (explode(',', strtolower($customized)) as $i => $item) {
                $a[] = ":L{$i}";
                $paramsExtend[":L{$i}"] = $item;
            }
            $jsonSql .= implode(",", $a) . '))';

            $query->andWhere($jsonSql, $paramsExtend);
        }

        // 订单状态查询
        if ($_status) {
            switch ($_status) {
                case 1:
                    //已付款
                    $query->andWhere(['IN', 'oi.id', (new Query())->select(['oi.id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_CHECK, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 2:
                    //待下单
                    $query->andWhere(['IN', 'oi.id', (new Query())->select(['oi.id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_PLACE_ORDER, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 3:
                    //等待供应商接单
                    $query->andWhere(['IN', 'oi.id', (new Query())
                        ->select('oi.id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_STAY_RECEIPT, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 4:
                    //下单成功
                    $query->andWhere(['IN', 'oi.id', (new Query())
                        ->select('oi.id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_TO_PRODUCED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);

                    break;
                case 5:
                    //下单失败
                    $query->andWhere(['IN', 'oi.id', (new Query())->select(['oi.id'])
                        ->from('{{%g_order_item}} oi')
                        ->innerJoin('{{%om_order_item_business}} oib', 'oib.order_item_id = oi.id')
                        ->where(['oib.status' => OrderItemBusiness::STATUS_STAY_REJECT, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 6:
                    //投入生产

                    $query->andWhere(['IN', 'oi.id', (new Query())->select('oi.id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 7:
                    //取消订单
                    $query->andWhere(['IN', 'oi.id', (new Query())->select('oi.id')->from("{{%om_order_item_route_cancel_log}} rcl")
                        ->innerJoin("{{%om_order_item_route}} oir", 'rcl.order_item_route_id=oir.id')
                        ->innerJoin("{{%g_order_item}} oi", 'oir.order_item_id = oi.id')]);
                    break;
                case 8:
                    //发货
                    $query->andWhere(['IN', 'oi.id', (new Query())->select('oi.id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_route}} oir", 'oir.order_item_id = oi.id')
                        ->where(['oir.current_node' => OrderItemRoute::NODE_ALREADY_SHIPPED, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
                case 9:
                    //完成
                    $query->andWhere(['IN', 'oi.id', (new Query())->select('oi.id')
                        ->from("{{%g_order_item}} oi")
                        ->innerJoin("{{%om_order_item_business}} bi", 'bi.order_item_id = oi.id')
                        ->where(['bi.status' => OrderItemBusiness::STATUS_STAY_COMPLETE, 'oi.ignored' => Constant::BOOLEAN_FALSE])]);
                    break;
            }
        }

        // 下单开始时间 下单结束时间
        if ($placeOrderBeginAt && $placeOrderEndAt) {
            $query->andWhere(["BETWEEN", 'oir.place_order_at',
                (new DateTime($placeOrderBeginAt))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($placeOrderEndAt))->setTime(23, 59, 59)->getTimestamp()
            ])->andWhere(['IN', 'bi.status', [OrderItemBusiness::STATUS_STAY_PLACE_ORDER, OrderItemBusiness::STATUS_IN_HANDLE, OrderItemBusiness::STATUS_STAY_REJECT, OrderItemBusiness::STATUS_STAY_COMPLETE]]);
        }

        $products = $query->all();
        if ($products) {
            $phpExcel = new PHPExcel();
            $phpExcel->getProperties()->setCreator("Microsoft")
                ->setLastModifiedBy("Microsoft")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("data");
            $phpExcel->setActiveSheetIndex(0);
            $activeSheet = $phpExcel->getActiveSheet();
            $phpExcel->getDefaultStyle()
                ->getFont()->setSize(14);

            $cols = ['A' => 4, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 15, 'G' => 30, 'H' => 30, 'I' => 15, 'J' => 10, 'K' => 20, 'L' => 30, 'M' => 10, 'N' => 15, 'O' => 30, 'P' => 10, 'Q' => 30];
            foreach ($cols as $col => $width) {
                $activeSheet->getColumnDimension($col)->setWidth($width);
            }
            $activeSheet->setCellValue('A1', '产品数据')->mergeCells('A1:Q1')->getStyle()->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $activeSheet->setCellValue("A2", '序号')
                ->setCellValue("B2", '订单号')
                ->setCellValue("C2", 'SKU')
                ->setCellValue("D2", '产品名称')
                ->setCellValue("E2", '图片')
                ->setCellValue("F2", '定制信息')
                ->setCellValue("G2", '其他')
                ->setCellValue("H2", '材质')
                ->setCellValue("I2", '尺寸')
                ->setCellValue("J2", '颜色')
                ->setCellValue("K2", '产品数量')
                ->setCellValue("L2", '下单时间')
                ->setCellValue("M2", '供应商')
                ->setCellValue("N2", '订单金额')
                ->setCellValue("O2", '付款时间')
                ->setCellValue("P2", '成本')
                ->setCellValue("Q2", '订单备注');

            $row = 3;
            $i = 0;
            foreach ($products as $product) {
                $i += 1;
                $extend = json_decode($product['extend'], true);
                if ($extend['names']) {
                    $count = count($extend['names']);
                } else {
                    $count = 0;
                }
                $names = "数量：" . $count . "\n";

                if ($extend['names']) {
                    $names .= implode("\n", $extend['names']);
                }
                $other = "";
                if ($extend['other']) {
                    foreach ($extend['other'] as $key => $item) {
                        $other .= $key . ":" . $item . "\n";
                    }
                }
                $activeSheet->setCellValue("A{$row}", $i)
                    ->setCellValue("B{$row}", $product['number'])
                    ->setCellValue("C{$row}", $product['sku'])
                    ->setCellValue("D{$row}", $product['product_name'])
                    ->setCellValue("F{$row}", $names)
                    ->setCellValue("G{$row}", $other)
                    ->setCellValue("H{$row}", $extend['material'])
                    ->setCellValue("I{$row}", $extend['size'])
                    ->setCellValue("J{$row}", $extend['color'])
                    ->setCellValue("K{$row}", $product['quantity'])
                    ->setCellValue("L{$row}", date("Y-m-d H:i:s", $product['place_order_at']))
                    ->setCellValue("M{$row}", $product['vendor_name'] ? $product['vendor_name'] : "")
                    ->setCellValue("N{$row}", $product['total_amount'])
                    ->setCellValue("O{$row}", date("Y-m-d H:i:s", $product['payment_at']))
                    ->setCellValue("P{$row}", $product['cost_price'])
                    ->setCellValue("Q{$row}", $product['remark']);
                // 判断是否有图片
                if (file_exists(Yii::getAlias("@webroot") . $product['image'])) {
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('avatar');
                    $objDrawing->setDescription('avatar');
                    $objDrawing->setPath(Yii::getAlias("@webroot") . $product['image']);
                    $objDrawing->setHeight(100);
                    $objDrawing->setWidth(100);
                    $objDrawing->setCoordinates("E{$row}");
                    $objDrawing->setWorksheet($phpExcel->getActiveSheet());
                } else {
                    $activeSheet->setCellValue("E{$row}", "暂无图片");
                }
                // 设置F行可换行
                $phpExcel->getActiveSheet()->getStyle("F{$row}")->getAlignment()->setWrapText(true);
                // 设置A-M行水平居中
                $phpExcel->getActiveSheet()->getStyle("A{$row}:Q{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                // 设置A-M行垂直居中
                $phpExcel->getActiveSheet()->getStyle("A{$row}:Q{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                // 设置行高
                $phpExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(100);;
                $row++;
            }
            $phpExcel->getActiveSheet()->setTitle('产品');
            $phpExcel->setActiveSheetIndex(0);
            $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
            $filename = '产品导出' . date("Y-m-d H:i:s") . '.xlsx';
            $file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . urlencode($filename);
            $objWriter->save($file);

            Yii::$app->getResponse()->sendFile($file, $filename, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        } else {
            throw new NotFoundHttpException("无可用商品");
        }
    }

}
