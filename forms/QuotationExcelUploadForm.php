<?php

namespace app\forms;

use Exception;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use Yii;
use yii\base\Model;

/**
 * Class QuotationExcelUploadForm
 *
 * 报价excel上传form
 *
 * @package app\forms
 */
class QuotationExcelUploadForm extends Model
{

    /**
     *  文件
     *
     * @var file
     */
    public $file;

    public function rules()
    {
        return [
            ['file', 'required'],
            [['file'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => '文件'
        ];
    }

    /**
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Exception
     */
    public function upload()
    {
        $file = $this->file;
        $inputFileType = PHPExcel_IOFactory::identify($file->tempName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file->tempName);
        $zip = new \ZipArchive(); // zip
        $zipPath = Yii::getAlias("@runtime/zip");
        // 创建目录
        if (!is_dir($zipPath)) {
            mkdir($zipPath, 0777, true);
        }
        $name = date('YmdHis', time()) . '.zip';
        $zipName = $zipPath . '/' . $name;
        // 获取excel索引
        foreach ($objPHPExcel->getSheetNames() as $sheetIndex => $sheetName) {
            $sheet = $objPHPExcel->getSheet($sheetIndex);
            // 获取sheet 行高
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $items = [];
            $weightItems = [];
            $fistWeight = 0; // 首重
            $continuedWeight = 0; // 续重
            $weightInterval = ""; // 重量区间。可多个
            $takeEffectTime = ""; // 生效时间
            $settings = $sheet->rangeToArray('A1', null, false, false);
            $settings = explode("\n", $settings[0][0]);
            foreach ($settings as $setting) {
                $setting = explode(":", $setting);
                if ($setting) {
                    switch ($setting[0]) {
                        case '首重':
                            $fistWeight = $setting[1];
                            break;
                        case '续重':
                            $continuedWeight = $setting[1];
                            break;
                        case '重量区间':
                            $weightInterval = $setting[1];
                            break;
                        case '生效时间':
                            $takeEffectTime = $setting[1];
                            break;
                        default:
                            break;
                    }
                }
            }

            $weightRange = $sheet->rangeToArray('A' . 2 . ':' . $highestColumn . 2, null, false, false);

            if ($weightRange) {
                $weightInterval = explode('|', $weightInterval);
                foreach ($weightInterval as $w) {
                    if (empty($w)) {
                        continue;
                    }

                    foreach ($weightRange[0] as $key => $weight) {
                        list($intervalStart, $intervalEnd) = explode('-', $w);
                        if ($weight == null || $key == 0) {
                            continue;
                        }
                        // 进行拆分，获取重量单位，如果为kg 则 *100，然后进行比较，获取下标
                        preg_match("/[a-zA-Z]{1,2}/", $weight, $match);
                        if (!$match) {
                            $unit = 'KG';
                        } else {
                            $unit = $match[0];
                        }
                        list($weightStart, $weightEnd) = explode('-', $weight);

                        if ($unit == 'KG') {
                            try {
                                $weightStart = $weightStart * 1000;
                                $weightEnd = str_replace($unit, '', $weightEnd);
                                $weightEnd = str_replace(' ', '', $weightEnd) * 1000;
                                $intervalStart = $intervalStart * 1000;
                                $intervalEnd = $intervalEnd * 1000;
                            } catch (Exception $exception) {
                                Yii::$app->getSession()->setFlash('notice', "数据格式错误,请检查excel");

                                return false;
                            }
                        }
                        // 开始区间大于等于开始重量。小于结束重量， 结束区间大于开始重量，小于等于结束重量
                        if ($intervalStart == $weightStart && $intervalEnd == $weightEnd) {
                            $weightItems[$w] = [
                                'startWeight' => $intervalStart,
                                'endWeight' => $intervalEnd,
                                'unit' => 'g',
                                'startIndex' => $key,
                                'endIndex' => $key + 1
                            ];
                        }
                    }
                }
            }
            // 循环获取excel数据
            for ($rowNumber = 4; $rowNumber <= $highestRow; $rowNumber++) {
                // 获取数据
                $rowData = $sheet->rangeToArray('A' . $rowNumber . ':' . $highestColumn . $rowNumber, null, false, false);

                if ($rowData) {
                    foreach ($weightItems as $key => $w) {
                        $country = null;
                        foreach ($rowData[0] as $rowKey => $rowValue) {
                            if ($rowValue === null) {
                                continue;
                            }
                            if ($rowKey == 0) {
                                $country = $rowValue;
                                $items[$country][$key] = [
                                    'startWeight' => $w['startWeight'],
                                    'endWeight' => $w['endWeight'],
                                    'firstWeight' => $fistWeight,
                                    'unit' => $w['unit'],
                                    'continuedWeight' => $continuedWeight
                                ];
                            } else {
                                if ($rowKey == $w['startIndex']) {
                                    if ($rowValue !== '-') {
                                        $items[$country][$key]['price'] = $rowValue;
                                    } else {
                                        unset($items[$country][$key]);
                                    }
                                }
                                if ($rowKey == $w['endIndex']) {
                                    if ($rowValue !== '-') {
                                        $items[$country][$key]['fee'] = $rowValue;
                                    } else {
                                        unset($items[$country][$key]);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    break;
                }
            }
            if ($items) {
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

                $cols = ['A' => 20, 'B' => 20, 'C' => 20, 'D' => 15, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 15];
                foreach ($cols as $col => $width) {
                    $activeSheet->getColumnDimension($col)->setWidth($width);
                }

                $activeSheet->setCellValue("A1", '国家(二字码，中文)')
                    ->setCellValue("B1", '开始重量（g）(*必填)')
                    ->setCellValue("C1", '结束重量（g）(*必填)')
                    ->setCellValue("D1", '首重/起重(g)')
                    ->setCellValue("E1", '首重/起重运费(￥)')
                    ->setCellValue("F1", '续重单位重量（g）(*必填)')
                    ->setCellValue("G1", '单价（g）(*必填)')
                    ->setCellValue("H1", '挂号费(￥)');

                $phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(100);
                // 设置A-M行水平居中
                $phpExcel->getActiveSheet()->getStyle("A1:H1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                // 设置A-M行垂直居中
                $phpExcel->getActiveSheet()->getStyle("A1:H1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $row = 2;
                $i = 0;
                foreach ($items as $key => $value) {
                    foreach ($value as $item) {
                        $i += 1;
                        try {
                            $price = !isset($item['price']) || $item['price'] == 0 ? 0 : $item['price'] / 1000;
                        } catch (Exception $exception) {
                            Yii::$app->getSession()->setFlash('notice', "数据匹配出错");
                        }

                        $activeSheet->setCellValue("A{$row}", $key)
                            ->setCellValue("B{$row}", $item['startWeight'])
                            ->setCellValue("C{$row}", $item['endWeight'])
                            ->setCellValue("D{$row}", $item['firstWeight'])
                            ->setCellValue("E{$row}", $item['firstWeight'] * $price)
                            ->setCellValue("F{$row}", $item['unit'] == 'g' ? 1 : 0)
                            ->setCellValue("G{$row}", $price)
                            ->setCellValue("H{$row}", $item['fee']);

                        // 设置A-M行水平居中
                        $phpExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        // 设置A-M行垂直居中
                        $phpExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $row++;
                    }
                }

                $phpExcel->getActiveSheet()->setTitle($sheetName);
                $phpExcel->setActiveSheetIndex(0);
                $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
                $filename = "({$takeEffectTime}){$sheetName}.xlsx";
                $file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . urlencode($filename);
                $objWriter->save($file);
                if ($zip->open($zipName, \ZipArchive::CREATE) === TRUE) {
                    $zip->addFile($file, $filename);
                }
            } else {
                Yii::$app->getSession()->setFlash('notice', "数据匹配出错");
            }
        }
        $zip->close();
        Yii::$app->getResponse()->sendFile($zipName, $name, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

        return true;
    }
}


