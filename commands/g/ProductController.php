<?php

namespace app\commands\g;

use app\commands\Controller;
use app\models\Constant;
use app\modules\admin\modules\g\models\Product;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use yadjet\helpers\ImageHelper;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use function Symfony\Component\String\u;

/**
 * 获取商品数据
 *
 * @package app\commands\g
 */
class ProductController extends Controller
{

    /**
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $this->stdout('Begin...' . PHP_EOL);
        $db = \Yii::$app->getDb();
        $items = [];
        $accounts = $db->createCommand('SELECT [[authentication_config]] FROM {{%g_third_party_authentication}} WHERE [[platform_id]] = :platformId AND [[enabled]] = :enabled', [
            ':platformId' => Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI,
            ':enabled' => Constant::BOOLEAN_TRUE,
        ])
            ->queryAll();
        foreach ($accounts as $account) {
            $config = json_decode($account['authentication_config'], true);
            if ($config && isset($config['dianxiaomi'])) {
                $config = $config['dianxiaomi'];
                if (($username = $config['username'] ?? null) && ($cookie = $config['cookie'] ?? null)) {
                    $cookies = [];
                    foreach (explode(';', $cookie) as $item) {
                        try {
                            list($key, $value) = explode('=', $item);
                            $key = trim($key);
                            $value = trim($value);
                            $cookies[$key] = $value;
                        } catch (\Exception $e) {
                            $cookies = [];
                        }
                    }
                    $cookies && $items[$username] = $cookies;
                }
            } else {
                $this->stderr(" > Bad config." . PHP_EOL);
            }
        }
        foreach ($items as $username => $cookie) {
            $client = new Client([
                'headers' => [
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'host' => 'www.xxx.com',
                    'Referer' => 'https://www.xxx.com/order/index.htm',
                ],
                'cookies' => CookieJar::fromArray($cookie, 'www.xxx.com'),
            ]);
            $url = "https://www.xxx.com/dxmCommodityProduct/pageList.htm";
            $page = 1;
            $totalPages = 1;
            while (true) {
                $response = $client->post($url, [
                    'form_params' => [
                        'pageNo' => $page,
                        'pageSize' => 100,
                    ]
                ]);
                if ($response->getStatusCode() == 200) {
                    $html = $response->getBody()->getContents();
                    $crawler = new Crawler();
                    $crawler->addHtmlContent($html);

                    if ($totalPages == 1) {
                        $subCrawler = $crawler->filter('#totalPage');
                        if ($subCrawler->count()) {
                            $totalPages = $subCrawler->attr('value');
                        }
                    }

                    $crawler->filter('table > tbody#goodsBody > tr.content')->each(function (Crawler $node) use ($client, $username, $page, $totalPages) {
                        $subCrawler = $node->filter('input[name="productBox"]');
                        $id = $subCrawler->count() ? $subCrawler->attr('value') : 0;
                        $this->stdout("Account: $username [ Page $page/$totalPages #$id ]");
                        if ($id) {
                            $detailResp = $client->post("https://www.xxx.com/dxmCommodityProduct/viewDxmCommodityProduct.json", [
                                'form_params' => [
                                    'id' => $id,
                                ]
                            ]);
                            if ($detailResp->getStatusCode() == 200) {
                                $json = json_decode($detailResp->getBody()->getContents(), true);
                                if ($json && ($sku = $json['productDTO']['dxmCommodityProduct']['sku'] ?? null)) {
                                    $payload = [
                                        'chinese_name' => $json['productDTO']['dxmCommodityProduct']['name'] ?? null,
                                        'english_name' => $json['productDTO']['dxmCommodityProduct']['nameEn'] ?? null,
                                        'weight' => $json['productDTO']['dxmCommodityProduct']['weight'] ?? null,
                                        'price' => $json['productDTO']['dxmCommodityProduct']['price'] ?? null,
                                    ];
                                    $imageOriginalUrl = $json['productDTO']['dxmCommodityProduct']['imgUrl'] ?? null;
                                    if ($imageOriginalUrl) {
                                        if (stripos($imageOriginalUrl, 'http') === false) {
                                            $imageOriginalUrl = "https://productimage-1251220924.picgz.myqcloud.com/$imageOriginalUrl";
                                        }

                                        $filename = u($sku)->lower()->snake();
                                        $payload['key'] = $filename->collapseWhitespace()->toString();

                                        $dirs = $filename->chunk(2);
                                        $imgUrl = "/uploads/items";
                                        if (isset($dirs[0])) {
                                            $imgUrl .= "/{$dirs[0]}";
                                            isset($dirs[1]) && $imgUrl .= "/{$dirs[1]}";
                                        }
                                        $imgName = $filename->toString() . ImageHelper::getExtension($imageOriginalUrl, true);
                                        $saveDir = FileHelper::normalizePath(Yii::getAlias('@webroot') . $imgUrl);
                                        if (file_exists($saveDir . DIRECTORY_SEPARATOR . $imgName)) {
                                            $payload['image'] = "$imgUrl/$imgName";
                                        } else {
                                            if (!file_exists($saveDir)) {
                                                FileHelper::createDirectory($saveDir);
                                            }

                                            Console::stdout(" > Download $imageOriginalUrl image...");
                                            try {
                                                $size = file_put_contents($saveDir . DIRECTORY_SEPARATOR . $imgName, file_get_contents($imageOriginalUrl));
                                                if ($size !== false) {
                                                    $payload['image'] = "$imgUrl/$imgName";
                                                    Console::stderr(" [ Successful ]" . PHP_EOL);
                                                } else {
                                                    Console::stderr(" [ Failed ]" . PHP_EOL);
                                                }
                                            } catch (Exception $e) {
                                                Console::stderr($e->getMessage() . PHP_EOL);
                                            }
                                        }
                                    }
                                    $payload['price'] = floatval($payload['price']);
                                    $payload['weight'] = intval($payload['weight']);
                                    $model = Product::find()->where(['sku' => $sku])->one();
                                    if ($model === null) {
                                        $model = new Product();
                                        $model->loadDefaultValues();
                                        $payload['sku'] = $sku;
                                    }
                                    $model->load($payload, '');
                                    if (empty($model->chinese_name) && empty($model->english_name)) {
                                        $model->chinese_name = $model->english_name = $model->sku;
                                    } elseif (empty($model->chinese_name)) {
                                        $model->chinese_name = $model->english_name;
                                    } elseif (empty($model->english_name)) {
                                        $model->english_name = $model->chinese_name;
                                    }
                                    if ($model->save()) {
                                        $this->stdout(' [' . ($model->getIsNewRecord() ? ' INSERT ' : ' UPDATE ') . "Successful ] " . PHP_EOL);
                                    } else {
                                        $this->stdout(PHP_EOL);
                                        $this->stdout(u(' Error ')->padBoth(80, '#') . PHP_EOL);
                                        $this->stdout("原始数据：" . var_export($model->toArray(), true));
                                        foreach ($model->getErrors() as $err) {
                                            foreach ($err as $e) {
                                                $this->stderr(" > $e" . PHP_EOL);
                                            }
                                        }
                                    }
                                }
                            } else {
                                $this->stdout(" #$id HTTP " . $detailResp->getStatusCode() . ' Error' . PHP_EOL);
                            }
                        }
                    });
                } else {
                    $this->stderr("Account: $username get page #{$page} HTTP " . $response->getStatusCode() . ' Error' . PHP_EOL);
                }
                $page++;
                if ($page > $totalPages) {
                    break;
                }
            }
        }

        $this->stdout("Done!" . PHP_EOL);
    }

}