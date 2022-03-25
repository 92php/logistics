订单详情接口
========

## 搜索可下单商品
GET /api/om/order-item/search-place-order?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| orderIds | string | - | 是 | 订单id | 多个以逗号分隔 |


## 产品质检
POST /api/om/order-item/product-inspection?sku=:sku&access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| order_item_id | int | - | - | 是 | 商品id | |
| feedback | string | - | - | 否 | 反馈，不符合质检标准时是必填的 | |
| information_feedback | int | - | - | 否 | 信息反馈，信息不匹配时是必填的 | |
| is_accord_with | int | - | - | 是 | 是否符合质检标准，0 => 不符合, 1 => 符合 | |
| is_information_match | int | - | - | 是 | 是否信息匹配，0 => 否, 1 => 是| |
| quantity | int | - | - | 是 | 质检合格数量 | 如果质检合格数量小于item数量，自动生成补单 |

## 商品核对
PUT,PATCH /api/om/order-item/check?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| order_item_id | int | - | - | 是 | 订单详情id | |
| customized | string | - | - | 是 | 定制信息 | |
| remark | string | - | - | 是 | 备注 | |

## 商品下单，批量下单接口
PUT,PATCH /api/om/order-item/place-order?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| products | array | - | - | 是 | 商品数据 | |

### <span id="params">商品下单products提交参数解析</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| id | int | - | - | 是 | 订单详情id | |
| vendor_id | int | - | - | 是 | 供应商id | |
| cost_price | float | - | - | 是 | 成本价 | |

## 产品批量入库
POST /api/om/order-item/batch-warehousing?access_token=:accessToken
### <span id="params">产品批量入库提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| order_item_ids | array | - | - | 是 | 商品id | |
| is_pass | boolean | - | - | 是 | 是否通过，1为通过，0为不通过 | |
| information_feedback | string | - | - | 否 | 信息反馈，is_pass不通过必填 | |
| feedback | string | - | - | 否 | 反馈，is_pass不通过必填 | |

## 产品忽略接口
PUT /api/om/order-item/ignore?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| order_item_id | int | - | - | 是 | 商品id | |