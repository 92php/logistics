<?php

namespace app\commands\g;

use app\commands\Controller;
use app\modules\admin\modules\g\models\Country;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * 获取所有国家及国家代码
 *
 * @package app\commands\g
 */
class CountryController extends Controller
{

    public function actionIndex()
    {
        $client = new Client(['headers' => [
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
        ]]);
        $response = $client->get('https://yumingsuoxie.51240.com/');

        $crawler = new Crawler();
        $crawler->addHtmlContent($response->getBody()->getContents());

        $crawler->filterXPath('//table[2]//tr')->each(function ($node) {
            /* @var $node Crawler */
            if (!$node->filterXPath('node()')->attr('bgcolor')) {
                $code = $node->filterXPath('node()/td[1]');
                $code = $code->count() ? $code->text() : '';
                $name = $node->filterXPath('node()/td[2]');
                $name = $name->count() ? $name->text() : '';
                $englishName = $node->filterXPath('node()/td[3]');
                $englishName = $englishName->count() ? $englishName->text() : '';
                $regionId = 0;
                if ($code && $name && $englishName) {
                    if ($country = Country::findOne(['abbreviation' => $code])) {
                        $this->stdout("Country Abbreviation " . $code . " Already Exists." . PHP_EOL);
                    } else {
                        $countryLoad = [
                            'abbreviation' => $code,
                            'chinese_name' => $name,
                            'english_name' => $englishName,
                            'region_id' => $regionId,
                        ];
                        $country = new Country();
                        $country->load($countryLoad, '');
                        if (!$country->save()) {
                            $this->stdout("Country " . $name . "Save Failed With Error:" . var_export($country->getErrors(), true) . PHP_EOL);
                        } else {
                            $this->stdout("Country " . $name . "Saved. " . PHP_EOL);
                        }
                    }
                }
            }
        });

        $this->stdout("Done!");
    }
}