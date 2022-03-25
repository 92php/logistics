<?php

namespace app\modules\api\modules\om\controllers;

use app\modules\api\models\Constant;
use app\modules\api\modules\g\models\VendorSearch;
use app\modules\api\modules\om\forms\InProductionForm;
use app\modules\api\modules\om\forms\RelationPackageForm;
use app\modules\api\modules\om\forms\RemoveProductForm;
use app\modules\api\modules\om\forms\VendorDeliveryForm;
use app\modules\api\modules\om\forms\VendorOrderReceivingForm;
use app\modules\api\modules\om\models\OrderItem;
use app\modules\api\modules\om\models\OrderItemBusiness;
use app\modules\api\modules\om\models\OrderItemRoute;
use app\modules\api\modules\om\models\OrderItemRouteCancelLog;
use app\modules\api\modules\om\models\Vendor;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;
use DateTime;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet_Drawing;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * /api/om/vendor
 * 供应商操作接口
 *
 * @package app\modules\api\modules\om\controllers
 */
class VendorController extends Controller
{

    public $modelClass = Vendor::class;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    '*' => ['GET'],
                    'relation-package' => ['POST'],
                    'delivery' => ['POST'],
                    'receiving' => ['PUT', 'PATCH'],
                    'confirm-cancel' => ['PUT', 'PATCH'],
                    'in-production' => ['PUT', 'PATCH'],
                    'remove-product' => ['PUT', 'PATCH']
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'relation-package', 'start-production', 'print', 'pack-deliver-product', 'receiving', 'delivery', 'confirm-cancel', 'status-options', 'intelligence', 'in-production', 'remove-product', 'package-product'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $search = new VendorSearch();

        return $search->search(Yii::$app->getRequest()->getQueryParams());
    }

    /**
     * 获取当前供应商所有生产中商品
     *
     * @param null $size
     * @param null $color
     * @param null $material
     * @param null $number
     * @param null $sku
     * @param null $extend 定制信息
     * @param null $productName
     * @param null $placeOrderBeginAt
     * @param null $placeOrderEndAt
     * @param null $isPrint
     * @param int $pageSize
     * @param int $page
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function actionIntelligence($size = null, $color = null, $material = null, $number = null, $sku = null, $extend = null, $productName = null, $placeOrderBeginAt = null, $placeOrderEndAt = null, $isPrint = null, $pageSize = 20, $page = 1)
    {
        $query = orderItem::find()
            ->innerJoin("{{%g_order}} o", 'o.id = {{%g_order_item}}.order_id')
            ->innerJoin("{{%om_order_item_route}} or", 'or.order_item_id = {{%g_order_item}}.id')
            ->innerJoin("{{%om_order_item_business}} ob", 'ob.order_item_id = {{%g_order_item}}.id')
            ->innerJoin("{{%g_vendor}} v", 'v.id = or.vendor_id')
            ->innerJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
            ->where(['ob.status' => OrderItemBusiness::STATUS_IN_HANDLE, 'or.current_node' => OrderItemRoute::NODE_ALREADY_PRODUCED, 'vm.member_id' => Yii::$app->getUser()->getId()]);
        //尺寸关键字
        if ($size) {
            $query->andWhere(['like', "json_extract({{%g_order_item}}.extend,'$.size')", $size]);
        }
        //颜色关键字
        if ($color) {
            $query->andWhere(['like', "json_extract({{%g_order_item}}.extend,'$.color')", $color]);
        }
        //材质关键字
        if ($material) {
            $query->andWhere(['like', "json_extract({{%g_order_item}}.extend,'$.material')", $material]);
        }
        // 如果有SKU
        if ($sku) {
            $query->andWhere(['sku' => $sku]);
        }
        //　订单号
        if ($number) {
            $query->andWhere(['IN', 'o.number', explode(' ', $number)]);
        }
        // 产品名
        if ($productName) {
            $query->andWhere(['product_name' => $productName]);
        }
        // 产品名
        if ($isPrint != null) {
            $query->andWhere(['or.is_print' => (int) $isPrint]);
        }
        // 如果有定制信息
        if ($extend) {
            $jsonSql = "jSON_CONTAINS(LOWER(extend->'$.names'), JSON_ARRAY(";
            $a = [];
            $params = [];
            foreach (explode(',', strtolower($extend)) as $i => $item) {
                $a[] = ":L{$i}";
                $params[":L{$i}"] = $item;
            }
            $jsonSql .= implode(",", $a) . '))';
            $query->andWhere($jsonSql, $params);
        }
        // 下单开始时间 下单结束时间
        if ($placeOrderBeginAt && $placeOrderEndAt) {
            $query->andWhere(['BETWEEN', 'o.payment_at',
                (new DateTime($placeOrderBeginAt))->setTime(0, 0, 0)->getTimestamp(),
                (new DateTime($placeOrderEndAt))->setTime(23, 59, 59)->getTimestamp()]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => (int) $page - 1,
                'pageSize' => (int) $pageSize ?: 20,
            ]
        ]);
    }

    /**
     * 供应商导出商品接口
     *
     * @param $orderItemIds string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     * @throws \yii\db\Exception
     * @throws NotFoundHttpException
     */
    public function actionStartProduction($orderItemIds = null)
    {
        // 执行 sql 获取产品
        $query = (new Query())->select(['oi.id', 'o.number', 'oi.sku', 'oi.product_name', 'oi.image', 'oi.extend', 'oi.quantity', 'o.payment_at', 'oi.remark', 'r.is_export'])->from("{{%g_order_item}} oi")
            ->innerJoin("{{%om_order_item_route}} r", 'r.order_item_id = oi.id')
            ->innerJoin("{{%g_order}} o", 'o.id = oi.order_id')
            ->innerJoin("{{%g_vendor}} v", 'v.id = r.vendor_id')
            ->innerJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
            ->where(['r.current_node' => OrderItemRoute::NODE_TO_PRODUCED, 'vm.member_id' => Yii::$app->getUser()->getId()]);
        if ($orderItemIds) {
            $query->andWhere(['IN', 'r.order_item_id', explode(',', $orderItemIds)]);
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

            $cols = ['A' => 4, 'B' => 30, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 15, 'G' => 30, 'H' => 30, 'I' => 15, 'J' => 10, 'K' => 20, 'L' => 30, 'M' => 10, 'N' => 30];
            foreach ($cols as $col => $width) {
                $activeSheet->getColumnDimension($col)->setWidth($width);
            }
            $activeSheet->setCellValue('A1', '产品数据')->mergeCells('A1:L1')->getStyle()->applyFromArray([
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
                ->setCellValue("M2", '是否已导出')
                ->setCellValue("N2", '订单备注');
            $db = Yii::$app->getDb();
            $cmd = $db->createCommand();
            $row = 3;
            $i = 0;
            foreach ($products as $product) {
                if (!$product['is_export']) {
                    // 如果订单未导出过，则修改为已导出状态
                    $cmd->update("{{%om_order_item_route}}", ['is_export' => Constant::BOOLEAN_TRUE], ['order_item_id' => $product['id']])->execute();
                }

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
                    ->setCellValue("L{$row}", date("Y-m-d H:i:s", $product['payment_at']))
                    ->setCellValue("M{$row}", $product['is_export'] ? '是' : '否')
                    ->setCellValue("N{$row}", $product['remark']);
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
                $phpExcel->getActiveSheet()->getStyle("A{$row}:M{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                // 设置A-M行垂直居中
                $phpExcel->getActiveSheet()->getStyle("A{$row}:M{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
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

    /**
     * 打印条形码
     *
     * @param $orderItemId integer 订单详情id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \Mpdf\MpdfException
     * @throws \yii\base\Exception
     */
    public function actionPrint($orderItemId)
    {
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $product = $db->createCommand("SELECT [[o.number]], [[oi.product_name]], [[o.payment_at]], [[oi.extend]] FROM {{%g_order_item}} oi INNER JOIN {{%g_order}} o on [[o.id]] = [[oi.order_id]] WHERE [[oi.id]] = :id", [':id' => $orderItemId])->queryOne();
        if ($product) {
            $exists = $db->createCommand("SELECT COUNT(*) FROM {{%om_part}} WHERE [[order_item_id]] = :orderItemId", [':orderItemId' => $orderItemId])->queryScalar();
            if ($exists) {
                // 如果有配件，清空箱子
                $cmd->update("{{%om_part}}", ['order_item_id' => Constant::BOOLEAN_FALSE, 'is_empty' => Constant::BOOLEAN_TRUE, 'sku' => "", 'customized' => ''], ['order_item_id' => $orderItemId])->execute();
            }
            // 获取是否打印过条码
            $isPrint = $db->createCommand("SELECT [[id]], [[is_print]] FROM {{%om_order_item_route}} WHERE [[order_item_id]] = :orderItemId ORDER BY [[id]] DESC", [':orderItemId' => $orderItemId])->queryOne();
            if (isset($isPrint['is_print']) && $isPrint['is_print'] != Constant::BOOLEAN_TRUE) {
                // 如果有id，修改值
                $cmd->update("{{%om_order_item_route}}", ['is_print' => Constant::BOOLEAN_TRUE], ['id' => $isPrint['id']])->execute();
            }

            // 获取年月日 创建文件夹
            list($y, $m, $d) = explode('-', (new DateTime())->setTimestamp($product['payment_at'])->format('Y-n-j'));
            $dir = Yii::getAlias("@webroot/tmp/pdf/$y/$m/$d");
            if (!file_exists($dir)) {
                FileHelper::createDirectory($dir);
            }
            $filename = md5($product['number'] . $product['product_name']) . ".pdf";
            $url = Yii::$app->request->hostInfo . "/tmp/pdf/$y/$m/$d/$filename";
            $path = $dir . "/" . $filename;
            // 判断文件是否存在
            if (!file_exists($path)) {
                $barcode = new BarcodeGenerator();
                $barcode->setText($product['number']);
                $barcode->setType(BarcodeGenerator::Code128);
                $barcode->setFontSize(15);
                // 生成pdf
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => [40, 58], // 4.0cm x 5.8cm
                    'default_font_size' => 0,
                    'default_font' => '',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_header' => 0,
                    'margin_footer' => 0,
                    'orientation' => 'P',
                ]);
                $mpdf->SetDisplayMode('fullwidth');
                $mpdf->autoPageBreak = false; // 禁止翻页
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
                $mpdf->useAdobeCJK = true;
                $productsExtend = json_decode($product['extend'], true);
                $extend = "<div style='font-size: 8px;margin-top: 5px'>";
                foreach ($productsExtend as $key => $item) {
                    if (!in_array($key, ['raw', 'giftBox'])) {
                        if (is_array($item)) {
                            if ($key == 'names' && $productsExtend['names']) {
                                $extend .= "names: " . implode(',', $productsExtend['names']);
                            } else {
                                foreach ($item as $k => $v) {
                                    if ($v) {
                                        $extend .= "<div style='display: inline-block; width: 100%'>$k: $v</div>";
                                    }
                                }
                            }
                        } else {
                            if ($item === true) {
                                $item = '是';
                            } else if ($item === false) {
                                $item = '否';
                            }
                            if ($item) {
                                $extend .= "<div style='display: inline-block;width: 100%'>$key: $item</div>";
                            }
                        }
                    }
                }
                $extend .= "</div>";
                $html = <<<EOT
<div style="text-align: center">
    <img src="data:image/png;base64,{$barcode->generate()}" />
</div>
$extend
EOT;
                $mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
                $mpdf->Output($path, 'f');

                return $url;
            } else {
                return $url;
            }
        } else {
            throw new NotFoundHttpException("无法找到该产品！");
        }
    }

    /**
     * 供应商接单或拒接单接口
     *
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionReceiving()
    {
        $model = new VendorOrderReceivingForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 供应商发货接口
     *
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionDelivery()
    {
        $model = new VendorDeliveryForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 供应商确认是否取消
     *
     * @param $id
     * @return OrderItemRouteCancelLog|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws InvalidConfigException
     */
    public function actionConfirmCancel($id)
    {
        $post = Yii::$app->request->getBodyParams();
        if (!isset($post['confirmed_status'])) {
            throw new BadRequestHttpException("参数 confirmed_status 是必须的");
        }
        if ($model = OrderItemRouteCancelLog::findOne(['id' => $id])) {
            if ($model->confirmed_status != OrderItemRouteCancelLog::STATUS_STAY_CONFIRM) {
                throw new BadRequestHttpException("该取消申请已经确认！");
            }
            $model->load([
                'confirmed_status' => $post['confirmed_status'],
                'confirmed_message' => isset($post['confirmed_message']) ? $post['confirmed_message'] : '',
                'confirmed_by' => Yii::$app->getUser()->getId(),
                'confirmed_at' => time(),
            ], '');
            if ($model->save()) {
                Yii::$app->getResponse()->setStatusCode(201);
            } elseif (!$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
            }
        } else {
            throw  new NotFoundHttpException("Not found the id:" . $id);
        }

        return $model;
    }

    /**
     * 商品状态选项以及统计
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionStatusOptions()
    {
        $options = [];
        foreach (OrderItemRoute::statusOptions() as $key => $value) {
            if (in_array($key, [OrderItemRoute::NODE_STAY_INSPECTION, OrderItemRoute::NODE_ALREADY_CANCEL])) {
                continue;
            }
            if ($key == OrderItemRoute::NODE_ALREADY_SHIPPED) {
                $value = '已发货';
            }
            $options[$key] = [
                'key' => $key,
                'name' => $value,
                'count' => 0,
            ];
        }
        $n = 0;
        if ($options) {
            $routes = Yii::$app->getDb()->createCommand("SELECT [[current_node]], COUNT(*) AS [[count]] FROM {{%om_order_item_route}} r INNER JOIN {{%g_vendor}} v ON v.id = r.vendor_id INNER JOIN {{%g_vendor_member}} vm ON vm.vendor_id = v.id  WHERE [[current_node]] IN (" . implode(', ', array_keys($options)) . ") AND vm.member_id = :memberId GROUP BY [[current_node]]", [
                ':memberId' => Yii::$app->getUser()->getId()
            ])->queryAll();
            foreach ($routes as $route) {
                if (isset($options[$route['current_node']])) {
                    $n += $route['count'];
                    $options[$route['current_node']]['count'] = $route['count'];
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
     * 待生产商品修改为生产中。可批量
     *
     * @return InProductionForm
     * @throws Exception
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionInProduction()
    {
        $model = new InProductionForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 包裹移除订单
     *
     * @return RemoveProductForm
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionRemoveProduct()
    {
        $model = new RemoveProductForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 关联包裹号
     *
     * @return RelationPackageForm
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws \Throwable
     */
    public function actionRelationPackage()
    {
        $model = new RelationPackageForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->getResponse()->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * 获取包裹下所有订单
     *
     * @param $packageId
     * @param null $number
     * @param null $productName
     * @param null $sku
     * @param null $extend
     * @param int $page
     * @param int $pageSize
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function actionPackageProduct($packageId, $number = null, $productName = null, $sku = null, $extend = null, $page = 1, $pageSize = 20)
    {
        // 获取 business为处理中，route为生产中的商品
        $query = orderItem::find()
            ->innerJoin("{{%g_order}} o", 'o.id = {{%g_order_item}}.order_id')
            ->innerJoin("{{%om_order_item_route}} or", 'or.order_item_id = {{%g_order_item}}.id')
            ->innerJoin("{{%om_order_item_business}} ob", 'ob.order_item_id = {{%g_order_item}}.id')
            ->innerJoin("{{%g_vendor}} v", 'v.id = or.vendor_id')
            ->innerJoin("{{%g_vendor_member}} vm", 'vm.vendor_id = v.id')
            ->where(['vm.member_id' => Yii::$app->getUser()->getId(), 'or.package_id' => $packageId])
            ->andWhere(['IN', 'or.current_node', [OrderItemRoute::NODE_STAY_SHIPPED, OrderItemRoute::NODE_ALREADY_SHIPPED, OrderItemRoute::NODE_STAY_INSPECTION, OrderItemRoute::NODE_ALREADY_COMPLETE]])
            ->andWhere(['IN', 'ob.status', [OrderItemBusiness::STATUS_IN_HANDLE, OrderItemBusiness::STATUS_STAY_COMPLETE]]);
        // 如果有SKU
        if ($sku) {
            $query->andWhere(['sku' => $sku]);
        }
        //　订单号
        if ($number) {
            $query->andWhere(['IN', 'o.number', explode(' ', $number)]);
        }
        // 产品名
        if ($productName) {
            $query->andWhere(['product_name' => $productName]);
        }
        // 如果有定制信息
        if ($extend) {
            $jsonSql = "jSON_CONTAINS(LOWER(extend->'$.names'), JSON_ARRAY(";
            $a = [];
            $params = [];
            foreach (explode(',', strtolower($extend)) as $i => $item) {
                $a[] = ":L{$i}";
                $params[":L{$i}"] = $item;
            }
            $jsonSql .= implode(",", $a) . '))';
            $query->andWhere($jsonSql, $params);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => (int) $page - 1,
                'pageSize' => (int) $pageSize ?: 20,
            ]
        ]);
    }

}
