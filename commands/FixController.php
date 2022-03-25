<?php

namespace app\commands;

use app\models\Constant;
use app\modules\admin\modules\g\models\Shop;
use Yii;
use yii\helpers\Console;
use function Symfony\Component\String\u;

/**
 * 数据修正脚本
 *
 * @package app\commands
 */
class FixController extends Controller
{

    /**
     * 包裹数据修正
     *
     * 原来的 wuliu Package 使用
     *
     * @throws \yii\db\Exception
     */
    public function actionGPackage()
    {
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $packages = $db->createCommand('SELECT [[id]], [[package_number]], [[weight]] FROM {{%wuliu_package}} WHERE [[weight]] > 0')->queryAll();
        foreach ($packages as $package) {
            $this->stdout("Package {$package['package_number']}...");
            $gPackageId = $db->createCommand('SELECT [[id]] FROM {{%g_package}} WHERE [[number]] = :number', [':number' => $package['package_number']])->queryScalar();
            if ($gPackageId) {
                $n = $cmd->update('{{%g_package}}', ['weight' => $package['weight']], ['id' => $gPackageId])->execute();
                $res = $n ? 'OK' : "Failed";
            } else {
                $res = 'Not found';
            }
            $this->stdout($res . PHP_EOL);
        }
        $this->stdout("Done.");
    }

    /**
     * 供应商数据修正
     *
     * @throws \yii\db\Exception
     */
    public function actionVendor()
    {
        $this->stdout("Begin..." . PHP_EOL);
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $items = [];
        $rows = $db->createCommand("SELECT [[order_item_id]], [[vendor_id]] FROM {{%om_order_item_route}} ORDER BY [[id]] ASC")->queryAll();
        foreach ($rows as $row) {
            $items[$row['order_item_id']] = $row['vendor_id'];
        }
        foreach ($items as $key => $value) {
            $this->stdout("Update #$key" . PHP_EOL);
            $cmd->update('{{%g_order_item}}', ['vendor_id' => $value], ['id' => $key, 'vendor_id' => 0])->execute();
        }
        $this->stdout("Done." . PHP_EOL);
    }

    /**
     * 订单项目成本价数据修正
     *
     * @throws \yii\db\Exception
     */
    public function actionCostPrice()
    {
        $this->stdout("Begin..." . PHP_EOL);
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $items = [];
        $rows = $db->createCommand("SELECT [[order_item_id]], [[cost_price]] FROM {{%om_order_item_route}} WHERE [[current_node]] NOT IN (3, 10) ORDER BY [[id]] ASC")->queryAll();
        foreach ($rows as $row) {
            $items[$row['order_item_id']] = $row['cost_price'];
        }
        foreach ($items as $key => $value) {
            $this->stdout("Update #$key" . PHP_EOL);
            $cmd->update('{{%g_order_item}}', ['cost_price' => $value], ['id' => $key, 'cost_price' => 0])->execute();
        }
        $this->stdout("Done." . PHP_EOL);
    }

    /**
     * 处理订单忽略项目
     *
     * @throws \yii\db\Exception
     */
    public function actionOrderItemIgnored()
    {
        $perPage = 1000;
        $this->stdout("Begin..." . PHP_EOL);
        $i = 0;
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        $maxPkValue = 0;
        while (true) {
            $rows = $db->createCommand("SELECT [[id]], [[order_id]], [[extend]] FROM {{%g_order_item}} WHERE [[id]] > :maxPkValue AND [[ignored]] = :ignored AND [[order_id]] IN (SELECT [[id]] FROM {{%g_order}} WHERE [[product_type]] = :productType) LIMIT :limit", [
                ':maxPkValue' => $maxPkValue,
                ':ignored' => Constant::BOOLEAN_FALSE,
                ':productType' => Shop::PRODUCT_TYPE_CUSTOMIZED,
                ':limit' => $perPage,
            ])->queryAll();
            foreach ($rows as $row) {
                $maxPkValue = $row['id'];
                $i++;
                $this->stdout("# $i ID: $maxPkValue OrderId: {$row['order_id']} Ignored: ");
                $ignored = false;
                $extend = json_decode($row['extend'], true);
                if ($extend) {
                    $variants = $extend['raw']['variants'] ?? null;
                    $variants === null && $variants = $extend['raw']['Variants'] ?? null;
                    if ($variants == 'checked') {
                        $ignored = true;
                    }
                }
                if (!$ignored) {
                    $raw = $extend['raw'];
                    (empty($raw) || !is_array($raw)) && $raw = [];
                    if (count($raw) == 1 && (isset($raw['variants']) || isset($raw['Variants']))) {
                        // 只有一个项目
                        $variants = $raw['variants'] ?? null;
                        $variants || $variants = $raw['Variants'] ?? null;
                        if ($variants) {
                            $variants = u($variants)->collapseWhitespace()->toString();
                            $orderItems = $db->createCommand('SELECT [[id]], [[order_id]], [[extend]], [[ignored]] FROM {{%g_order_item}} WHERE [[order_id]] = :orderId AND [[id]] != :id', [
                                ':orderId' => $row['order_id'],
                                ':id' => $row['id']
                            ])->queryAll();
                            foreach ($orderItems as $d) {
                                $dExtend = json_decode($d['extend'], true);
                                $dRaw = $dExtend['raw'];
                                if (empty($dRaw) || !is_array($dRaw)) {
                                    $dRaw = [];
                                }
                                if (count($dRaw) > 1) {
                                    foreach ($dRaw as $v) {
                                        $v = u($v)->collapseWhitespace()->toString();
                                        if ($v == $variants || $v == "DAD+$variants") {
                                            $ignored = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($ignored) {
                    $this->stdout(Console::ansiFormat(' Y ', [Console::BG_RED]) . PHP_EOL);
                    $cmd->update('{{%g_order_item}}', ['ignored' => Constant::BOOLEAN_TRUE], ['id' => $row['id']])->execute();
                } else {
                    $this->stdout(Console::ansiFormat(' N ', [Console::BG_GREEN]) . PHP_EOL);
                }
            }
            if (!isset($rows[$perPage - 1])) {
                break;
            }
        }

        $this->stdout("Done." . PHP_EOL);
    }

}
