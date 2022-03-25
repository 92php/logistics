<?php

namespace app\modules\admin\modules\g\models;

use yadjet\helpers\IsHelper;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "{{%g_product_image}}".
 *
 * @property int $id
 * @property int $product_id 所属产品
 * @property string|null $title 标题
 * @property string|null $path 图片
 * @property int $ordering 排序
 * @property int $created_at 添加时间
 * @property int $created_by 添加人
 * @property int $updated_at 更新时间
 * @property int $updated_by 更新人
 */
class ProductImage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_product_image}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['product_id', 'ordering', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['title', 'path'], 'string', 'max' => 100],
            ['product_id', 'exist',
                'targetClass' => Product::class,
                'targetAttribute' => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'product_id' => '所属产品',
            'title' => '标题',
            'path' => '图片',
            'ordering' => '排序',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $userId = IsHelper::cli() ? 0 : Yii::$app->getUser()->getId();
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = $userId;
            } else {
                $this->updated_at = time();
                $this->updated_by = $userId;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 图片所属产品
     *
     * @return ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->path && file_exists($path = Yii::getAlias('@webroot' . $this->path))) {
            FileHelper::unlink($path);
        }
    }

}
