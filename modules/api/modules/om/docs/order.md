订单接口
========

## 查询
GET /api/om/order/index?access_token=:accessToken&expand=items,items.business

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| number | int | - | 否 | 订单号 | 多个以逗号分隔 |
| sku | int | - | 否 | sku |  |
| productName | int | - | 否 | 产品名 |  |
| vendor | int | - | 否 | 供应商 |  |
| itemStatus | int | - | 否 | 订单状态 |  |
| _status | int | - | 否 | 订单商品状态 |  |
| customized | string | - | 否 | 定制信息 |  |
| payment_begin_at | date | - | 否 | 付款开始时间 |  |
| payment_end_at | date | - | 否 | 付款结束时间 |  |
| item_place_order_begin_at | date | - | 否 | 商品下单开始时间 |  |
| item_place_order_end_at | date | - | 否 | 商品下单结束时间 |  |


### expand查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| items | int | - | 否 | 订单号 | 订单商品详情 |
| items.business | int | - | 否 | 订单商品状态 |  |
| items.vendor | int | - | 否 | 供应商 |  |
| items.workflow  | int | - | 否 | 路由工作流向 |  |
| items.log  | int | - | 否 | 操作日志 |  |

## 详情
GET /api/om/order/view?id=:id&access_token=:accessToken

## 商品状态选项以及统计
GET /api/om/order/status-options?access_token=:accessToken