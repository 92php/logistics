<?php

namespace app\commands\g;

use app\commands\classes\DxmDataProvider;
use app\commands\classes\GerpgoDataProvider;
use app\commands\classes\Order;
use app\commands\classes\OrderItem;
use app\commands\classes\Shop;
use app\commands\classes\TongToolDataProvider;
use app\commands\Controller;
use app\models\Constant;
use app\modules\admin\modules\g\extensions\Formatter;
use app\modules\admin\modules\g\models\Package;
use app\modules\admin\modules\g\models\PackageOrderItem;
use app\modules\admin\modules\g\models\SyncTask;
use app\modules\admin\modules\om\models\OrderItemBusiness;
use DateTime;
use Symfony\Component\Process\Process;
use Yii;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\db\BaseActiveRecord;
use yii\db\Query;
use yii\helpers\Console;
use function Symfony\Component\String\u;

class ObjectsAction
{

    const ACTION_NOTHING = 'NOTHING';
    const ACTION_INSERT = 'INSERT';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_INSERT_UPDATE = 'INSERT + UPDATE';

    private $package;
    private $order;
    private $orderItem;

    public function __construct()
    {
        $this->package = self::ACTION_NOTHING;
        $this->order = self::ACTION_NOTHING;
        $this->orderItem = self::ACTION_NOTHING;
    }

    /**
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * @param string $package
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getOrderItem(): string
    {
        return $this->orderItem;
    }

    /**
     * @param string $orderItem
     */
    public function setOrderItem(string $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

}

/**
 * 数据同步
 *
 * @package app\commands

 */
class SyncController extends Controller
{

    /**
     * 脚本超时时间（秒）
     */
    const TIMEOUT_SECONDS = 600;

    /**
     * 收集模型错误信息
     *
     * @param BaseActiveRecord $model
     * @return array
     */
    private function collectionErrors(BaseActiveRecord $model)
    {
        $errors = [];
        foreach ($model->getErrors() as $err) {
            foreach ($err as $e) {
                $errors[] = $model::className() . " $e";
            }
        }

        return $errors;
    }

    /**
     * 订单同步处理
     *
     * @param null $date
     * @param int $days
     * @param int $debug
     * @return int
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @deprecated 使用 packages 动作代替 orders
     */
    public function actionOrders($date = null, $days = 1, $debug = 0)
    {
        die('This command is deprecated, please use g/sync/packages.');
        if (Console::confirm('此脚本将废弃，请使用 g/sync/packages 代替！是否终止？')) {
            return ExitCode::OK;
        }

        $todayDatetime = new DateTime();
        $days = abs(intval($days));
        $db = Yii::$app->getDb();
        // 国家数据
        $countries = (new Query())
            ->select(['id', 'abbreviation'])
            ->from('{{%g_country}}')
            ->indexBy(function ($row) {
                return strtolower($row['abbreviation']);
            })
            ->column();
        // 产品数据
        $products = (new Query())
            ->select(['chinese_name', 'sku'])
            ->from('{{%g_product}}')
            ->indexBy(function ($row) {
                return strtolower($row['sku']);
            })
            ->column();
        // 物流数据
        $logisticsLines = $db->createCommand("SELECT [[id]], [[name_prefix]], [[name]] FROM {{%wuliu_company_line}}")->queryAll();
        $fnGetLogisticsLineId = function ($name) use ($logisticsLines) {
            $id = 0;
            if (empty($name) || !($name = u($name)->trim()->toString())) {
                return $id;
            }

            foreach ($logisticsLines as $line) {
                $v = $line['name'];
                if ($line['name_prefix']) {
                    $v = "{$line['name_prefix']}-{$v}";
                }
                if ($name == $v) {
                    $id = $line['id'];
                    break;
                }
            }
            if ($id == 0) {
                if (($index = strpos($name, '-')) !== false) {
                    $name = substr($name, $index + 1);
                }
                foreach ($logisticsLines as $line) {
                    if ($name == $line['name']) {
                        $id = $line['id'];
                        break;
                    }
                }
            }

            return $id;
        };
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        $messages = [];
        $allErrorMessages = [];
        $shops = $db->createCommand('
SELECT [[t.id]] AS [[shop_id]], [[t.organization_id]], [[t.name]] AS [[shop_name]], [[t.third_party_sign]] AS [[shop_sign]], [[t.platform_id]] AS [[shop_platform_id]], [[t.product_type]], [[a.platform_id]] AS [[third_party_platform_id]], [[a.authentication_config]] FROM {{%g_shop}} t
LEFT JOIN {{%g_third_party_authentication}} a ON [[t.third_party_authentication_id]] = [[a.id]]
WHERE [[t.enabled]] = :enabled AND [[t.third_party_authentication_id]] <> 0
', [
            ':enabled' => Constant::BOOLEAN_TRUE,
        ])
            ->queryAll();
        foreach ($shops as $i => $shop) {
            $beginTime = time();
            if (!isset($messages[$shop['shop_id']])) {
                $messages[$shop['shop_id']] = [
                    'id' => $i + 1,
                    'organizationName' => $formatter->asOrganization($shop['organization_id']),
                    'platformName' => $formatter->asPlatform($shop['shop_platform_id']),
                    'shopName' => $shop['shop_name'],
                    'insertCount' => 0,
                    'updateCount' => 0,
                    'errorCount' => 0,
                    'costSeconds' => 0,
                ];
            }

            switch ($shop['third_party_platform_id']) {
                case Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI:
                    $class = new DxmDataProvider();
                    break;

                case Constant::THIRD_PARTY_PLATFORM_TONG_TOOL:
                    $class = new TongToolDataProvider();
                    break;

                default:
                    $class = null;
                    break;
            }
            if ($class) {
                $shopClass = new Shop();
                $shopClass->setId($shop['shop_id']);
                $shopClass->setPlatformId($shop['shop_platform_id']);
                $shopClass->setSign($shop['shop_sign']);
                try {
                    $datetime = new DateTime($date);
                } catch (\Exception $e) {
                    $datetime = new DateTime();
                }
                $datetime->setTime(0, 0, 0);
                if (!$date) {
                    // 如果未提供日期的话则拉取最近两天的数据（昨天和今天）
                    $datetime->modify("-1 days");
                    $days++;
                }
                for ($day = 0; $day < $days; $day++) {
                    if ($datetime > $todayDatetime) {
                        // 无效的日期直接跳过
                        break;
                    }

                    $authenticationConfig = json_decode($shop['authentication_config'], true);
                    $this->stdout("{$datetime->format('Y-m-d')} Shop [ {$shop['shop_name']} ] orders collection in progress, please waiting..." . PHP_EOL);
                    try {
                        $orders = $class->getOrders($authenticationConfig, $shopClass, $datetime);
                    } catch (\Exception $e) {
                        $orders = [];
                        $this->stderr(u(" Shop [ #{$shop['shop_id']} {$shop['shop_name']} ] throw exception ")->padBoth(80, '=')->toString() . PHP_EOL);
                        $this->stderr($e->getMessage() . PHP_EOL);
                        $this->stderr(str_repeat('=', 80) . PHP_EOL);
                    }

                    if ($orders) {
                        /* @var $order Order */
                        foreach ($orders as $order) {
                            if (!$order instanceof Order) {
                                if (is_array($order) && isset($order['number'])) {
                                    $s = $order['number'];
                                } else {
                                    $s = var_export($order, true);
                                }
                                $this->stderr(" > $s is not instanceof app\commands\classes\Order class.", [Console::BG_RED]);
                                continue;
                            }
                            if (!$order->getItems()) {
                                $this->stderr("Shop [ {$shop['shop_name']} ] -> Order number: " . $order->getNumber() . ' NOT FOUND items.' . PHP_EOL, [Console::BG_RED]);
                                continue;
                            }
                            if (!$order->getPackage() instanceof \app\commands\classes\Package) {
                                $this->stderr(" > " . var_export($order->getPackage(), true) . " is not instanceof app\commands\classes\Package class.", [Console::BG_RED]);
                                continue;
                            }
                            $order->setPlatformId($shopClass->getPlatformId());
                            $order->setThirdPartyPlatformId($shop['third_party_platform_id']);
                            $order->setProductType($shop['product_type']);
                            if (($country = strtolower($order->getCountry())) && isset($countries[$country])) {
                                $order->setCountryId($countries[$country]);
                            }

                            $package = $order->getPackage();
                            $package->setThirdPartyPlatformId($order->getThirdPartyPlatformId());
                            $package->setLogisticsLineId($fnGetLogisticsLineId($package->getLogisticsLineName()));

                            $transaction = $db->beginTransaction();
                            try {
                                $success = false;
                                $errorMessages = [];
                                $isNewOrder = false;
                                $orderModel = \app\modules\api\modules\g\models\Order::find()->where(['number' => $order->getNumber()])->one();
                                if ($orderModel === null) {
                                    $isNewOrder = true;
                                    $orderModel = new \app\modules\admin\modules\g\models\Order();
                                    $orderModel->loadDefaultValues();
                                } elseif ($orderModel->status == \app\modules\admin\modules\g\models\Order::STATUS_FINISHED) {
                                    // 已经完成的订单不再做任何处理
                                    $this->stdout(" > This order #{$orderModel->id} status is finished, ignore it." . PHP_EOL);
                                    continue;
                                }
                                if ($orderModel->load($order->toArray(), '') && $orderModel->save()) {
                                    $orderId = $orderModel->id;
                                    $packageId = null;
                                    if ($package->getNumber()) {
                                        $packageModel = Package::find()->where(['number' => $package->getNumber()])->one();
                                        if ($packageModel === null) {
                                            $packageModel = new Package();
                                            $packageModel->loadDefaultValues();
                                        }
                                        $success = $packageModel->load($package->toArray(), '') && $packageModel->save();
                                        if ($success) {
                                            $packageId = $packageModel->id;
                                        } else {
                                            $errorMessages[] = "[ Package ] ERROR: ";
                                            $errorMessages = array_merge($errorMessages, $this->collectionErrors($packageModel));
                                            $packageId = 0;
                                        }
                                    }

                                    if ($packageId === null || $success) {
                                        // 没有包裹信息或者有包裹信息且包裹添加或者更新成功则执行余下的处理
                                        $orderItemIds = $db->createCommand("SELECT [[id]] FROM {{%g_order_item}} WHERE [[order_id]] = :orderId", [':orderId' => $orderId])->queryColumn();
                                        foreach ($order->getItems() as $orderItem) {
                                            /* @var $orderItem OrderItem */
                                            if ($orderItem instanceof OrderItem) {
                                                $orderItem->setOrderId($orderId);
                                                /* @var $orderItemModel \app\modules\admin\modules\g\models\OrderItem */
                                                $orderItemModel = \app\modules\admin\modules\g\models\OrderItem::find()->where([
                                                    'order_id' => $orderId,
                                                    'key' => $orderItem->getKey(),
                                                    'sku' => $orderItem->getSku(),
                                                ])->one();
                                                if ($orderItemModel === null) {
                                                    // key 为后续添加，需要修正历史数据
                                                    $orderItemModel = \app\modules\admin\modules\g\models\OrderItem::find()->where(['AND', [
                                                        'order_id' => $orderId,
                                                        'sku' => $orderItem->getSku()
                                                    ], "[[key]] IS NULL OR [[key]] = ''"])->one();
                                                }
                                                if ($orderItemModel === null) {
                                                    $sku = $orderItem->getSku();
                                                    $productName = $orderItem->getProductName();
                                                    $orderItemModel = new \app\modules\admin\modules\g\models\OrderItem();
                                                    $orderItemModel->loadDefaultValues();
                                                } else {
                                                    $sku = $orderItemModel->sku;
                                                    $productName = $orderItemModel->product_name;
                                                    foreach ($orderItemIds as $key => $id) {
                                                        if ($orderItemModel->id == $id) {
                                                            unset($orderItemIds[$key]);
                                                        }
                                                    }
                                                }
                                                // 更新 order_item 商品名称
                                                $sku = strtolower($sku);
                                                if (($sku == strtolower($productName) || empty($productName)) && isset($products[$sku])) {
                                                    $orderItem->setProductName($products[$sku]);
                                                }

                                                $payload = array_merge($orderItemModel->toArray(), $orderItem->toArray());
                                                $success = $orderItemModel->load($payload, '') && $orderItemModel->save();
                                                if ($success) {
                                                    if ($packageId) {
                                                        $packageOrderItemModel = PackageOrderItem::find()->where([
                                                            'package_id' => $packageId,
                                                            'order_id' => $orderModel->id,
                                                            'order_item_id' => $orderItemModel->id
                                                        ])->one();
                                                        if ($packageOrderItemModel === null) {
                                                            $packageOrderItemModel = new PackageOrderItem();
                                                            $packageOrderItemModel->loadDefaultValues();
                                                        }
                                                        $success = $packageOrderItemModel->load([
                                                                'package_id' => $packageId,
                                                                'order_id' => $orderModel->id,
                                                                'order_item_id' => $orderItemModel->id
                                                            ], '') && $packageOrderItemModel->save();
                                                        if (!$success) {
                                                            $errorMessages[] = "[ PackageOrderItem ] ERROR: ";
                                                            $errorMessages = array_merge($errorMessages, $this->collectionErrors($packageOrderItemModel));
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    $errorMessages[] = "[ OrderItem ] ERROR: ";
                                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($orderItemModel));
                                                    break;
                                                }
                                            } else {
                                                if (is_array($orderItem) && isset($orderItem['sku'])) {
                                                    $s = $orderItem['sku'];
                                                } else {
                                                    $s = var_export($orderItem, true);
                                                }
                                                $this->stderr(" > $s is not instanceof app\commands\classes\OrderItem class.", [Console::BG_RED]);
                                                $success = false;
                                                break;
                                            }
                                        }
                                        if ($success && $orderItemIds) {
                                            $models = \app\modules\admin\modules\g\models\OrderItem::findAll(['id' => $orderItemIds]);
                                            foreach ($models as $model) {
                                                $success = $model->delete() ? true : false;
                                                if (!$success) {
                                                    $errorMessages[] = "Delete [ OrderItem ] ERROR: ";
                                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($model));
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $errorMessages[] = "[ Order ] ERROR 原始数据: ";
                                    $errorMessages[] = var_export($order, true);
                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($orderModel));
                                }

                                $success ? $transaction->commit() : $transaction->rollBack();
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                $success = false;
                                $errorMessages[] = $e->getMessage();
                            } catch (\Throwable $e) {
                                $transaction->rollBack();
                                $success = false;
                                $errorMessages[] = $e->getMessage();
                            }

                            $hint = "{$datetime->format('Y-m-d')} Shop [ {$shop['shop_name']} ] -> Order Number: {$order->getNumber()} " . ($isNewOrder ? 'INSERT' : 'UPDATE') . ' ';
                            if ($success) {
                                $messages[$shop['shop_id']][$isNewOrder ? 'insertCount' : 'updateCount'] += 1;
                                $this->stdout($hint . Console::ansiFormat(" OK ", [Console::BG_GREEN]) . PHP_EOL);
                            } else {
                                $messages[$shop['shop_id']]['errorCount'] += 1;
                                $this->stdout($hint . Console::ansiFormat(" ERROR ", [Console::BG_RED]) . PHP_EOL);
                                if ($errorMessages) {
                                    $errorMessages[] = "Third Party Platform: " . $formatter->asThirdPartyPlatform($shop['third_party_platform_id']);
                                    foreach ($errorMessages as $errorMessage) {
                                        $this->stderr(" > $errorMessage" . PHP_EOL);
                                    }
                                    $allErrorMessages = array_merge($allErrorMessages, $errorMessages);
                                } else {
                                    $this->stderr(' > ' . Console::ansiFormat(" Not found error messages!", [Console::BG_RED]) . PHP_EOL);
                                }
                                $this->stdout(str_repeat('#', 80) . PHP_EOL);
                                if (boolval($debug)) {
                                    goto BREAK_OFF;
                                }
                            }
                        }
                    } else {
                        $this->stdout(sprintf("%s Shop [ %s ] not found orders.", $datetime->format('Y-m-d'), $shop['shop_name']) . PHP_EOL);
                    }

                    $datetime->modify('+1 days');
                }
            }
            $messages[$shop['shop_id']]['costSeconds'] = time() - $beginTime;
        }

        BREAK_OFF:

        if ($messages) {
            $fnFormatSeconds = function ($seconds) {
                if ($seconds < 60) {
                    $s = "{$seconds}s";
                } else {
                    $a = floor($seconds / 60);
                    $s = "{$a}m";
                    if ($b = $seconds % 60) {
                        $s .= "{$b}s";
                    }
                }

                return $s;
            };
            $summary = [
                'id' => null,
                'organizationName' => null,
                'platformName' => null,
                'shopName' => 'Total',
                'insertCount' => 0,
                'updateCount' => 0,
                'errorCount' => 0,
                'costSeconds' => 0,
            ];
            foreach ($messages as &$message) {
                $summary['insertCount'] += $message['insertCount'];
                $summary['updateCount'] += $message['updateCount'];
                $summary['errorCount'] += $message['errorCount'];
                $summary['costSeconds'] += $message['costSeconds'];
                $message['costSeconds'] = $fnFormatSeconds($message['costSeconds']);
            }
            unset($message);
            $summary['costSeconds'] = $fnFormatSeconds($summary['costSeconds']);
            $messages[] = $summary;
            echo Table::widget([
                'headers' => ['#', 'Organization Name', 'Platform Name', 'Shop Name', "Insert", "Update", "Error", 'Time'],
                'rows' => array_values($messages),
            ]);
        }

        if ($allErrorMessages) {
            foreach ($allErrorMessages as $i => $errorMessage) {
                $this->stderr(" > $errorMessage" . PHP_EOL);
            }
        }

        $this->stdout("Done." . PHP_EOL);
    }

    /**
     * 订单同步处理
     *
     * @param null $shopId
     * @param null $beginDate
     * @param null $endDate
     * @param int $debug
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionPackages($shopId = null, $beginDate = null, $endDate = null, $debug = 0)
    {
        try {
            $endDate || $endDate = $beginDate;
            $beginDate = new DateTime($beginDate);
            $endDate = new DateTime($endDate);
        } catch (\Exception $e) {
            $beginDate = new DateTime();
            $endDate = new DateTime();
        }
        $beginDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);
        $db = Yii::$app->getDb();
        // 国家数据
        $countries = (new Query())
            ->select(['id', 'chinese_name', 'abbreviation'])
            ->from('{{%g_country}}')
            ->all();
        $fnGetCountryId = function ($value) use ($countries) {
            $id = 0;
            $value = strtolower($value);
            foreach ($countries as $country) {
                if ($value == $country['chinese_name'] || $value == strtolower($country['abbreviation'])) {
                    $id = $country['id'];
                    break;
                }
            }

            return $id;
        };
        // 产品数据
        $products = (new Query())
            ->select(['chinese_name', 'sku'])
            ->from('{{%g_product}}')
            ->indexBy(function ($row) {
                return strtolower($row['sku']);
            })
            ->column();
        // 物流数据
        $logisticsLines = $db->createCommand("SELECT [[id]], [[name_prefix]], [[name]] FROM {{%wuliu_company_line}}")->queryAll();
        $fnGetLogisticsLineId = function ($name) use ($logisticsLines) {
            $id = 0;
            if (empty($name) || !($name = u($name)->trim()->toString())) {
                return $id;
            }

            foreach ($logisticsLines as $line) {
                $v = $line['name'];
                if ($line['name_prefix']) {
                    $v = "{$line['name_prefix']}-{$v}";
                }
                if ($name == $v) {
                    $id = $line['id'];
                    break;
                }
            }
            if ($id == 0) {
                if (($index = strpos($name, '-')) !== false) {
                    $name = substr($name, $index + 1);
                }
                foreach ($logisticsLines as $line) {
                    if ($name == $line['name']) {
                        $id = $line['id'];
                        break;
                    }
                }
            }

            return $id;
        };
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();
        $messages = [];
        $allErrorMessages = [];
        $sql = '
SELECT [[t.id]] AS [[shop_id]], [[t.organization_id]], [[t.name]] AS [[shop_name]], [[t.third_party_sign]] AS [[shop_sign]], [[t.platform_id]] AS [[shop_platform_id]], [[t.product_type]], [[a.platform_id]] AS [[third_party_platform_id]], [[a.authentication_config]] FROM {{%g_shop}} t
LEFT JOIN {{%g_third_party_authentication}} a ON [[t.third_party_authentication_id]] = [[a.id]]
WHERE [[t.enabled]] = :enabled AND [[t.third_party_authentication_id]] <> 0';
        $params = [
            ':enabled' => Constant::BOOLEAN_TRUE,
        ];
        if ($shopId && $shopId != 'null') {
            if (strpos($shopId, '-') !== false) {
                list($min, $max) = explode('-', $shopId);
                $shopId = [];
                for ($i = $min; $i <= $max; $i++) {
                    $shopId[] = $i;
                }
            } else {
                $shopId = array_unique(array_filter(explode(',', $shopId)));
                $shopId = array_map(function ($v) {
                    return intval($v);
                }, $shopId);
            }

            $sql .= ' AND [[t.id]] IN (' . implode(', ', $shopId) . ')';
        }

        $shops = $db->createCommand($sql, $params)->queryAll();
        foreach ($shops as $i => $shop) {
            $beginTime = time();
            if (!isset($messages[$shop['shop_id']])) {
                $messages[$shop['shop_id']] = [
                    'id' => $i + 1,
                    'organizationName' => $formatter->asOrganization($shop['organization_id']),
                    'platformName' => $formatter->asPlatform($shop['shop_platform_id']),
                    'shopName' => $shop['shop_name'],
                    'insertCount' => 0,
                    'updateCount' => 0,
                    'nothingCount' => 0,
                    'errorCount' => 0,
                    'costSeconds' => 0,
                ];
            }

            switch ($shop['third_party_platform_id']) {
                case Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI:
                    $class = new DxmDataProvider();
                    break;

                case Constant::THIRD_PARTY_PLATFORM_TONG_TOOL:
                    $class = new TongToolDataProvider();
                    break;

                case Constant::THIRD_PARTY_PLATFORM_GERPGO:
                    $class = new GerpgoDataProvider();
                    break;

                default:
                    $class = null;
                    break;
            }
            if ($class) {
                $shopClass = new Shop();
                $shopClass->setId($shop['shop_id']);
                $shopClass->setPlatformId($shop['shop_platform_id']);
                $shopClass->setSign($shop['shop_sign']);
                $this->stdout(sprintf("Shop [ #%d %s ] %s ~ %s",
                        $shop['shop_id'],
                        $shop['shop_name'],
                        $beginDate->format('Y-m-d'),
                        $endDate->format('Y-m-d')
                    ) . PHP_EOL);
                $authenticationConfig = json_decode($shop['authentication_config'], true);
                $this->stdout("{$beginDate->format('Y-m-d')} ~ {$endDate->format('Y-m-d')} Shop #{$shop['shop_id']} [ {$shop['shop_name']} ] packages collection in progress, please waiting..." . PHP_EOL);
                try {
                    $packages = $class->getPackages($authenticationConfig, $shopClass, clone $beginDate, clone $endDate);
                } catch (\Exception $e) {
                    $packages = [];
                    $this->stderr(u(" Shop [ #{$shop['shop_id']} {$shop['shop_name']} ] throw exception ")->padBoth(80, '=')->toString() . PHP_EOL);
                    $this->stderr($e->getMessage() . PHP_EOL);
                    $this->stderr(str_repeat('=', 80) . PHP_EOL);
                }

                if ($packages) {
                    /* @var $package \app\commands\classes\Package */
                    foreach ($packages as $package) {
                        $objectsAction = new ObjectsAction();
                        if (!$package instanceof \app\commands\classes\Package) {
                            if (is_array($package) && isset($package['number'])) {
                                $s = $package['number'];
                            } else {
                                $s = var_export($package, true);
                            }
                            $this->stderr(" > $s is not instanceof app\commands\classes\Package class." . PHP_EOL, [Console::BG_RED]);
                            continue;
                        }
                        if (!$package->getOrders()) {
                            $this->stderr("Shop [ {$shop['shop_name']} ] -> Package number: " . $package->getNumber() . ' not found orders in this package.' . PHP_EOL, [Console::BG_RED]);
                            continue;
                        }
                        $package->setThirdPartyPlatformId($shop['third_party_platform_id']);
                        $package->setLogisticsLineId($fnGetLogisticsLineId($package->getLogisticsLineName()));
                        if ($package->getCountry()) {
                            $package->setCountryId($fnGetCountryId($package->getCountry()));
                        }
                        $transaction = $db->beginTransaction();
                        try {
                            $success = false;
                            $errorMessages = [];
                            $packageId = null;
                            if ($package->getNumber()) {
                                /* @var $packageModel Package */
                                $packageModel = Package::find()->where(['number' => $package->getNumber()])->one();
                                if ($packageModel === null) {
                                    $objectsAction->setPackage($objectsAction::ACTION_INSERT);
                                    $packageModel = new Package();
                                    $packageModel->loadDefaultValues();
                                } elseif ($packageModel->status == Package::STATUS_SUCCESSFUL_RECEIPTED) {
                                    // 已经完成的包裹不再做任何处理
                                    $success = true;
                                    $this->stdout(" > This package #{$packageModel->number} status is finished, ignore it." . PHP_EOL);
                                    $transaction->rollBack();
                                    continue;
                                } else {
                                    $objectsAction->setPackage($objectsAction::ACTION_UPDATE);
                                }
                                $success = $packageModel->load($package->toArray(), '') && $packageModel->save();
                                if ($success) {
                                    $packageId = $packageModel->id;
                                } else {
                                    $errorMessages[] = "[ Package ] ERROR: ";
                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($packageModel));
                                    $packageId = 0;
                                }
                            }

                            if ($packageId === null || $success) {
                                foreach ($package->getOrders() as $order) {
                                    /* @var $order Order */
                                    $order->setThirdPartyPlatformId($shop['third_party_platform_id']);
                                    /* @var $orderModel \app\modules\admin\modules\g\models\Order */
                                    $orderModel = \app\modules\api\modules\g\models\Order::find()->where(['number' => $order->getNumber()])->one();
                                    if ($orderModel === null) {
                                        $objectsAction->setOrder($objectsAction::ACTION_INSERT);
                                        $orderModel = new \app\modules\admin\modules\g\models\Order();
                                        $orderModel->loadDefaultValues();
                                    } elseif ($orderModel->status == \app\modules\admin\modules\g\models\Order::STATUS_FINISHED) {
                                        // 已经完成的订单不再做任何处理
                                        $success = true;
                                        $this->stdout(" > This order #{$orderModel->number} status is finished, ignore it." . PHP_EOL);
                                        continue;
                                    } else {
                                        $objectsAction->setOrder($objectsAction::ACTION_UPDATE);
                                    }

                                    if ($order->getCountry()) {
                                        $order->setCountryId($fnGetCountryId($order->getCountry()));
                                    }
                                    $success = $orderModel->load($order->toArray(), '') && $orderModel->save();
                                    if ($success) {
                                        $orderId = $orderModel->id;
                                        $sql = "SELECT [[id]] FROM {{%g_order_item}} WHERE [[order_id]] = :orderId";
                                        $params = [
                                            ':orderId' => $orderId,
                                        ];
                                        if ($packageId) {
                                            $sql .= ' AND [[id]] IN (SELECT [[order_item_id]] FROM {{%g_package_order_item}} WHERE [[package_id]] = :packageId AND [[order_id]] = :orderId2)';
                                            $params[':packageId'] = $packageId;
                                            $params[':orderId2'] = $orderId;
                                        }
                                        $orderItemIds = $db->createCommand($sql, $params)->queryColumn();
                                        foreach ($order->getItems() as $orderItem) {
                                            /* @var $orderItem OrderItem */
                                            if ($orderItem instanceof OrderItem) {
                                                $orderItem->setOrderId($orderId);
                                                /* @var $orderItemModel \app\modules\admin\modules\g\models\OrderItem */
                                                // 老数据查询修正
                                                $orderItemModel = \app\modules\admin\modules\g\models\OrderItem::find()->where([
                                                    'order_id' => $orderId,
                                                    'key' => $orderItem->getPid(),
                                                    'sku' => $orderItem->getSku(),
                                                ])->one();
                                                if ($orderItemModel === null) {
                                                    // key 为后续添加，需要修正历史数据
                                                    $orderItemModel = \app\modules\admin\modules\g\models\OrderItem::find()->where(['AND', [
                                                        'order_id' => $orderId,
                                                        'sku' => $orderItem->getSku()
                                                    ], "[[key]] IS NULL OR [[key]] = ''"])->one();
                                                    if ($orderItemModel === null) {
                                                        $orderItemModel = \app\modules\admin\modules\g\models\OrderItem::find()->where([
                                                            'order_id' => $orderId,
                                                            'key' => $orderItem->getKey(),
                                                            'sku' => $orderItem->getSku(),
                                                        ])->one();
                                                    }
                                                }
                                                if ($orderItemModel === null) {
                                                    $sku = $orderItem->getSku();
                                                    $productName = $orderItem->getProductName();
                                                    $orderItemModel = new \app\modules\admin\modules\g\models\OrderItem();
                                                    $orderItemModel->loadDefaultValues();
                                                } else {
                                                    $sku = $orderItemModel->sku;
                                                    $productName = $orderItemModel->product_name;
                                                    foreach ($orderItemIds as $key => $id) {
                                                        if ($orderItemModel->id == $id) {
                                                            unset($orderItemIds[$key]);
                                                        }
                                                    }
                                                }

                                                $action = $objectsAction->getOrderItem();
                                                switch ($action) {
                                                    case ObjectsAction::ACTION_INSERT:
                                                        if (!$orderItemModel->getIsNewRecord()) {
                                                            $action = ObjectsAction::ACTION_INSERT_UPDATE;
                                                        }
                                                        break;

                                                    case ObjectsAction::ACTION_UPDATE:
                                                        if ($orderItemModel->getIsNewRecord()) {
                                                            $action = ObjectsAction::ACTION_INSERT_UPDATE;
                                                        }
                                                        break;

                                                    case ObjectsAction::ACTION_NOTHING:
                                                        $action = $orderItemModel->getIsNewRecord() ? ObjectsAction::ACTION_INSERT : ObjectsAction::ACTION_UPDATE;
                                                        break;
                                                }
                                                $objectsAction->setOrderItem($action);

                                                // 更新 order_item 商品名称
                                                $sku = strtolower($sku);
                                                if (($sku == strtolower($productName) || empty($productName)) && isset($products[$sku])) {
                                                    $orderItem->setProductName($products[$sku]);
                                                }

                                                $orderItemData = $orderItem->toArray();
                                                if (!$orderItemModel->getIsNewRecord()) {
                                                    $omStatus = $db->createCommand('SELECT [[status]] FROM {{%om_order_item_business}} WHERE [[order_item_id]] = :orderItemId', [':orderItemId' => $orderItemModel->id])->queryScalar();
                                                    if ($omStatus === false || $omStatus != OrderItemBusiness::STATUS_STAY_CHECK) {
                                                        // 已经审核的商品不能再次修改其定制资料
                                                        unset($orderItemData['extend']);
                                                    }
                                                }
                                                if (!$orderItemModel->getIsNewRecord() && $orderItemModel->ignored) {
                                                    unset($orderItemData['ignored']);
                                                }
                                                $payload = array_merge($orderItemModel->toArray(), $orderItemData);
                                                $success = $orderItemModel->load($payload, '') && $orderItemModel->save();
                                                if ($success) {
                                                    if ($packageId) {
                                                        $packageOrderItemModel = PackageOrderItem::find()->where([
                                                            'package_id' => $packageId,
                                                            'order_id' => $orderModel->id,
                                                            'order_item_id' => $orderItemModel->id
                                                        ])->one();
                                                        if ($packageOrderItemModel === null) {
                                                            $packageOrderItemModel = new PackageOrderItem();
                                                            $packageOrderItemModel->loadDefaultValues();
                                                        }
                                                        $success = $packageOrderItemModel->load([
                                                                'package_id' => $packageId,
                                                                'order_id' => $orderModel->id,
                                                                'order_item_id' => $orderItemModel->id
                                                            ], '') && $packageOrderItemModel->save();
                                                        if (!$success) {
                                                            $errorMessages[] = "[ PackageOrderItem ] ERROR: ";
                                                            $errorMessages = array_merge($errorMessages, $this->collectionErrors($packageOrderItemModel));
                                                            break 2;
                                                        }
                                                    }
                                                } else {
                                                    $errorMessages[] = "[ OrderItem ] ERROR: ";
                                                    $errorMessages[] = "[ OrderItem ] 原始数据: ";
                                                    $errorMessages[] = var_export($orderItem, true);
                                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($orderItemModel));
                                                    break 2;
                                                }
                                            } else {
                                                if (is_array($orderItem) && isset($orderItem['sku'])) {
                                                    $s = $orderItem['sku'];
                                                } else {
                                                    $s = var_export($orderItem, true);
                                                }
                                                $this->stderr(" > $s is not instanceof app\commands\classes\OrderItem class.", [Console::BG_RED]);
                                                $success = false;
                                                break;
                                            }
                                        }
                                        if ($success && $orderItemIds) {
                                            $models = \app\modules\admin\modules\g\models\OrderItem::findAll(['id' => $orderItemIds]);
                                            foreach ($models as $model) {
                                                $success = $model->delete() ? true : false;
                                                if (!$success) {
                                                    $errorMessages[] = "Delete [ OrderItem ] ERROR: ";
                                                    $errorMessages = array_merge($errorMessages, $this->collectionErrors($model));
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        $errorMessages[] = "[ Order ] 原始数据: ";
                                        $errorMessages[] = var_export($order, true);
                                        $errorMessages = array_merge($errorMessages, $this->collectionErrors($orderModel));
                                    }
                                }
                            }

                            $success ? $transaction->commit() : $transaction->rollBack();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                            $success = false;
                            $errorMessages[] = $e->getMessage();
                        } catch (\Throwable $e) {
                            $transaction->rollBack();
                            $success = false;
                            $errorMessages[] = $e->getMessage();
                        }

                        $hint = "{$beginDate->format('Y-m-d')} ~ {$endDate->format('Y-m-d')} Shop #{$shop['shop_id']} [ {$shop['shop_name']} ] Package ";
                        if ($objectsAction->getPackage() != ObjectsAction::ACTION_NOTHING) {
                            $hint .= $package->getNumber();
                        }
                        $hint .= " [ {$objectsAction->getPackage()} ] Order [ {$objectsAction->getOrder()} ] OrderItem [ {$objectsAction->getOrderItem()} ] ";
                        if ($success) {
                            $messagesKey = 'nothingCount';
                            if (in_array($objectsAction->getPackage(), [ObjectsAction::ACTION_UPDATE, ObjectsAction::ACTION_INSERT_UPDATE]) ||
                                in_array($objectsAction->getOrder(), [ObjectsAction::ACTION_UPDATE, ObjectsAction::ACTION_INSERT_UPDATE]) ||
                                in_array($objectsAction->getOrderItem(), [ObjectsAction::ACTION_UPDATE, ObjectsAction::ACTION_INSERT_UPDATE])
                            ) {
                                $messagesKey = 'updateCount';
                            } elseif ($objectsAction->getPackage() == ObjectsAction::ACTION_INSERT ||
                                $objectsAction->getOrder() == ObjectsAction::ACTION_INSERT ||
                                $objectsAction->getOrderItem() == ObjectsAction::ACTION_INSERT
                            ) {
                                $messagesKey = 'insertCount';
                            }
                            $messages[$shop['shop_id']][$messagesKey] += 1;
                            $this->stdout($hint . Console::ansiFormat(" Successful ", [Console::BG_GREEN]) . PHP_EOL);
                        } else {
                            $messages[$shop['shop_id']]['errorCount'] += 1;
                            $this->stdout($hint . Console::ansiFormat(" Failure ", [Console::BG_RED]) . PHP_EOL);
                            if ($errorMessages) {
                                $errorMessages[] = "Third Party Platform: " . $formatter->asThirdPartyPlatform($shop['third_party_platform_id']);
                                foreach ($errorMessages as $errorMessage) {
                                    $this->stderr(" > $errorMessage" . PHP_EOL);
                                }
                                $allErrorMessages = array_merge($allErrorMessages, $errorMessages);
                            } else {
                                $this->stderr(' > ' . Console::ansiFormat("Save failed, But not found error messages! Please check code.", [Console::BG_RED]) . PHP_EOL);
                            }
                            $this->stdout(str_repeat('#', 80) . PHP_EOL);
                            if (boolval($debug)) {
                                goto BREAK_OFF;
                            }
                        }
                    }
                } else {
                    $this->stdout(sprintf("%s ~ %s Shop #%d [ %s ] " . Console::ansiFormat('not found packages', [Console::BG_YELLOW]), $beginDate->format('Y-m-d'), $endDate->format('Y-m-d'), $shop['shop_id'], $shop['shop_name']) . PHP_EOL);
                }
            }
            $messages[$shop['shop_id']]['costSeconds'] = time() - $beginTime;
        }

        BREAK_OFF:
        $contents = '';
        if ($messages) {
            $fnFormatSeconds = function ($seconds) {
                if ($seconds < 60) {
                    $s = "{$seconds}s";
                } else {
                    $a = floor($seconds / 60);
                    $s = "{$a}m";
                    if ($b = $seconds % 60) {
                        $s .= "{$b}s";
                    }
                }

                return $s;
            };
            $summary = [
                'id' => null,
                'organizationName' => null,
                'platformName' => null,
                'shopName' => 'Total',
                'insertCount' => 0,
                'updateCount' => 0,
                'nothingCount' => 0,
                'errorCount' => 0,
                'costSeconds' => 0,
            ];
            foreach ($messages as &$message) {
                $summary['insertCount'] += $message['insertCount'];
                $summary['updateCount'] += $message['updateCount'];
                $summary['nothingCount'] += $message['nothingCount'];
                $summary['errorCount'] += $message['errorCount'];
                $summary['costSeconds'] += $message['costSeconds'];
                $message['costSeconds'] = $fnFormatSeconds($message['costSeconds']);
            }
            unset($message);
            $summary['costSeconds'] = $fnFormatSeconds($summary['costSeconds']);
            $messages[] = $summary;
            $table = Table::widget([
                'headers' => ['#', 'Organization Name', 'Platform Name', 'Shop Name', "Insert", "Update", "Nothing", "Error", 'Time'],
                'rows' => array_values($messages),
            ]);
            $contents .= $table;
            echo $table;
        }

        $allErrorMessages && $contents .= var_export($allErrorMessages, true);
        if ($contents) {
            file_put_contents(Yii::getAlias('@runtime/logs/sync-' . date('YmdHi') . '.log'), $contents);
        }

        $this->stdout("Done." . PHP_EOL);
    }

    /**
     * 自动开启同步任务
     *
     * @throws \yii\db\Exception
     */
    public function actionScanTasks()
    {
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $path = 'php ' . Yii::getAlias('@app') . '/';
        $processes = [];
        while (true) {
            $tasks = $db->createCommand('SELECT * FROM {{%g_sync_task}} WHERE [[status]] = :status ORDER BY [[priority]] ASC, [[begin_date]] DESC LIMIT 10', [
                ':status' => SyncTask::STATUS_PENDING,
            ])->queryAll();
            if ($tasks) {
                foreach ($tasks as $task) {
                    if (isset($processes[$task['id']])) {
                        continue;
                    }
                    $exists = $db->createCommand('SELECT COUNT(*) FROM {{%g_shop}} WHERE [[id]] = :id AND [[enabled]] = :enabled', [':id' => $task['shop_id'], ':enabled' => Constant::BOOLEAN_TRUE])->queryScalar();
                    if (!$exists) {
                        $cmd->delete('{{%g_sync_task}}', ['id' => $task['id']])->execute();
                        continue;
                    }
                    $beginDate = (new DateTime())->setTimestamp($task['begin_date']);
                    $endDate = (new DateTime())->setTimestamp($task['end_date']);
                    $command = "yii g/sync/packages {$task['shop_id']} {$beginDate->format('Y-m-d')} {$endDate->format('Y-m-d')}";
                    try {
                        $process = Process::fromShellCommandline("{$path}{$command}", null, null, null, self::TIMEOUT_SECONDS);
                        $process->setTimeout(self::TIMEOUT_SECONDS);
                        $process->setIdleTimeout(120);
                        $process->start();
                        $pid = $process->getPid();
                        $processes[$task['id']] = [
                            'pid' => $pid,
                            'shopId' => $task['shop_id'],
                            'taskId' => $task['id'],
                            'startDatetime' => time(),
                            'process' => $process,
                        ];
                        $this->stdout('[ ' . date('Y-m-d H:i:s') . " ] Start process successful, pid is " . $pid . PHP_EOL);
                        $cmd->update('{{%g_sync_task}}', [
                            'start_datetime' => time(),
                            'status' => SyncTask::STATUS_WORKING
                        ], ['id' => $task['id']])->execute();
                    } catch (\Exception $e) {
                        $this->stderr('[ ' . date('Y-m-d H:i:s') . " ] Start process failed." . PHP_EOL);
                    }
                }

                $n = count($processes);
                while ($n >= 5) {
                    foreach ($processes as $i => $item) {
                        /* @var $process Process */
                        $process = $item['process'];
                        if ($process->isTerminated()) {
                            unset($processes[$i]);
                            $cmd->delete('{{%g_sync_task}}', ['id' => $item['taskId']])->execute();
                        } elseif ((time() - $item['startDatetime']) >= self::TIMEOUT_SECONDS) {
                            $cmd->update('{{%g_sync_task}}', ['status' => SyncTask::STATUS_PENDING, 'start_datetime' => null], ['id' => $item['taskId']])->execute();
                            $process->stop(1);
                        }
                    }
                    $n = count($processes);
                }
                $n = 1;
                while ($n <= 100) {
                    foreach ($processes as $i => $item) {
                        /* @var $process Process */
                        $process = $item['process'];
                        if ($process->isRunning()) {
                            if ($s = $process->getIncrementalOutput()) {
                                $n++;
                                $this->stdout($s);
                            }
                        }
                    }
                }
            } else {
                if (in_array(date('G'), [1, 3, 9, 13, 19])) {
                    // 只有在指定的时间段才会生成拉取任务
                    $rows = [];
                    $shopIds = $db->createCommand('SELECT [[id]] FROM {{%g_shop}} WHERE [[enabled]] = :enabled', [
                        ':enabled' => Constant::BOOLEAN_TRUE,
                    ])->queryColumn();
                    $datetime = (new DateTime())->setTime(0, 0, 0);
                    $endDate = $datetime->getTimestamp();
                    $beginDate = $datetime->modify('-1 days')->getTimestamp();
                    $retryTaskIds = [];
                    foreach ($shopIds as $shopId) {
                        $taskId = $db->createCommand('SELECT [[id]] FROM {{%g_sync_task}} WHERE [[shop_id]] = :shopId', [
                            ':shopId' => $shopId
                        ])->queryScalar();
                        if ($taskId) {
                            $retryTaskIds[] = $taskId;
                        } else {
                            $rows[] = [
                                'shop_id' => $shopId,
                                'begin_date' => $beginDate,
                                'end_date' => $endDate,
                                'priority' => 10,
                                'status' => SyncTask::STATUS_PENDING,
                            ];
                        }
                    }
                    $rows && $cmd->batchInsert('{{%g_sync_task}}', array_keys($rows[0]), $rows)->execute();
                    $retryTaskIds && $cmd->update('{{%g_sync_task}}', ['status' => SyncTask::STATUS_PENDING], ['id' => $retryTaskIds])->execute();
                } else {
                    sleep(60);
                }
            }
        }

        $this->stdout("Done." . PHP_EOL);
    }

    /**
     * 没有运单号的任务添加
     *
     * @throws \yii\db\Exception
     */
    public function actionAddTasks()
    {
        $this->stdout('Begin...');
        $tasks = [];
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $rows = $db->createCommand(<<<EOT
SELECT [[t.id]], [[o.id]] AS [[order_id]], [[t.shop_id]], [[o.place_order_at]], [[o.payment_at]]
FROM {{%g_package}} t
LEFT JOIN {{%g_package_order_item}} poi ON [[t.id]] = [[poi.package_id]]
LEFT JOIN {{%g_order}} o ON [[poi.order_id]] = [[o.id]]
WHERE [[t.waybill_number]] IS NULL OR [[t.waybill_number]] = ''
ORDER BY [[o.place_order_at]] ASC
EOT
        )->queryAll();
        foreach ($rows as $row) {
            $this->stdout("Package #{$row['id']} " . PHP_EOL);
            $d = $row['place_order_at'];
            if ($d) {
                $shopId = $row['shop_id'];
                $date = (new DateTime())->setTimestamp($d)->setTime(0, 0, 0)->getTimestamp();
                if (!isset($tasks[$shopId])) {
                    $tasks[$shopId] = [
                        'shop_id' => $shopId,
                        'begin_date' => $date,
                        'end_date' => $date,
                        'priority' => 9,
                        'status' => SyncTask::STATUS_PENDING,
                    ];
                } else {
                    $tasks[$shopId]['end_date'] = $date;
                }
            }
        }
        if ($tasks) {
            $updateRows = [];
            $histories = $db->createCommand('SELECT [[id]], [[shop_id]], [[begin_date]], [[end_date]] FROM {{%g_sync_task}} WHERE [[status]] = :status', [
                ':status' => SyncTask::STATUS_PENDING,
            ])->queryAll();
            if ($histories) {
                foreach ($tasks as $shopId => $task) {
                    foreach ($histories as $history) {
                        if ($history['shop_id'] == $shopId) {
                            if ($history['begin_date'] == $task['begin_date'] && $history['end_date'] == $task['end_date']) {
                                unset($tasks[$shopId]);
                                break;
                            } else {
                                if ($history['begin_date'] > $task['begin_date']) {
                                    $updateRows[$shopId]['begin_date'] = $task['begin_date'];
                                }
                                if ($history['end_date'] < $task['end_date']) {
                                    $updateRows[$shopId]['end_date'] = $task['end_date'];
                                }
                                if (isset($updateRows[$shopId])) {
                                    $updateRows[$shopId]['id'] = $history['id'];
                                    unset($tasks[$shopId]);
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if ($tasks) {
                $tasks = array_values($tasks);
                $n = $cmd->batchInsert('{{%g_sync_task}}', array_keys($tasks[0]), $tasks)->execute();
                $this->stdout("Add $n tasks." . PHP_EOL);
            }
            if ($updateRows) {
                foreach ($updateRows as $column) {
                    $id = $column['id'];
                    unset($column['id']);
                    $cmd->update('{{%g_sync_task}}', $column, ['id' => $id])->execute();
                }
            }
        }
        $this->stdout("Done." . PHP_EOL);
    }

}