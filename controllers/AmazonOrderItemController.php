<?php

namespace app\controllers;

use app\forms\UploadForm;
use app\modules\admin\modules\wuliu\models\AmazonOrderItem;
use app\modules\admin\modules\wuliu\models\AmazonOrderItemSearch;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet_Drawing;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * AmazonOrderItemController implements the CRUD actions for AmazonOrderItem model.
 */
class AmazonOrderItemController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'upload-excel' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all AmazonOrderItem models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AmazonOrderItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AmazonOrderItem model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new AmazonOrderItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AmazonOrderItem();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AmazonOrderItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AmazonOrderItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AmazonOrderItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return AmazonOrderItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AmazonOrderItem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 打印条形码
     *
     * @param $id integer 订单详情id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \Mpdf\MpdfException
     * @throws \yii\base\Exception
     */
    public function actionPrint($id)
    {
        $db = Yii::$app->getDb();
        $product = $db->createCommand("SELECT [[order_id]], [[customized]] FROM {{%wuliu_amazon_order_item}} WHERE [[id]] = :id", [':id' => $id])->queryOne();

        // 获取年月日 创建文件夹
        $now = time();
        $y = date("y", $now);
        $m = date("m", $now);
        $d = date("d", $now);
        $dir = Yii::getAlias("@webroot/tmp/pdf/") . $y . "/" . $m . "/" . $d;
        if (!file_exists($dir)) {
            FileHelper::createDirectory($dir);
        }
        $filename = md5($product['order_id']) . ".pdf";
        $url = Yii::$app->request->hostInfo . "/tmp/pdf/" . $y . "/" . $m . "/" . $d . "/" . $filename;
        $path = $dir . "/" . $filename;
        // 判断文件是否存在
        if (!file_exists($path)) {
            if ($product) {
                $barcode = new BarcodeGenerator();
                $barcode->setText($product['order_id']);
                $barcode->setType(BarcodeGenerator::Code128);
//                $barcode->setFontSize(20);
//                $barcode->setThickness(60);
                // 生成pdf
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => [40, 58],
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

                //设置pdf的尺寸为4cm*5.8cm
                $mpdf->WriteHTML('<div style="width: 300px;text-align: left"><img src="data:image/png;base64,' . $barcode->generate() . '" />' . "<div style='font-size: 10px;margin-top: 5px'>" . $product['customized'] . '</div>' . HTMLParserMode::HTML_BODY);
                $mpdf->Output($path, 'f');

                return $url;
            } else {
                throw new NotFoundHttpException("无法找到该产品");
            }
        } else {
            return $url;
        }
    }

    /**
     * @return \yii\web\Response
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\base\Exception
     */
    public function actionUploadExcel()
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName('file');
        if ($filename = $form->upload()) {
            $excel = PHPExcel_IOFactory::load($filename);
            $rowCount = $excel->getActiveSheet()->getHighestRow();
            $columnCount = $excel->getActiveSheet()->getHighestColumn();
            $orderItems = [];
            for ($row = 2; $row <= 30; $row++) {
                $orderItem = [];
                $customized = [];
                for ($column = 'A'; $column <= $columnCount; $column++) {
                    if ($column == 'C' || $column == 'P') {
                        // 过滤图片和价格
                        continue;
                    }
                    $cellValue = $excel->getActiveSheet()->getCell($column . $row)->getValue();
                    if ($column == 'A') {
                        if (!$cellValue) {
                            // 订单号为空时停止循环
                            $row = $rowCount + 1;
                            break;
                        }
                        $exists = Yii::$app->db->createCommand("SELECT [[id]] FROM {{%wuliu_amazon_order_item}} WHERE [[order_id]] = :orderId", [':orderId' => $cellValue])->queryScalar();
                        if ($exists) {
                            // 过滤已存在的订单
                            break;
                        }
                    }
                    if (in_array($column, ['A', 'B', 'D', 'E', 'F', 'P', 'Q'])) {
                        // 合并定制信息
                        $orderItem[] = $cellValue;
                    } else {
                        $customized[] = $cellValue;
                    }
                }
                if ($orderItem) {
                    $now = time();
                    $orderItems[] = array_merge($orderItem, [implode(',', array_filter($customized)), $now, $now]);
                }
            }
            Yii::$app->db->createCommand()->batchInsert(AmazonOrderItem::tableName(), ['order_id', 'product_name', 'product_quantity', 'size', 'color', 'remark', 'customized', 'created_at', 'updated_at'], $orderItems)->execute();
        }

        return $this->redirect('index');
    }

    /**
     * @param $ids
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionToExcel($ids)
    {
        // 执行 sql 获取产品
        $sql = "SELECT * FROM {{%wuliu_amazon_order_item}}";
        if ($ids) {
            $products = Yii::$app->db->createCommand($sql . " WHERE [[id]] IN (" . implode(',', explode(',', $ids)) . ")")->queryAll();
        } else {
            $products = Yii::$app->db->createCommand($sql)->queryAll();
        }
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

            $activeSheet->getDefaultRowDimension()->setRowHeight(20);

            $cols = ['A' => 20, 'B' => 20, 'C' => 12, 'D' => 10, 'G' => 40, 'H' => '20'];
            foreach ($cols as $col => $width) {
                $activeSheet->getColumnDimension($col)->setWidth($width);
            }
            $activeSheet->setCellValue("A1", '订单号')
                ->setCellValue("B1", '产品名')
                ->setCellValue("C1", '产品图片')
                ->setCellValue("D1", '产品数量')
                ->setCellValue("E1", '尺寸')
                ->setCellValue("F1", '颜色')
                ->setCellValue("G1", '定制信息')
                ->setCellValue("H1", '备注');

            $row = 2;

            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('avatar');
            $objDrawing->setDescription('avatar');
            foreach ($products as $product) {
                $activeSheet->setCellValue("A{$row}", $product['order_id'])
                    ->setCellValue("B{$row}", $product['product_name'])
                    ->setCellValue("D{$row}", $product['product_quantity'])
                    ->setCellValue("E{$row}", $product['size'])
                    ->setCellValue("F{$row}", $product['color'])
                    ->setCellValue("G{$row}", $product['customized'])
                    ->setCellValue("H{$row}", $product['remark']);
                // 判断是否有图片
                if ($product['product_image'] && file_exists(Yii::getAlias("@webroot") . $product['product_image'])) {
                    $objDrawing->setPath(Yii::getAlias("@webroot") . $product['product_image']);
                    $objDrawing->setHeight(30);
                    $objDrawing->setWidth(30);
                    $objDrawing->setCoordinates("C{$row}");
                    $objDrawing->setWorksheet($phpExcel->getActiveSheet());
                } else {
                    $activeSheet->setCellValue("C{$row}", "暂无图片");
                }
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
