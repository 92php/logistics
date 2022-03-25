供应商操作接口
========

## 供货商确认接单或拒接单
PUT,PATCH /api/om/vendor/receiving?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| products | array | - | - | 是 | 商品数据 | |

### <span id="params">供货商确认接单或拒接单products提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| route_id | int | - | - | 是 | 路由id | |
| is_order_receiving | boolean | - | - | 是 | 是否接单，1代表接单，0代表拒接 | |
| reason | string | - | - | 是 | 拒接原因 | |


## 产品导出为excel
GET /api/om/vendor/start-production?sku=:sku&access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| orderItemIds | int | - | - | 否 | 订单商品id,多个以逗号分隔，不传的话则获取供应商下所有待生产订单 | |


## 打印条形码
GET /api/om/vendor/print?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| orderItemId | int | - | 是 | 订单商品id |  |


## 商品关联包裹号
POST /api/om/vendor/relation-package?access_token=:accessToken

### 提交参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| package_id | int | - | 是 | 包裹号 |  |
| number | string | - | 是 | 订单号 |  |

## 供应商发货接口
POST /api/om/vendor/delivery?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| waybill_number | int | 1-11 | - | 是 | 发货运单号 | |
| package_id | int | - | - | 是 | 包裹号 |  |
| logistics_company | string | - | - | 否 | 物流公司 |  |


## 获取打包发货产品
GET /api/om/vendor/pack-deliver-product?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| packageNumbers | string | - | 是 | 包裹号 |  |
| waybillNumbers | string | - | 是 | 运单号 |  |
| pageSize | int | - | 是 | 页数大小 |  |
| page | int | - | 是 | 第几页 |  |

## 获取智能找单
GET /api/om/vendor/intelligence?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| size | string | - | 是 | 尺寸 |  |
| color | string | - | 是 | 颜色 |  |
| material | string | - | 是 | 材质 |  |
| sku | string | - | 是 | sku 多个以空格分隔 |  |
| customized | string | - | 是 | 定制信息 |  |
| pageSize | int | - | 是 | 页数大小 |  |
| page | int | - | 是 | 第几页 |  |

## 供应商确认是否取消接口
PUT/PATCH /api/om/vendor/confirm-cancel?access_token=:accessToken&id=:id
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| confirmed_status | int | 1-3 | - | 是 | 是否同意取消 | 1=>同意,2=>拒绝 |
| confirmed_message | string | - | - | 否 | 确认反馈消息 | 拒绝取消时，消息是必填的 |

## 商品状态选项以及统计
GET /api/om/vendor/status-options?access_token=:accessToken

## 商品待生产转为生产中
PUT/PATCH /api/om/vendor/in-production?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| route_ids | array | | - | 是 | 路由id。可传多个 |

## 包裹移除商品
PUT/PATCH /api/om/vendor/remove-product?access_token=:accessToken
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| package_id | int | | - | 是 | 包裹id |
| route_id | int | | - | 是 | 路由id |

## 获取当前包裹下商品
GET /api/om/vendor/package-product?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| packageId | string | - | 是 | 包裹id |  |
| number | string | - | 否 | 订单号，多个以逗号分隔 |  |
| productName | string | - | 否 | 产品名 |  |
| sku | string | - | 否 | sku 多个以空格分隔 |  |
| extend | string | - | 否 | 定制信息 |  |
| pageSize | int | - | 是 | 页数大小 |  |
| page | int | - | 是 | 第几页 |  |