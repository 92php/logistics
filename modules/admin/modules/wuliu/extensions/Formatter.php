<?php

namespace app\modules\admin\modules\wuliu\extensions;

use app\models\Option;
use app\modules\admin\modules\wuliu\models\FreightTemplate;
use app\modules\admin\modules\wuliu\models\Package;
use app\modules\admin\modules\wuliu\models\PackageRoute;

/**
 * Class Formatter
 *
 * @package app\modules\admin\modules\wuliu\extensions

 */
class Formatter extends \app\modules\admin\extensions\Formatter
{

    /**
     * 包裹同步状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asPackageSyncStatus($value)
    {
        $options = Package::syncStatusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

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

    /**
     * 包裹路由状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asPackageRouteStatus($value)
    {
        $options = PackageRoute::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 包裹路由处理状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asPackageRouteProcessStatus($value)
    {
        $options = PackageRoute::handledStatusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 模板计费方式
     *
     * @param $value
     * @return mixed|null
     */
    public function asFeeMode($value)
    {
        $options = FreightTemplate::feeModeOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 平台名称
     *
     * @param $value
     * @return mixed|null
     */
    public function asPlatform($value)
    {
        $options = Option::platforms();

        return isset($options[$value]) ? $options[$value] : null;
    }

}
