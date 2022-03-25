<?php

namespace app\modules\api\modules\g\models;

use app\modules\admin\modules\g\extensions\Formatter;
use app\modules\api\models\Meta;
use Yii;

class Shop extends \app\modules\admin\modules\g\models\Shop
{

    public function fields()
    {
        /* @var $formatter Formatter */
        $formatter = Yii::$app->getFormatter();

        return [
            'id',
            'organization_id',
            'organization_id_formatted' => function ($model) use ($formatter) {
                return $formatter->asOrganization($model->organization_id);
            },
            'platform_id',
            'platform_id_formatted' => function ($model) use ($formatter) {
                return $formatter->asPlatform($model->platform_id);
            },
            'name',
            'url',
            'product_type',
            'product_type_formatted' => function ($model) use ($formatter) {
                return $formatter->asProductType($model->product_type);
            },
            'third_party_authentication_id',
            'enabled' => function ($model) {
                return boolval($model->enabled);
            },
            'remark',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ];
    }

    public function extraFields()
    {
        return [
            'third_party_authentication' => 'thirdPartyAuthentication'
        ];
    }

    /**
     * 第三方平台认证
     *
     * @return \yii\db\ActiveQuery
     */
    public function getThirdPartyAuthentication()
    {
        return $this->hasOne(ThirdPartyAuthentication::class, ['id' => 'third_party_authentication_id']);
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function getMetaItems()
    {
        $items = [];
        $rawItems = Yii::$app->getDb()->createCommand('
SELECT [[m.key]], [[m.return_value_type]], [[string_value]], [[text_value]], [[integer_value]], [[decimal_value]] FROM {{%meta_value}} t
 LEFT JOIN {{%meta}} m ON [[t.meta_id]] = [[m.id]]
 WHERE [[t.object_id]] = :objectId AND [[meta_id]] IN (SELECT [[id]] FROM {{%meta}} WHERE [[table_name]] = :tableName)
', [
            ':objectId' => $this->id,
            ':tableName' => str_replace(['{{%', '}}'], '', static::tableName())
        ])->queryAll();
        foreach ($rawItems as $item) {
            $valueKey = Meta::parseReturnKey($item['return_value_type']);
            $items[$item['key']] = $item[$valueKey];
        }

        return $items;
    }

}

