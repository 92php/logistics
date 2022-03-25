商品路由接口
===============

## 供应商商品列表
GET /api/om/order-item-route/index?access_token=:accessToken&expand=item,vendor

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| current_node | int | - | 否 | 商品业务状态 | 2=>待接单,3=>拒接,4=>待生产,5=>生产中,6=>待发货,7=>待收货,8=>待质检,9=>已完成,10=>已取消 |
| sku | string | - | 否 | sku | 支持模糊搜索 |
| product_name | string | - | 否 | 产品名 | 支持模糊搜索 |
| vendor_id | int | - | 否 | 供应商 |  |
| extend | string | - | 否 | 定制信息筛选 | 注意区分大小写 |
| number | string | - | 否 | 订单号筛选 | 多个以逗号分隔 |
| payment_begin_datetime | string | - | 否 | 付款开始时间 | 日期格式如：2020-05-05 |
| payment_end_datetime | string | - | 否 | 付款结束时间 | 日期格式如：2020-05-05 |
| expand | string | - | 否 | 扩展数据,vendor => 供应商数据, item => 商品数据, item.order => 订单数据 | 多个以,连接 |
| is_package | int | - | 否 | 是否有包裹号 |  |
| place_order_begin_at | string | - | 否 | 下单开始时间 | 日期格式如：2020-05-05 |
| place_order_end_at | string | - | 否 | 下单结束时间 | 日期格式如：2020-05-05 |

## 质检核对搜索接口
GET /api/om/order-item-route/index?access_token=:accessToken&expand=item,vendor

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| number | string | - | 否 | 订单号筛选 | 支持模糊搜索 |
| waybill_number | string | - | 否 | 运单号筛选 | 不支持模糊搜索,支持多个搜索,多个运单号请以,连接 |

### 商品总数以及已质检商品数
计算商品总数:
- 对route.item.quantity求和；

- route.status=0 忽略（无效的route忽略）；

- route.is_reissue=1 忽略（补单的route忽略）

计算已质检商品数：
- 对route.inspection_number求和

## 仓库批量收货接口
POST /api/om/order-item-route/batch-receipt?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| waybill_number | int | 1-11 | - | 是 | 收货运单号 | |