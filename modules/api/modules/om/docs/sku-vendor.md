SKU 供应商匹配接口
================

## 新增
POST /api/om/sku-vendor/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| ordering | int | 0 - 128 | 1 | 是 | 排序 | |
| sku | string | 1 ~ 100 | - | 是 | SKU | |
| vendor_id | int | - | - | 否 | 供应商 | 供应商 ID |
| cost_price | float | 10,2 | 0 | 是 | 成本价 | |
| production_min_days | int | 1 ~ 127 | - | 是 | 生产最小天数 | |
| production_max_days | int | 1 ~ 127 | - | 是 | 生产最大天数 | |
| enabled | int | - | 1 | 否 | 激活 | 0: 未激活 1: 激活 |
| remark | string | - | - | 否 | 备注 | |

## 批量新增
POST /api/om/sku-vendor/batch-create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| sku | string | 1 ~ 100 | - | 是 | SKU | |
| vendors | array | - | - | 是 | 供应商列表 | 请参考【新增】接口提交参数，除 sku 不需要单独提交外，其他数据格式一致 |

## 删除
DELETE /api/om/sku-vendor/delete?id=:id&access_token=:accessToken

## 修改
PUT/PATCH /api/om/sku-vendor/update?id=:id&access_token=:accessToken

## 批量修改
POST /api/om/sku-vendor/batch-update?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| sku | string | 1 ~ 100 | - | 是 | SKU | |
| vendors | array | - | - | 是 | 供应商列表 | 请参考【新增】接口提交参数，除 sku 不需要单独提交外，需要额外提交数据 id，其他数据格式一致 |

## 查询
GET /api/om/sku-vendor/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| sku | string | - | 是 | SKU | |
| vendor_id | int | - | 否 | 供应商 | 供应商 ID |

## 详情
GET /api/om/sku-vendor/view?sku=:sku&access_token=:accessToken


