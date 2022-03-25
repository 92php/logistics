<?php

namespace app\modules\api\modules\g\models;

use app\modules\api\extensions\AppHelper;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * Class Product
 *
 * @package app\modules\api\modules\g\models
 */
class Product extends \app\modules\admin\modules\g\models\Product
{

    public $ext_images; // 图片
    public $ext_sku_map; // sku配对
    public $ext_combine; // 组合商品

    public function rules()
    {
        $webRoot = Yii::getAlias('@webroot');

        return array_merge(parent::rules(), [
            ['ext_images', function ($attribute, $params) use ($webRoot) {
                if (is_array($this->ext_images)) {
                    $error = null;
                    foreach ($this->ext_images as $image) {
                        $path = $image['path'];
                        if (!file_exists($webRoot . $path)) {
                            $error = "$path 文件不存在。";
                            break;
                        }
                    }
                    if ($error !== null) {
                        $this->addError($attribute, $error);
                    }
                } else {
                    $this->addError($attribute, "产品图片数据格式错误。");
                }
            }],

            ['ext_sku_map', function ($attribute, $params) {
                if (is_array($this->ext_sku_map)) {
                    foreach ($this->ext_sku_map as $item) {
                        if (empty($item['value'])) {
                            $this->addError($attribute, "商品sku配对值不能为空，请检查。");
                            break;
                        }
                    }
                } else {
                    $this->addError($attribute, "sku配对规则格式错误。");
                }
            }],
            ['ext_combine', function ($attribute, $params) {
                if (is_array($this->ext_combine)) {
                    if ($this->type == Product::TYPE_COMBINE) {
                        $db = Yii::$app->getDb();
                        foreach ($this->ext_combine as $combine) {
                            $exists = $db->createCommand("SELECT COUNT(*) FROM {{%g_product}} WHERE [[id]] = :id", [':id' => $combine['child_product_id']])->queryScalar();
                            if (!$exists) {
                                $this->addError($attribute, "组合产品不存在，请查验。");
                                break;
                            }
                        }
                    } else {
                        $this->addError($attribute, "该产品不是组合商品，不可组合。");
                    }
                } else {
                    $this->addError($attribute, "sku配对规则格式错误。");
                }
            }],
        ]);
    }

    public function fields()
    {
        return [
            'id',
            'category_id',
            'type',
            'sale_method',
            'sku',
            'chinese_name',
            'english_name',
            'image' => function ($model) {
                return AppHelper::fixStaticAssetUrl($model['image']);
            },
            'weight',
            'size_length',
            'size_width',
            'size_height',
            'allow_offset_weight',
            'stock_quantity',
            'status',
            'price',
            'cost_price',
            'development_member_id',
            'purchase_member_id',
            'purchase_reference_price',
            'qc_description',
            'customs_declaration_document_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
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
     * 组合商品
     *
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(ProductCombine::class, ['product_id' => 'id']);
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

    public function extraFields()
    {
        return [
            'sku-map' => 'skuMap',
            'images',
            'children',
            'customs-declaration-document' => 'customsDeclarationDocument',
        ];
    }

    /**
     * @throws Throwable
     * @throws Exception
     * @throws StaleObjectException
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $db = Yii::$app->getDb();
        // 新增图片
        if ($this->ext_images) {
            // 添加图片 先查询此产品所有图片id
            $imagesIds = $db->createCommand("SELECT [[id]] FROM {{%g_product_image}} WHERE [[product_id]] = :productId", [':productId' => $this->id])->queryColumn();
            $existsImagesIds = [];
            foreach ($this->ext_images as $key => $image) {
                if ($image['id'] != 0) {
                    $model = ProductImage::findOne($image['id']);
                    if ($model == null) {
                        $this->ext_images[$key]['id'] = 0;
                        $model = new ProductImage();
                    } else {
                        $existsImagesIds[] = $image['id'];
                    }
                } else {
                    $model = new ProductImage();
                }
                $payload = [
                    'product_id' => $this->id,
                    'ordering' => isset($image['ordering']) ? $image['ordering'] : 0,
                    'path' => $image['path']
                ];
                if ($model->load($payload, '')) {
                    $model->save();
                }
            }
            // 计算差集 获取删除项
            $deleteImagesIds = array_diff($imagesIds, $existsImagesIds);
            if ($deleteImagesIds) {
                foreach ($deleteImagesIds as $id) {
                    ProductImage::findOne($id)->delete();
                }
            }
        }
        // 增加sku产品配对
        if ($this->ext_sku_map) {
            $skuMapIds = $db->createCommand("SELECT [[id]] FROM {{%g_product_sku_map}} WHERE [[product_id]] = :productId", [':productId' => $this->id])->queryColumn();
            $existsSkuMapIds = [];
            foreach ($this->ext_sku_map as $key => $skuMap) {
                if ($skuMap['id'] != 0) {
                    $model = ProductSkuMap::findOne($skuMap['id']);
                    if ($model == null) {
                        $this->ext_sku_map[$key]['id'] = 0;
                        $model = new ProductSkuMap();
                    } else {
                        $existsSkuMapIds[] = $skuMap['id'];
                    }
                } else {
                    $model = new ProductSkuMap();
                }
                $payload = [
                    'product_id' => $this->id,
                    'value' => $skuMap['value']
                ];
                if ($model->load($payload, '')) {
                    $model->save();
                }
            }
            // 计算差集 获取删除项
            $deleteSkuMapIds = array_diff($skuMapIds, $existsSkuMapIds);
            if ($deleteSkuMapIds) {
                foreach ($deleteSkuMapIds as $id) {
                    ProductSkuMap::findOne($id)->delete();
                }
            }
        }

        // 组合商品
        if ($this->ext_combine) {
            $combineIds = $db->createCommand("SELECT [[id]] FROM {{%g_product_combine}} WHERE [[product_id]] = :productId", [':productId' => $this->id])->queryColumn();
            $existsCombineIds = [];
            foreach ($this->ext_combine as $key => $combine) {
                if ($combine['id'] != 0) {
                    $model = ProductCombine::findOne($combine['id']);
                    if ($model == null) {
                        $this->ext_combine[$key]['id'] = 0;
                        $model = new ProductCombine();
                    } else {
                        $existsCombineIds[] = $combine['id'];
                    }
                } else {
                    $model = new ProductCombine();
                }
                $payload = [
                    'product_id' => $this->id,
                    'child_product_id' => $combine['child_product_id']
                ];
                if ($model->load($payload, '')) {
                    $model->save();
                }
            }
            // 计算差集 获取删除项
            $deleteCombineIds = array_diff($combineIds, $existsCombineIds);
            if ($deleteCombineIds) {
                foreach ($deleteCombineIds as $id) {
                    ProductCombine::findOne($id)->delete();
                }
            }
        }
    }

}