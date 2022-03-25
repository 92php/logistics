<?php

namespace app\forms;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadForm extends Model
{

    /**
     * @var $file UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false],
//            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx'],
        ];
    }

    /**
     * @throws \yii\base\Exception
     */
    public function upload()
    {
        if ($this->validate()) {
            // 获取年月日 创建文件夹
            $now = time();
            $y = date("y", $now);
            $m = date("m", $now);
            $d = date("d", $now);
            $dir = Yii::getAlias("@webroot/tmp/excel/") . $y . "/" . $m . "/" . $d;
            if (!file_exists($dir)) {
                FileHelper::createDirectory($dir);
            }
            $filename = $this->file->baseName . rand(1000, 9999) . '.' . $this->file->extension;
            $this->file->saveAs($dir . '/' . $filename);

            return $dir . '/' . $filename;
        } else {
            return false;
        }
    }
}