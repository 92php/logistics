<?php

namespace app\forms;

use app\models\Option;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use yadjet\helpers\DatetimeHelper;
use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 * Class PackageReportForm
 *
 * 物流导出包裹
 *
 * @package app\forms
 */
class PackageReportForm extends Model
{

    /**
     * 开始时间
     *
     * @var string
     */
    public $beginDate;

    /**
     * 结束时间
     *
     * @var string
     */
    public $endDate;

    /**
     * 平台
     *
     * @var integer
     */
    public $platform_id;

    /**
     * 物流商
     *
     * @var integer
     */
    public $logistics_provider_id;

    /**
     * 渠道
     *
     * @var integer
     */
    public $channel_id;

    public function rules()
    {
        return [
            [['beginDate', 'endDate'], 'date'],
            [['platform_id', 'logistics_provider_id', 'channel_id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'beginDate' => '开始时间',
            'endDate' => '结束时间',
            'platform_id' => '平台',
            'logistics_provider_id' => '物流商',
            'channel_id' => '渠道',
        ];
    }

    /**
     * 下载报表
     */
    public function download()
    {
        if (!$this->beginDate) {
            $this->beginDate = date('Y-m-d');
        }
        if (!$this->endDate) {
            $this->endDate = $this->beginDate;
        }
        $begin = DatetimeHelper::mktime($this->beginDate);
        $end = DatetimeHelper::mktime($this->endDate . ' 23:59:59');
        $query = (new Query())->select(['p.number', 'p.waybill_number', 's.platform_id', 'wc.name AS company_name', 'cl.name AS line_name', 'p.weight', 'p.freight_cost', 'c.chinese_name', 'p.delivery_datetime', 's.name AS shop_name', 'p.weight_datetime'])->from("{{%g_package}} p")
            ->innerJoin("{{%g_shop}} s", 's.id = p.shop_id')
            ->innerJoin("{{%wuliu_company_line}} cl", 'cl.id = p.logistics_line_id')
            ->innerJoin("{{%wuliu_company}} wc", 'wc.id = cl.company_id')
            ->innerJoin("{{%g_country}} c", 'c.id = p.country_id');

        if ($this->platform_id) {
            $query->andWhere(['s.platform_id' => $this->platform_id]);
        }
        if ($this->logistics_provider_id) {
            $query->andWhere(['wc.id' => $this->logistics_provider_id]);
        }
        if ($this->channel_id) {
            $query->andWhere(['p.logistics_line_id' => $this->channel_id]);
        }
        $query->andWhere(['BETWEEN', 'p.delivery_datetime', $begin, $end]);
        $packages = $query->all();
        if ($packages) {
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

            $cols = ['A' => 5, 'B' => 20, 'C' => 30, 'D' => 15, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 15, 'L' => 15, 'M' => 20, 'N' => 15];
            foreach ($cols as $col => $width) {
                $activeSheet->getColumnDimension($col)->setWidth($width);
            }
            $activeSheet->setCellValue("A1", '序号')
                ->setCellValue("B1", '发货单号')
                ->setCellValue("C1", '跟踪号')
                ->setCellValue("D1", '平台')
                ->setCellValue("E1", '物流商')
                ->setCellValue("F1", '渠道')
                ->setCellValue("G1", '称重时间')
                ->setCellValue("H1", '体积重')
                ->setCellValue("I1", '计费重量')
                ->setCellValue("J1", '计算物流费用')
                ->setCellValue("K1", '实际物流费用')
                ->setCellValue("L1", '国家中文名称')
                ->setCellValue("M1", '发货时间')
                ->setCellValue("N1", '业务人员名称');

            $phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(100);
            // 设置A-M行水平居中
            $phpExcel->getActiveSheet()->getStyle("A1:N1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            // 设置A-M行垂直居中
            $phpExcel->getActiveSheet()->getStyle("A1:N1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $row = 2;
            $i = 0;
            foreach ($packages as $key => $item) {
                $i += 1;
                $activeSheet->setCellValue("A{$row}", $i)
                    ->setCellValue("B{$row}", $item['number'])
                    ->setCellValue("C{$row}", $item['waybill_number'])
                    ->setCellValue("D{$row}", isset(Option::platforms()[$item['platform_id']]) ? Option::platforms()[$item['platform_id']] : "")
                    ->setCellValue("E{$row}", $item['company_name'])
                    ->setCellValue("F{$row}", $item['line_name'])
                    ->setCellValue("G{$row}", $item['weight_datetime'])
                    ->setCellValue("H{$row}", "")
                    ->setCellValue("I{$row}", $item['weight'])
                    ->setCellValue("J{$row}", $item['freight_cost'])
                    ->setCellValue("K{$row}", "")
                    ->setCellValue("L{$row}", $item['chinese_name'])
                    ->setCellValue("M{$row}", date("Y-m-d H:i:s", $item['delivery_datetime']))
                    ->setCellValue("N{$row}", $item['shop_name']);

                // 设置A-M行水平居中
                $phpExcel->getActiveSheet()->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                // 设置A-M行垂直居中
                $phpExcel->getActiveSheet()->getStyle("A{$row}:N{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $row++;
            }
            $phpExcel->getActiveSheet()->setTitle("数据源");
            $phpExcel->setActiveSheetIndex(0);
            $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
            $filename = $this->beginDate . "-" . $this->beginDate . '.xlsx';
            $file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . urlencode($filename);
            $objWriter->save($file);
            Yii::$app->getResponse()->sendFile($file, $filename, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }
    }
}
