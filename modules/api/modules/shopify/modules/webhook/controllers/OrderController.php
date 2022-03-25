<?php

namespace app\modules\api\modules\shopify\modules\webhook\controllers;

use app\models\Constant;
use app\modules\api\modules\g\models\Order;
use app\modules\api\modules\g\models\OrderItem;
use app\modules\api\modules\g\models\Package;
use app\modules\api\modules\g\models\PackageOrderItem;
use app\modules\api\modules\g\models\Shop;
use Exception;
use yadjet\helpers\ImageHelper;
use Yii;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use function Symfony\Component\String\u;

/**
 * Order webhook
 *
 * @package app\modules\api\modules\shopify\modules\webhook\controllers
 * @see Webhook https://shopify.dev/docs/admin-api/rest/reference/events/webhook?api[version]=2019-10
 * @see Order API https://shopify.dev/docs/admin-api/rest/reference/orders/order?api[version]=2019-10
 */
class OrderController extends Controller
{

    /**
     * @var string Shopify 订单主键值
     */
    private $key;

    /**
     * @var string 订单号
     */
    private $number;

    /**
     * @var array 订单数据
     */
    private $orderPayload = [];

    /**
     * @var array
     */
    private $orderStatusOptions = [
        'authorized' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_AUTHORIZED, // 已授权
        'paid' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_PAID, //　已付费
        'partially_refunded' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_PARTIALLY_PARTIALLY_REFUNDED, // 部分退款
        'partially_paid' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_PARTIALLY_PAID, // 部分付款
        'pending' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_PENDING, // 待定
        'refunded' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_PARTIALLY_REFUNDED, // 已退款
        'unpaid' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_UNPAID, // 未付款
        'voided' => Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_VOIDED, // 无效
    ];

    /**
     * 图片下载
     *
     * @param $path
     * @param null $filename
     * @return string
     * @throws \yii\base\Exception
     */
    private function downloadImage($path, $filename = null)
    {
        $res = "";
        if ($filename) {
            $s = u($filename)->collapseWhitespace()->snake()->lower()->trim()->toString();
            $s = str_replace(['_', '-', '@', '#', '$', ' ', '　', '*'], '', $s);
        } else {
            $filename = uniqid();
            $s = $filename;
        }

        $n = strlen($s);
        $dir1 = substr($s, 0, $n >= 2 ? 2 : $n);
        $imgPath = "/uploads/items/$dir1";
        if ($n > 2) {
            $imgPath .= '/' . trim(substr($s, 2, ($n - 2) > 2 ? 2 : 1));
        }
        $imgName = $filename . ImageHelper::getExtension($path, true);
        $savePath = FileHelper::normalizePath(Yii::getAlias('@webroot') . $imgPath);
        if (file_exists($savePath . DIRECTORY_SEPARATOR . $imgName)) {
            $res = "$imgPath/$imgName";
        } else {
            if (!file_exists($savePath)) {
                FileHelper::createDirectory($savePath);
            }
            try {
                $size = file_put_contents($savePath . DIRECTORY_SEPARATOR . $imgName, file_get_contents($path));
                if ($size !== false) {
                    $res = "$imgPath/$imgName";
                }
            } catch (Exception $e) {
            }
        }

        return $res;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $payload = $this->payload;
            $db = Yii::$app->getDb();
            $this->key = isset($payload['id']) ? (string) $payload['id'] : null;
            $this->number = isset($payload['number']) ? (string) $payload['number'] : null;
            if (empty($this->key) || empty($this->number)) {
                throw new BadRequestHttpException("Bad order request.");
            }
            // 根据国家简称获取国家
            $countryId = $db->createCommand("SELECT [[id]] FROM {{%g_country}} WHERE [[abbreviation]] = :abbreviation", [':abbreviation' => $payload['shipping_address']['country_code']])->queryScalar();
            $countryId || $countryId = 0;

            $consigneeName = $payload['shipping_address']['first_name'] ?? '';
            $lastName = $payload['shipping_address']['last_name'] ?? '';
            $consigneeName .= ($lastName ? ' ' : '') . $lastName;
            $this->orderPayload = [
                'key' => $this->key,
                'number' => $this->number,
                'consignee_name' => $consigneeName,
                'consignee_mobile_phone' => '',
                'consignee_tel' => $payload['shipping_address']['phone'] ?? null,
                'country_id' => $countryId,
                'consignee_state' => $payload['shipping_address']['province'] ?? null,
                'consignee_city' => $payload['shipping_address']['city'] ?? null,
                'consignee_address1' => $payload['shipping_address']['address1'] ?? null,
                'consignee_address2' => $payload['shipping_address']['address2'] ?? null,
                'consignee_postcode' => $payload['shipping_address']['zip'] ?? null,
                'total_amount' => $payload['total_price'],
                'third_party_platform_id' => Constant::THIRD_PARTY_PLATFORM_SHOPIFY,
                'third_party_platform_status' => $this->orderStatusOptions[$payload['financial_status']] ?? Constant::THIRD_PARTY_PLATFORM_FINANCIAL_STATUS_UNKNOWN,
                'platform_id' => Constant::PLATFORM_SHOPIFY,
                'status' => $payload['financial_status'] == 'paid' ? Order::STATUS_PENDING : Order::STATUS_INVALID,
                'shop_id' => $this->shopId,
                'product_type' => Shop::PRODUCT_TYPE_CUSTOMIZED,
                'place_order_at' => strtotime($payload['created_at']),
                'payment_at' => strtotime($payload['created_at']),
                'cancelled_at' => $payload['cancelled_at'] ? strtotime($payload['cancelled_at']) : null,
                'cancel_reason' => $payload['cancel_reason'] ?? null,
                'closed_at' => $payload['closed_at'] ? strtotime($payload['closed_at']) : null,
                'items' => [],
            ];

            $lineItems = $payload['line_items'] ?? [];
            foreach ($lineItems as $key => $lineItem) {
                $lineItem['properties'][] = [
                    'name' => 'variants',
                    'value' => $lineItem['variant_title']
                ];
                $extend = $this->parseExtend($lineItem['properties']);
                // 是否忽略
                $ignored = false;
                $variants = $extend['raw']['variants'] ?? null;
                if ($variants == 'checked') {
                    $ignored = true;
                }
                $sku = $lineItem['sku'] ?? null;
                $item = [
                    'sku' => $sku,
                    'key' => $lineItem['id'] ?? null,
                    'product_name' => $lineItem['title'] ?? null,
                    'quantity' => $lineItem['quantity'] ?? 0,
                    'extend' => $extend,
                    'ignored' => $ignored,
                    'sale_price' => $lineItem['price'] ?? 0,
                ];
                if ($sku) {
                    $product = $db->createCommand("SELECT [[id]], [[image]] FROM {{%g_product}} WHERE [[key]] = :key", [':key' => $lineItem['product_id']])->queryOne();
                    if ($product) {
                        $item['product_id'] = $product['id'];
                        $item['image'] = $product['image'];
                    }
                }
                $this->orderPayload['items'][] = $item;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 解析extend数据
     *
     * @param $properties
     * @return array
     * @throws \yii\base\Exception
     */
    function parseExtend($properties)
    {
        $json = [
            'names' => [],
            'color' => '',
            'material' => '',
            'size' => '',
            'giftBox' => false,
            'beads' => 0,
            'image' => '',
            'other' => [],
            'raw' => [],
        ];
        $variantsOther = [];
        $delimiter = ':';
        foreach ($properties as $property) {
            $property = implode(':', $property);
            $s = u($property)->trim();
            if (!$s->isEmpty() && !$s->startsWith("_") && $s->containsAny($delimiter)) {
                list($key, $value) = $s->split($delimiter, 2);
                $key = $key->trim()->lower();
                $value = $value->trim();
                $json['raw'][$key->toString()] = $value->toString();
                if ($key->containsAny(['giftbox', 'gift box'])) {
                    $json['giftBox'] = true;
                } elseif ($key->containsAny(['number', 'number of', '#'])) {
                    $a = $value->match('/([\d])+/');
                    isset($a[1]) && $json['beads'] = $a['1'];
                } elseif ($key->containsAny(['please write', 'name', 'charm', 'inscription', 'initial of star']) && !$key->containsAny([
                        'add leaf charm',
                        'add leaf charms',
                    ])) {
                    $json['names'] = array_merge($json['names'], array_map('trim', $value->split(",")));
                } elseif ($key->containsAny(['chain length'])) {
                    // select chain length
                    $json['size'] = $value->toString();
                } elseif ($key->containsAny(['upload', 'photo'])) {
                    $imgPath = $this->downloadImage($value->toString(), md5($value->toString()));
                    $imgPath || $imgPath = $value->toString();
                    $json['image'] = $imgPath;
                } elseif ($key->containsAny(['variants'])) {
                    $variants = [];
                    $materials = [];
                    $colors = [];
                    $sizes = [];
                    foreach ($value->split('/') as $variant) {
                        $v = u($variant)->trim();
                        $variant = $v->toString();
                        $variants[] = $variant;
                        $v = $v->lower();

                        if ($v->containsAny(['beads', 'bead'])) {
                            $a = $value->match('/([\d])+/');
                            isset($a[1]) && $json['beads'] = $a['1'];
                        } elseif ($v->containsAny(['sterling silver', 'silver plat', 'k gold'])) {
                            $materials[] = $variant;
                        } elseif ($v->containsAny(['size', 'cm', 'inch', 'adjustable', 'diameter', "''"])) {
                            $sizes[] = $variant;
                        } elseif ($v->containsAny(['silver', 'gold', 'black'])) {
                            $colors[] = $variant;
                        } else {
                            $variantsOther = [$v->toString()];
                        }
                    }
                    $materials && $json['material'] = implode(' / ', $materials);
                    $colors && $json['color'] = implode(' / ', $colors);
                    if ($sizes) {
                        $s = $json['size'];
                        $s && $s .= ' / ';
                        $json['size'] = $s . implode(' / ', $sizes);
                    }
                } else {
                    $variantsOther[] = $key->toString() . ':' . $value->toString();
                }
            }
        }
        if (!$json['beads'] && $json['names']) {
            $json['beads'] = count($json['names']);
        }
        $json['other'] = $variantsOther;

        return $json;
    }

    /**
     * 创建
     *
     * @return Order
     * @throws \Throwable
     */
    public function actionCreation()
    {
        $model = null;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $success = true;
            $order = new Order();
            $order->load($this->orderPayload, '');
            $orderItemIdList = [];
            if ($success = $order->save()) {
                $model = $order;
                foreach ($this->orderPayload['items'] as $item) {
                    $item['order_id'] = $order->id;
                    $orderItem = new OrderItem();
                    $orderItem->load($item, '');
                    if ($success = $orderItem->save()) {
                        $orderItemIdList[] = $orderItem->id;
                    } else {
                        $model = $orderItem;
                        if (!$orderItem->hasErrors()) {
                            throw new ServerErrorHttpException('Failed to update the object for unknown reason.' . var_export($orderItem->getErrors(), true));
                        }
                        break;
                    }
                }
                if ($success) {
                    $package = new Package();
                    $package->loadDefaultValues();
                    $package->number = Package::generateNumber();
                    $package->shop_id = $order->shop_id;
                    $package->third_party_platform_id = $order->third_party_platform_id;
                    $package->country_id = $order->country_id;
                    if ($success = $package->save()) {
                        foreach ($orderItemIdList as $orderItemId) {
                            $orderItem = new PackageOrderItem();
                            $orderItem->package_id = $package->id;
                            $orderItem->order_id = $order->id;
                            $orderItem->order_item_id = $orderItemId;
                            if (!($success = $orderItem->save())) {
                                $model = $orderItem;
                                if (!$orderItem->hasErrors()) {
                                    throw new ServerErrorHttpException('Failed to update the object for unknown reason.' . var_export($orderItem->getErrors(), true));
                                }
                                break;
                            }
                        }
                    } else {
                        $model = $package;
                    }
                }
            } elseif (!$order->hasErrors()) {
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.' . var_export($order->getErrors(), true));
            } else {
                $model = $order;
            }

            $success ? $transaction->commit() : $transaction->rollBack();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $model;
    }

    /**
     * 修改
     *
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionUpdate()
    {
        $order = $this->findModel();
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $order->load($this->orderPayload, '');
            if ($order->save()) {
                $orderItems = OrderItem::find()->where(['order_id' => $order->id])->all();
                $newSkuList = [];
                foreach ($this->orderPayload['items'] as $item) {
                    $newSkuList[] = trim($item['sku']);
                    $model = null;
                    foreach ($orderItems as $orderItem) {
                        /* @var $orderItem OrderItem */
                        if ($orderItem->sku == $item['sku']) {
                            $model = $orderItem;
                            break;
                        }
                    }
                    if ($model === null) {
                        $model = new OrderItem();
                        $model->loadDefaultValues();
                    }
                    $model->load($item, '');
                    if ($model->save() === false) {
                        throw new ServerErrorHttpException('Failed to update the object.' . var_export($model->getErrors(), true));
                    }
                }

                if ($newSkuList) {
                    /* @var $orderItem OrderItem */
                    foreach ($orderItems as $orderItem) {
                        if (!in_array($orderItem->sku, $newSkuList)) {
                            $orderItem->delete();
                        }
                    }
                }
            } else {
                throw new ServerErrorHttpException('Failed to create the object.' . var_export($order->getErrors(), true));
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $order;
    }

    /**
     * 跟踪信息
     *
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionFulfillment()
    {
        // @todo
    }

    /**
     * 支付结果
     *
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionPayment()
    {
        $model = $this->findModel();
        if (in_array($model->status, [Order::STATUS_INVALID, Order::STATUS_FAILURE])) {
            $model->status = Order::STATUS_PENDING;
            $model->save();
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * 取消订单
     *
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionCancellation()
    {
        $model = $this->findModel();
        $model->status = Order::STATUS_INVALID;
        $model->cancelled_at = $this->orderPayload['cancelled_at'];
        $model->cancel_reason = $this->orderPayload['cancel_reason'];
        if ($model->save()) {
            $packages = $this->findPackages($model->id);
            foreach ($packages as $package) {
                /* @var Package $package */
                $package->status = Package::STATUS_CLOSED;
                $package->save();
            }
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * 删除订单
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionDeletion()
    {
        $model = $this->findModel();
        $model->status = Order::STATUS_INVALID;
        if ($model->save()) {
            $packages = $this->findPackages($model->id);
            foreach ($packages as $package) {
                /* @var Package $package */
                $package->status = Package::STATUS_CLOSED;
                $package->save();
            }
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @return Order|\yii\db\ActiveRecord|null
     * @throws NotFoundHttpException
     */
    public function findModel()
    {
        $model = Order::find()->where(['key' => $this->key])->one();
        if ($model === null) {
            throw new NotFoundHttpException("Not found $this->number [ #{$this->key} ] order.");
        }

        return $model;
    }

    /**
     * 订单包裹
     *
     * @param $orderId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findPackages($orderId)
    {
        return Package::find()
            ->where(['IN', 'id', (new Query())
                ->from('{{%g_package_order_item}}')
                ->where(['order_id' => (int) $orderId])
            ])
            ->all();
    }

}