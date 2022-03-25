<?php

namespace app\modules\admin\modules\g\extensions;

use app\models\Option;
use app\modules\admin\modules\g\models\Customer;
use app\modules\admin\modules\g\models\Order;
use app\modules\admin\modules\g\models\Package;
use app\modules\admin\modules\g\models\Shop;
use app\modules\api\models\Constant;

/**
 * Class Formatter
 *
 * @package app\modules\admin\modules\g\extensions

 */
class Formatter extends \app\modules\admin\extensions\Formatter
{

    /**
     * 所属组织
     *
     * @param $value
     * @return string|null
     */
    public function asOrganization($value)
    {
        $options = Option::organizations();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 所属平台
     *
     * @param $value
     * @return string|null
     */
    public function asPlatform($value)
    {
        $options = Option::platforms();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 所属第三方平台
     *
     * @param $value
     * @return string|null
     */
    public function asThirdPartyPlatform($value)
    {
        $options = Option::thirdPartyPlatforms();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 国家所属地区
     *
     * @param $value
     * @return mixed|null
     */
    public function asCountryRegion($value)
    {
        $options = Option::regions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 商品类型
     *
     * @param $value
     * @return string|null
     */
    public function asProductType($value)
    {
        $options = Shop::productTypeOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 订单类型
     *
     * @param $value
     * @return string|null
     */
    public function asOrderType($value)
    {
        $options = Order::typeOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 订单状态
     *
     * @param $value
     * @return string|null
     */
    public function asOrderStatus($value)
    {
        $options = Order::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 包裹状态
     *
     * @param $value
     * @return string|null
     */
    public function asPackageStatus($value)
    {
        $options = Package::statusOptions();

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 第三方平台订单状态
     *
     * @param $value
     * @param int $thirdPartyPlatformId
     * @return string|null
     */
    public function asThirdPlatformOrderStatus($value, $thirdPartyPlatformId = Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI)
    {
        switch ($thirdPartyPlatformId) {
            case Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI:
                $options = Option::thirdPartyPlatformDxmOrderStatusOptions();
                break;

            case Constant::THIRD_PARTY_PLATFORM_TONG_TOOL:
                $options = Option::thirdPartyPlatformTongToolOrderStatusOptions();
                break;

            default:
                $options = [];
                break;
        }

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 第三方平台包裹状态
     *
     * @param $value
     * @param int $thirdPartyPlatformId
     * @return string|null
     */
    public function asThirdPlatformPackageStatus($value, $thirdPartyPlatformId = Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI)
    {
        switch ($thirdPartyPlatformId) {
            case Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI:
                $options = Option::thirdPartyPlatformDxmPackageStatusOptions();
                break;

            case Constant::THIRD_PARTY_PLATFORM_TONG_TOOL:
                $options = Option::thirdPartyPlatformTongToolPackageStatusOptions();
                break;

            default:
                $options = [];
                break;
        }

        return isset($options[$value]) ? $options[$value] : null;
    }

    /**
     * 客户状态
     *
     * @param $value
     * @return mixed|null
     */
    public function asCustomerStatus($value)
    {
        $options = Customer::statusOptions();

        return $options[$value] ?? null;
    }

}
