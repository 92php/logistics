<?php

namespace app\modules\admin\modules\wuliu\models;

use yadjet\behaviors\ImageUploadBehavior;
use Yii;

/**
 * This is the model class for table "{{%wuliu_amazon_order_item}}".
 *
 * @property int $id
 * @property string $order_id 订单号
 * @property string $product_name 产品
 * @property string|null $product_image 产品图片
 * @property int $product_quantity 产品数量
 * @property string|null $size 尺寸
 * @property string|null $color 颜色
 * @property string|null $customized 定制内容
 * @property string|null $remark 备注
 * @property int $created_at 创建人
 * @property int $created_by 创建时间
 * @property int $updated_at 修改时间
 * @property int $updated_by 修改人
 */
class AmazonOrderItem extends \yii\db\ActiveRecord
{

    /**
     * @var string 文件上传字段
     */
    public $fileFields = 'product_image';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wuliu_amazon_order_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_name'], 'required'],
            [['product_quantity'], 'integer'],
            [['product_name', 'size', 'color'], 'string', 'max' => 50],
            [['order_id'], 'string', 'max' => 30],
            [['customized'], 'string', 'max' => 200],
            [['remark'], 'string'],
            ['product_image', 'image',
                'extensions' => 'jpg,gif,png,jpeg',
                'minSize' => 1024,
                'maxSize' => 1024 * 200,]
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => ImageUploadBehavior::class,
                'attribute' => 'product_image'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单号',
            'product_name' => '产品',
            'product_image' => '产品图片',
            'product_quantity' => '产品数量',
            'size' => '尺寸',
            'color' => '颜色',
            'customized' => '定制内容',
            'remark' => '备注',
            'created_at' => '添加时间',
            'created_by' => '添加人',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->updated_at = time();
                $this->created_by = $this->updated_by = Yii::$app->getUser()->getId();
            } else {
                $this->updated_at = time();
                $this->updated_by = Yii::$app->getUser()->getId();
            }

            return true;
        } else {
            return false;
        }
    }
}
