<?php

namespace app\commands\classes;

use DateTime;
use Exception;
use yadjet\helpers\ImageHelper;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use function Symfony\Component\String\u;

/**
 * 第三方平台数据提供接口类
 *
 * @package app\commands\classes
 */
abstract class ThirdPartyDataProvider
{

    /**
     * 获取订单数据
     *
     * @param array $identity
     * @param Shop|null $shop
     * @param Datetime|null $datetime
     * @return array
     */
    abstract public function getOrders(array $identity, Shop $shop = null, Datetime $datetime = null): array;

    /**
     * 获取包裹数据
     *
     * @param array $identity
     * @param Shop|null $shop
     * @param Datetime|null $beginDate
     * @param DateTime|null $endDate
     * @return array
     */
    abstract public function getPackages(array $identity, Shop $shop = null, Datetime $beginDate = null, Datetime $endDate = null): array;

    /**
     * 图片下载
     *
     * @param $path
     * @param null $filename
     * @param bool $showMessage
     * @return string
     * @throws \yii\base\Exception
     */
    protected function downloadImage($path, $filename = null, $showMessage = true)
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

            $showMessage && Console::stdout(" > Download $path image...");
            try {
                $size = file_put_contents($savePath . DIRECTORY_SEPARATOR . $imgName, file_get_contents($path));
                if ($size !== false) {
                    $res = "$imgPath/$imgName";
                    $showMessage && Console::stderr(" [ Successful ]" . PHP_EOL);
                } else {
                    $showMessage && Console::stderr(" [ Failed ]" . PHP_EOL);
                }
            } catch (Exception $e) {
                $showMessage && Console::stderr($e->getMessage() . PHP_EOL);
            }
        }

        return $res;
    }

}
