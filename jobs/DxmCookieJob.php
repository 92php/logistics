<?php

namespace app\jobs;

use app\models\Constant;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use GuzzleHttp\Client;
use Yii;
use yii\queue\JobInterface;

/**
 * cookie获取任务
 *
 * @package app\jobs
 */
class DxmCookieJob extends Job implements JobInterface
{

    /**
     * @var int Dxm Account id
     */
    public $id;

    /**
     * @var string captcha file name
     */
    public $filename;

    public function init()
    {
        $this->filename = Yii::getAlias("@runtime/logs/") . '/captcha.png';
    }

    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function execute($queue)
    {
        $db = Yii::$app->db;
        $dxmAccount = $db->createCommand("SELECT [[username]], [[password]] FROM {{%wuliu_dxm_account}} WHERE [[id]] = :id", [':id' => $this->id])->queryOne();
        if ($dxmAccount) {
            $cookies = $this->getCookies($dxmAccount['username'], $dxmAccount['password']);
            if ($cookies) {
                $db->createCommand()->update('{{%wuliu_dxm_account}}', ['cookies' => $cookies, 'is_valid' => Constant::BOOLEAN_TRUE], ['id' => $this->id])->execute();
            }
        } else {
            $this->error("Not found the dxm account with id [" . $this->id . "]" . PHP_EOL);
        }
    }

    /**
     * @param $username
     * @param $password
     * @return array|string
     * @throws Exception
     */
    protected function getCookies($username, $password)
    {
        // 启动 webdriver
        try {
            $host = 'http://localhost:4444/wd/hub';
            $options = new ChromeOptions();
            $options->addArguments(['--headless']);
            $desiredCapabilities = DesiredCapabilities::chrome();
            $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $options);
            $driver = RemoteWebDriver::create($host, $desiredCapabilities);

            $driver->get('https://www.xxx.com');
            $driver->manage()->window()->maximize();
            $wait = $driver->wait(10, 100);

            $nameInput = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id("exampleInputName")));
            $pwdInput = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id("exampleInputPassword")));
            $codeInput = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id("loginVerifyCode")));
            $codeImg = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id("loginImgVcode")));
        } catch (Exception $e) {
            $this->error('File:' . $e->getFile() . PHP_EOL . 'Line:' . $e->getLine() . PHP_EOL . 'Exception:' . $e->getMessage() . PHP_EOL);
            throw $e;
        }
        $codeImg->takeElementScreenshot($this->filename);

        $nameInput->sendKeys($username);
        $pwdInput->sendKeys($password);

        $code = $this->recognizeVerifyCode();
        $codeInput->sendKeys($code);
        $driver->findElement(WebDriverBy::id('loginBtn'))->click();
        sleep(1);

        // 检查是否登录成功
        $cookies = [];
        try {
            // 登录错误提示元素
            $errorElement = $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('p.p-left0')));
            $this->error('Login failed with error ' . $errorElement->getText() . PHP_EOL);
            $driver->close();
        } catch (TimeoutException $te) {
            // 未找到元素,登录成功
            $this->info("Login succeeded!" . PHP_EOL);
            $cookiesObj = $driver->manage()->getCookies();
            $driver->close();

            foreach ($cookiesObj as $cookie) {
                $cookies[] = $cookie->getName() . "=" . $cookie->getValue();
            }
            $cookies = implode(';', $cookies);
        } catch (Exception $e) {
            $this->error('File:' . $e->getFile() . PHP_EOL . 'Line:' . $e->getLine() . PHP_EOL . 'Exception:' . $e->getMessage() . PHP_EOL);
            $driver->close();
            throw $e;
        }

        return $cookies;
    }

    /**
     * recognize verify code
     *
     * @return mixed
     */
    protected function recognizeVerifyCode()
    {
        // 云打码识别验证码
        $client = new Client();
        $config = Yii::$app->params['yundama'];
        $res = $client->request('POST', 'http://api.yundama.com/api.php', [
            'multipart' => [
                [
                    'name' => 'method',
                    'contents' => 'upload'
                ],
                [
                    'name' => 'username',
                    'contents' => $config['username']
                ],
                [
                    'name' => 'password',
                    'contents' => $config['password']
                ],
                [
                    'name' => 'appid',
                    'contents' => $config['appid']
                ],
                [
                    'name' => 'appkey',
                    'contents' => $config['appkey']
                ],
                [
                    'name' => 'codetype',
                    'contents' => $config['codetype']
                ],
                [
                    'name' => 'timeout',
                    'contents' => '60'
                ],
                [
                    'name' => 'file',
                    'filename' => 'a.jpg',
                    'contents' => fopen($this->filename, 'r')
                ]

            ],
        ]);
        $json = json_decode($res->getBody()->getContents(), true);
        if ($json['ret'] == 0 && isset($json['text'])) {
            $this->info("Recognize verify code: [" . $json['text'] . "]" . PHP_EOL);
            if (empty($json['text'])) {
                sleep(10);

                return $this->recognizeVerifyCode();
            } else {
                return $json['text'];
            }
        } else {
            sleep(10);

            return $this->recognizeVerifyCode();
        }
    }
}