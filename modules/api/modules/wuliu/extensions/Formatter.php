<?php

namespace app\modules\api\modules\wuliu\extensions;

use app\modules\api\modules\g\models\Package;

/**
 * Formatter
 *
 * @package app\modules\api\modules\wuliu\extensions
 */
class Formatter extends \app\modules\api\modules\g\extensions\Formatter
{

    /**
     * 包裹状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asPackageStatus($value)
    {
        $options = Package::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }
}