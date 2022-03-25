<?php

namespace app\modules\admin\modules\g\forms;

use app\modules\admin\modules\g\models\Package;
use PHPExcel_IOFactory;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\Html;

/**
 * 导入包裹发货数据
 *
 * @package app\modules\admin\modules\g\forms

 */
class ImportDeliveryDataForm extends Model
{

    public $files;

    public function rules()
    {
        return [
            ['files', 'file',
                'skipOnEmpty' => false,
                'extensions' => 'csv',
                'checkExtensionByMimeType' => false,
                'maxFiles' => 10,
            ],
        ];
    }

    /**
     * 保存导入的数据
     *
     * @return void
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\db\Exception
     */
    public function save()
    {
        $db = Yii::$app->getDb();
        $excelTotalRows = 0;
        $importTotalRows = 0;
        $deliveredCount = 0;
        $notFoundWaybillNumbers = [];
        foreach ($this->files as $file) {
            $inputFileType = PHPExcel_IOFactory::identify($file->tempName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($file->tempName);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // 获取excel文件的数据，$row=2代表从第二行开始获取数据
            $waybillNumbers = [];
            $rows = [];
            for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
                // 行转换为数组
                $rowData = $sheet->rangeToArray('A' . $rowNumber . ':' . $highestColumn . $rowNumber, null, false, false);

                if (($waybillNumber = $rowData[0][0] ?? null) &&
                    ($weight = $rowData[0][1] ?? null) &&
                    ($datetime = $rowData[0][5] ?? null)
                ) {
                    $waybillNumber = str_replace(['[', ']'], '', $waybillNumber);
                    if (!isset($rows[$waybillNumber])) {
                        $excelTotalRows++;
                        $waybillNumbers[] = $waybillNumber;
                    }
                    $rows[$waybillNumber] = [
                        'rowNumber' => $rowNumber,
                        'weight' => intval($weight),
                        'datetime' => $datetime
                    ];
                }
            }
            if ($rows) {
                $searchedWaybillNumbers = [];
                $packages = (new Query())
                    ->select(['id', 'waybill_number', 'status'])
                    ->from("{{%g_package}}")
                    ->where(['waybill_number' => $waybillNumbers])
                    ->all();

                $whens = [];
                foreach ($packages as $package) {
                    $searchedWaybillNumbers[] = $package['waybill_number'];
                    unset($rows[$package['waybill_number']]);
                    if ($package['status'] == Package::STATUS_PENDING) {
                        if (($row = $rows[$package['waybill_number']] ?? null)) {
                            $whens[$package['id']] = [
                                'weight' => $row['weight'],
                                'datetime' => strtotime($row['datetime'])
                            ];
                        }
                    } else {
                        $deliveredCount += 1;
                    }
                }
                $thisTimeNotFoundWaybillNumbers = array_diff($waybillNumbers, $searchedWaybillNumbers);
                if ($thisTimeNotFoundWaybillNumbers) {
                    $notFoundWaybillNumbers = array_merge($notFoundWaybillNumbers, $thisTimeNotFoundWaybillNumbers);
                }
                if ($whens) {
                    $weightSets = [];
                    $deliveryDatetimeSets = [];
                    foreach ($whens as $id => $set) {
                        $weightSets[] = "WHEN $id THEN {$db->quoteValue($set['weight'])}";
                        $deliveryDatetimeSets[] = "WHEN $id THEN {$db->quoteValue($set['datetime'])}";
                    }
                    $sql = 'UPDATE {{%g_package}} SET [[weight]] = (CASE [[id]] ' . implode(' ', $weightSets) . ' END), [[delivery_datetime]] = (CASE [[id]] ' . implode(' ', $deliveryDatetimeSets) . ' END), [[status]] = :status WHERE [[id]] IN (' . implode(', ', array_keys($whens)) . ')';
                    $n = $db->createCommand($sql, [':status' => Package::STATUS_RECEIVED])->execute();
                    $importTotalRows += $n;
                }

                if ($rows) {
                    // 添加进未匹配包裹数据表中,先判断是否存在于未匹配表中
                    $payload = [];
                    foreach ($rows as $key => $row) {
                        $exists = $db->createCommand("SELECT COUNT(*) FROM {{%wuliu_package_not_match}} WHERE [[number]] = :number", [':number' => $key])->queryScalar();
                        if (!$exists) {
                            $payload[] = [
                                'number' => $key,
                                'weight' => $row['weight'],
                                'scan_at' => strtotime($row['datetime']),
                                'created_at' => time(),
                                'update_at' => time()
                            ];
                        }
                    }
                    if ($payload) {
                        $db->createCommand()->batchInsert("{{%wuliu_package_not_match}}", array_keys($payload[0]), $payload)->execute();
                    }
                }
            }
        }
        $messages = "Excel 文件中共有 {$excelTotalRows} 条发货数据，前期已成功导入 {$deliveredCount} 条，本次成功导入 {$importTotalRows} 条。";
        if ($notFoundWaybillNumbers) {
            $filename = 'waybill-numbers-' . Yii::$app->getUser()->getId() . time() . '.txt';
            $path = Yii::getAlias("@webroot") . "/tmp/" . $filename;
            file_put_contents($path, implode("\r\n", $notFoundWaybillNumbers));
            $messages .= Html::a('<b>点击查看未导入的发货数据 [ ' . count($notFoundWaybillNumbers) . ' ] </b>', Yii::$app->getRequest()->getBaseUrl() . '/tmp/' . $filename, ['target' => '_blank']);
        }
        Yii::$app->getSession()->setFlash('notice', $messages);
    }

    public function attributeLabels()
    {
        return [
            'files' => '发货数据',
        ];
    }

}