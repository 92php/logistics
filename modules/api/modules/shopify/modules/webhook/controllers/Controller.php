<?php

namespace app\modules\api\modules\shopify\modules\webhook\controllers;

use app\modules\api\extensions\BaseController;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * shopify webhook base class.
 *
 * @package app\modules\api\modules\shopify\modules\webhook\controller
 */
class Controller extends BaseController
{

    /**
     * @var int Shop name
     */
    protected $shopId = 0;

    /**
     * @var array webhooks post payload
     */
    protected $payload;

    /**
     * @param \yii\base\Action $action
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
            $request = Yii::$app->getRequest();
            $headers = $request->getHeaders();
            $this->payload = $request->post();
            isset($headers['X-Shopify-Test']) && $headers['X-Shopify-Test'] == 'true' && file_put_contents(Yii::getAlias('@runtime/logs/' . $action->id . '.log'), var_export($this->payload, true));
            $headerKeys = ['X-Shopify-Topic', 'X-Shopify-Hmac-Sha256', 'X-Shopify-Shop-Domain', 'X-Shopify-API-Version'];
            foreach ($headerKeys as $key) {
                if (!isset($headers[$key])) {
                    throw new BadRequestHttpException("Bad request, missed `$key` value, may be not come from shopify.");
                }
            }

            list($shopName,) = explode('.', $headers['X-Shopify-Shop-Domain']);
            $shop = Yii::$app->getDb()->createCommand("
SELECT [[t.id]], [[tpa.authentication_config]]
FROM {{%g_shop}} t
LEFT JOIN {{%g_third_party_authentication}} tpa ON [[t.third_party_authentication_id]] = [[tpa.id]]
WHERE [[t.name]] = :name
", [
                ':name' => $shopName,
            ])->queryOne();

            if (!$shop) {
                throw new NotFoundHttpException("Not found `{$headers['X-Shopify-Shop-Domain']}` in shops.");
            }
            $this->shopId = $shop['id'];
            $config = json_decode($shop['authentication_config'], true);
            $webhooksSharedSecret = $config['api']['webhooks_shared_secret'] ?? null;
            if (empty($webhooksSharedSecret)) {
                Yii::error("Not found `webhooks_shared_secret` config value in $shopName shopify shop.");
                throw new BadRequestHttpException("Not found `webhooks_shared_secret` config value in $shopName shopify shop.");
            }

            $hmac = $headers['X-Shopify-Hmac-Sha256'];
            $calculatedHmac = base64_encode(hash_hmac('sha256', file_get_contents('php://input'), $webhooksSharedSecret, true));
            if (!hash_equals($hmac, $calculatedHmac)) {
                throw new BadRequestHttpException("X-Shopify-Hmac-Sha256 value `$hmac` is invalid.");
            }

            return true;
        } else {
            return false;
        }
    }

}