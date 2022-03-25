<?php

namespace app\models;

class Option extends BaseOption
{

    /**
     * 组织选项
     *
     * @return string[]
     */
    public static function organizations()
    {
        return [
            Constant::ORGANIZATION_SHU_KE => '数客星球',
            Constant::ORGANIZATION_LIANG_ZI => '量子星球',
            Constant::ORGANIZATION_KUA_KE => '跨客星球',
            Constant::ORGANIZATION_YUAN_ZI => '原子星球',
            Constant::ORGANIZATION_A_ER_FA => '零壹星球',
            Constant::ORGANIZATION_BEI_TA => '贝塔星球',
            Constant::ORGANIZATION_LIU_LIANG => '流量星球',
            Constant::ORGANIZATION_MA_YI => '蚂蚁星球',
            Constant::ORGANIZATION_NUO_YA => '诺亚星球',
            Constant::ORGANIZATION_SAN_TI => '三体星球',
            Constant::ORGANIZATION_XING_QIU_CAN => '星球仓',
            Constant::ORGANIZATION_SHEN_ZHOU_WU_LIU => '神州物流',
            Constant::ORGANIZATION_PAI_TE => '派特星球',
            Constant::ORGANIZATION_OTHER => '其他',
        ];
    }

    /**
     * 平台选项
     *
     * @return string[]
     */
    public static function platforms()
    {
        return [
            Constant::PLATFORM_SHOPIFY => 'Shopify',
            Constant::PLATFORM_AMAZON => 'Amazon',
            Constant::PLATFORM_EBAY => 'eBay',
            Constant::PLATFORM_WISH => 'Wish',
            Constant::PLATFORM_TOPHATTER => 'TopHatter',
            Constant::PLATFORM_ALIEXPRESS => 'AliExpress',
        ];
    }

    /**
     * 第三方平台选项
     *
     * @return string[]
     */
    public static function thirdPartyPlatforms()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_DIAN_XIAO_MI => '店小秘',
            Constant::THIRD_PARTY_PLATFORM_TONG_TOOL => '通途',
            Constant::THIRD_PARTY_PLATFORM_GERPGO => '积加',
            Constant::THIRD_PARTY_PLATFORM_SHOPIFY => 'Shopify',
        ];
    }

    /**
     * 地区选项
     *
     * @return array
     */
    public static function regions()
    {
        return [
            Constant::REGION_EUROPE => '欧洲',
            Constant::REGION_NORTH_AMERICA => '北美',
            Constant::REGION_SOUTH_AMERICA => '南美',
            Constant::REGION_ASIA => '亚洲',
            Constant::REGION_OCEANIA => '大洋洲',
            Constant::REGION_AFRICA => '非洲',
            Constant::REGION_OTHER => '其他',
        ];
    }

    /**
     * 店小秘平台订单状态选项
     *
     * @return string[]
     */
    public static function thirdPartyPlatformDxmOrderStatusOptions()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_UNKNOWN => '未知',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_WEI_FU_KUAN => '未付款',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_FENG_KONG_ZHONG => '风控中',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_SHEN_HE => '待审核',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_CHU_LI => '待处理',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_CHU_LI => '已处理',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_YOU_HUO => '待打单（有货）',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_QUE_HUO => '待打单（缺货）',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_DAI_DA_DANG_YOU_YI_CHANG => '待打单（有异常）',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_JIAO_YUN => '已交运',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_TUI_KUAN => '已退款',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_HU_LUE => '已忽略',
            Constant::THIRD_PARTY_PLATFORM_DXM_ORDER_STATUS_YI_WAN_CHENG => '已完成',
        ];
    }

    /**
     * 店小秘平台包裹状态选项
     *
     * @return string[]
     */
    public static function thirdPartyPlatformDxmPackageStatusOptions()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_UNKNOWN => '未知',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_WEI_FU_KUAN => '未付款',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_FENG_KONG_ZHONG => '风控中',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_SHEN_HE => '待审核',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_CHU_LI => '待处理',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_CHU_LI => '已处理',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_YOU_HUO => '待打单（有货）',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_QUE_HUO => '待打单（缺货）',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_DAI_DA_DANG_YOU_YI_CHANG => '待打单（有异常）',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_JIAO_YUN => '已交运',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_TUI_KUAN => '已退款',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_HU_LUE => '已忽略',
            Constant::THIRD_PARTY_PLATFORM_DXM_PACKAGE_STATUS_YI_WAN_CHENG => '已完成',
        ];
    }

    /**
     * 通途平台订单状态选项
     *
     * @return string[]
     */
    public static function thirdPartyPlatformTongToolOrderStatusOptions()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_TONG_TOOL_ORDER_STATUS_UNKNOWN => '未知',
        ];
    }

    /**
     * 通途平台包裹状态选项
     *
     * @return string[]
     */
    public static function thirdPartyPlatformTongToolPackageStatusOptions()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_TONG_TOOL_PACKAGE_STATUS_UNKNOWN => '未知',
        ];
    }

    /**
     * 积加 ERP 订单状态
     *
     * @return array
     */
    public static function thirdPartyPlatformGerpgoOrderStatusOptions()
    {
        return [
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_UNKNOWN => '无状态',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_WEI_FU_KUAN => '未付款',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_WEI_FA_HUO => '未发货',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_JIAN_HUO_ZHONG => '拣货中',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YI_FA_HUO => '已发货',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YI_QU_XIAO => '已取消',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_YU_DING => '预订订单',
            Constant::THIRD_PARTY_PLATFORM_GERPGO_ORDER_STATUS_BU_FENG_FA_HUO => '部分发货',
        ];
    }

}