<?php

namespace app\modules\api\modules\wuliu\controllers;

use app\helpers\Config;
use app\modules\api\extensions\BaseController;
use DateTime;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use SoapClient;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;

/**
 * 顺风快递查询
 *
 * @package app\modules\api\modules\tiangong\controllers
 */
class SfController extends BaseController
{

    /**
     * @var string 接口地址
     */
    protected $url;

    /**
     * @var string 客户代码
     */
    protected $clientCode;

    /**
     * @var string 密钥
     */
    protected $secureKey;

    public function init()
    {
        parent::init();
        $this->url = Config::get('sf.url');
        $this->clientCode = Config::get('sf.clientCode');
        $this->secureKey = Config::get('sf.secureKey');
    }

    /**
     * 顺丰限制，每次只能查询 30 个订单
     *
     * @param $orders
     * @return array
     */
    private function batchOrders($orders)
    {
        if ($orders) {
            $orders = explode(',', $orders);

            return array_chunk($orders, 30);
        } else {
            return [];
        }
    }

    /**
     * 发送请求
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \SoapFault
     */
    private function send($request)
    {
        $soap = new SoapClient($this->url);
        $verifyCode = base64_encode(strtoupper(md5($request . $this->secureKey)));
        $xml = $soap->sfexpressService($request, $verifyCode);
        $xml = simplexml_load_string($xml);
        $response = json_decode(json_encode($xml), true);
        if ($response !== false) {
            $response = array_change_key_case($response);
            if ($response['head'] == "OK") {
                $items = [];
                $service = $response['@attributes']['service'];
                $responseKey = str_replace("Service", '', $service) . 'Response';
                if (isset($response['body']) && $response['body']) {
                    $serviceResponse = $response['body'][$responseKey];
                    switch ($service) {
                        case 'OrderSearchService':
                            if (is_array($serviceResponse)) {
                                foreach ($serviceResponse as $item) {
                                    $items[] = $item['@attributes'];
                                }
                            } else {
                                $items[] = $serviceResponse['@attributes'];
                            }
                            break;

                        default:
                            // RouteService
                            if (ArrayHelper::isIndexed($serviceResponse)) {
                                foreach ($serviceResponse as $item) {
                                    $t = $item['@attributes'];
                                    $t['routes'] = [];
                                    if (ArrayHelper::isIndexed($item['Route'])) {
                                        foreach ($item['Route'] as $route) {
                                            $t['routes'][] = $route['@attributes'];
                                        }
                                    } else {
                                        $t['routes'][] = $item['Route']['@attributes'];
                                    }
                                    $items[] = $t;
                                }
                            } else {
                                $t = $serviceResponse['@attributes'];
                                $t['routes'] = [];
                                if (ArrayHelper::isIndexed($serviceResponse['Route'])) {
                                    foreach ($serviceResponse['Route'] as $route) {
                                        $t['routes'][] = $route['@attributes'];
                                    }
                                } else {
                                    $t['routes'][] = $serviceResponse['Route']['@attributes'];
                                }
                                $items[] = $t;
                            }
                            break;
                    }
                }

                return $items;
            } else {
                throw new BadRequestHttpException($response['error']);
            }
        } else {
            throw new BadRequestHttpException("查询失败.");
        }
    }

    /**
     * 订单查询
     *
     * @param $orders
     * @return array
     */
    public function actionOrderSearch($orders)
    {
        $items = [];
        $orders = $this->batchOrders($orders);
        foreach ($orders as $order) {
            try {
                $order = implode(',', $order);
                $request = <<<EOT
<Request service="OrderSearchService" lang="zh-CN">
    <Head>{$this->clientCode}</Head>
    <Body>
        <OrderSearch orderid="{$order}" />
    </Body>
</Request>
EOT;
                $singleItems = $this->send($request);
                if ($singleItems) {
                    $items = array_merge($items, $singleItems);
                }
            } catch (\Exception $e) {
            }
        }

        return $items;
    }

    /**
     * 路由查询
     *
     * @param $orders
     * @param int $days
     * @return array
     * @throws \Exception
     */
    public function actionRoute($orders, $days = 7)
    {
        $items = [];
        $orders = $this->batchOrders($orders);
        foreach ($orders as $order) {
            $order = implode(',', $order);
            try {
                $request = <<<EOT
<Request service="RouteService" lang="zh-CN">
    <Head>{$this->clientCode}</Head>
    <Body>
        <RouteRequest tracking_type="1" method_type="1" tracking_number="{$order}" />
    </Body>
</Request>
EOT;

                $singleItems = $this->send($request);
                if ($singleItems) {
                    $items = array_merge($items, $singleItems);
                }
            } catch (\Exception $e) {
            }
        }

        $now = time();
        foreach ($items as &$item) {
            $item['_ok'] = true;
            if ($days) {
                foreach ($item['routes'] as $i => $route) {
                    if ($i == 0) {
                        $item['_ok'] = (new DateTime($route['acceptTime']))->getTimestamp() + $days * 86400 > $now;
                        break;
                    }
                }
            }
        }
        unset($item);

        return $items;
    }

    /**
     * 路由信息导出为 Excel
     *
     * @throws BadRequestHttpException
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionRouteToExcel()
    {
        $items = Yii::$app->getRequest()->post('items');
        if (is_array($items) && $items) {
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

            $activeSheet->getDefaultRowDimension()->setRowHeight(25);
            $activeSheet->setCellValue('A1', '顺丰订单路由')->mergeCells('A1:F1')->getStyle()->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $activeSheet->setCellValue("A2", '序号')
                ->setCellValue("B2", '订单号')
                ->setCellValue("C2", '顺丰单号')
                ->setCellValue("D2", '最后路由时间')
                ->setCellValue("E2", '最后路由地点')
                ->setCellValue("F2", '备注');
            $row = 3;

            foreach ($items as $i => $item) {
                $activeSheet->setCellValue("A{$row}", $i + 1)
                    ->setCellValue("B{$row}", $item['orderid'])
                    ->setCellValue("C{$row}", $item['mailno'])
                    ->setCellValue("D{$row}", $item['acceptTime'])
                    ->setCellValue("E{$row}", $item['acceptAddress'])
                    ->setCellValue("F{$row}", $item['remark']);
                $row++;
            }

            $phpExcel->getActiveSheet()->setTitle('数据包');
            $phpExcel->setActiveSheetIndex(0);
            $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
            $filename = microtime() . '.xlsx';
            $file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $filename;
            $objWriter->save($file);

            $content = file_get_contents($file);
            FileHelper::unlink($file);
            Yii::$app->getResponse()->sendContentAsFile($content, $filename);
        } else {
            throw new BadRequestHttpException('请求有误。');
        }
    }

}