<?php

namespace app\modules\admin\modules\g\models;

use app\modules\api\models\Category;
use app\modules\api\modules\g\models\ProductImage;
use yadjet\helpers\IsHelper;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%g_product}}".
 *
 * @property int $id
 * @property int $category_id 分类id
 * @property int $type 商品类型
 * @property int $sale_method 销售方式
 * @property string $key Key
 * @property string $sku SKU
 * @property string $chinese_name 产品中文名称
 * @property string $english_name 产品英文名称
 * @property string $image 产品图
 * @property int $weight 重量
 * @property float $size_length 长
 * @property float $size_width 宽
 * @property float $size_height 高
 * @property float $allow_offset_weight 允许称重误差
 * @property int $stock_quantity 库存数量
 * @property int $status 商品状态
 * @property float $price 价格
 * @property float $cost_price 成本价
 * @property int $development_member_id 开发员
 * @property int $purchase_member_id 采购员
 * @property float $purchase_reference_price 采购参考价
 * @property string $qc_description 质检说明
 * @property int $customs_declaration_document_id 报关信息
 * @property int $created_at 创建时间
 * @property int $created_by 创建人
 * @property int $updated_at 修改时间
 * @property int $updated_by 修改人
 */
class Product extends \yii\db\ActiveRecord
{

    /**
     *  商品类型
     */
    const TYPE_SINGLE = 0; // 单商品
    const TYPE_COMBINE = 1; // 组合商品

    /**
     * 商品状态
     */
    const STATUS_ON_SALE = 0; // 在售
    const STATUS_HOT_SALE = 1; // 热销
    const STATUS_NEW_PRODUCT = 2; // 新品
    const STATUS_CLEARANCE = 3; // 清仓
    const STATUS_STOP_SALE = 4; // 停售

    /**
     * 销售方式
     */
    const SALE_METHOD_PRODUCT = 0; // 商品
    const SALE_METHOD_GIFT = 1; // 赠品
    const SALE_METHOD_PACKING = 2; // 包材（包装材料）

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%g_product}}';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sku', 'chinese_name', 'english_name'], 'required'],
            [['key', 'sku', 'chinese_name', 'english_name', 'image', 'qc_description'], 'trim'],
            [['category_id', 'weight', 'created_at', 'created_by', 'updated_at', 'updated_by', 'development_member_id', 'purchase_member_id', 'type', 'sale_method', 'size_length', 'size_width', 'size_height', 'allow_offset_weight', 'customs_declaration_document_id'], 'integer'],
            ['purchase_reference_price', 'number'],
            [['weight', 'price', 'cost_price', 'size_length', 'size_width', 'size_height', 'allow_offset_weight', 'stock_quantity', 'customs_declaration_document_id'], 'default', 'value' => 0],
            ['type', 'default', 'value' => self::TYPE_SINGLE],
            ['status', 'default', 'value' => self::STATUS_ON_SALE],
            ['sale_method', 'default', 'value' => self::SALE_METHOD_PRODUCT],
            ['type', 'in', 'range' => array_keys(self::typeOptions())],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['sale_method', 'in', 'range' => array_keys(self::saleMethodOptions())],
            [['stock_quantity', 'weight'], 'integer', 'min' => 0],
            [['price', 'cost_price'], 'number', 'min' => 0.01],
            [['key', 'sku'], 'string', 'max' => 128],
            [['chinese_name'], 'string', 'max' => 200],
            [['english_name'], 'string', 'max' => 300],
            [['image'], 'string', 'max' => 100],
            ['qc_description', 'string'],
            ['key', 'filter', 'filter' => 'strtolower'],
            [['key'], 'unique'],
            [['sku'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'category_id' => '分类',
            'category.name' => '分类',
            'type' => '商品类型',
            'sale_method' => '销售方式',
            'key' => 'Key',
            'sku' => 'SKU',
            'chinese_name' => '商品中文名称',
            'english_name' => '商品英文名称',
            'weight' => '重量（克）',
            'size_length' => '长（厘米）',
            'size_width' => '宽（厘米）',
            'size_height' => '高（厘米）',
            'allow_offset_weight' => '允许称重误差',
            'stock_quantity' => '库存数量',
            'status' => '商品状态',
            'price' => '价格',
            'cost_price' => '成本价',
            'image' => '图片',
            'development_member_id' => '开发员',
            'purchase_member_id' => '采购员',
            'purchase_reference_price' => '采购参考价',
            'qc_description' => '质检说明',
            'customs_declaration_document_id' => '报关信息',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'updated_at' => '修改时间',
            'updated_by' => '修改人',
        ];
    }

    /**
     * 商品类型
     *
     * @return array
     */
    public static function typeOptions()
    {
        return [
            self::TYPE_SINGLE => '单商品',
            self::TYPE_COMBINE => '组合商品',
        ];
    }

    /**
     * 商品状态
     *
     * @return array
     */
    public static function statusOptions()
    {
        return [
            self::STATUS_ON_SALE => '在售',
            self::STATUS_HOT_SALE => '热销',
            self::STATUS_NEW_PRODUCT => '新品',
            self::STATUS_CLEARANCE => '清仓',
            self::STATUS_STOP_SALE => '停售',
        ];
    }

    /**
     * 销售方式
     *
     * @return array
     */
    public static function saleMethodOptions()
    {
        return [
            self::SALE_METHOD_PRODUCT => '商品',
            self::SALE_METHOD_GIFT => '赠品',
            self::SALE_METHOD_PACKING => '包材（包装材料）',
        ];
    }

    /**
     * 所属分类
     *
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * skuMap
     *
     * @return ActiveQuery
     */
    public function getSkuMap()
    {
        return $this->hasMany(ProductSkuMap::class, ['product_id' => 'id']);
    }

    /**
     * 产品图片
     *
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id']);
    }

    /**
     * 报关信息
     *
     * @return ActiveQuery
     */
    public function getCustomsDeclarationDocument()
    {
        return $this->hasOne(CustomsDeclarationDocument::class, ['id' => 'customs_declaration_document_id']);
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (empty($this->key)) {
                $this->key = $this->sku;
            }

            return true;
        } else {
            return false;
        }
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
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert || isset($changedAttributes['chinese_name'])) {
            // 同步更新商品名称
            Yii::$app->getDb()->createCommand()->update(OrderItem::tableName(), [
                'product_id' => $this->id,
                'product_name' => $this->chinese_name
            ], ['sku' => $this->sku])->execute();
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $db = Yii::$app->getDb();
        $cmd = $db->createCommand();
        // 同步重置商品名称
        $db->createCommand('UPDATE {{%g_order_item}} SET [[product_name]] = [[sku]] WHERE [[sku]] = :sku', [':sku' => $this->sku])->execute();
        // 删除skuMap
        $cmd->delete("{{%g_product_sku_map}}", ['product_id' => $this->id])->execute();
    }

}
