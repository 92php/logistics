商品配件接口
========

## 获取配件列表
GET  /api/om/part/index?access_token=:accessToken

## 新增配件
POST /api/om/part/upsert?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| sku | string | - | - | 是 | sku | |
| customized | string | - | - | 是 | 定制信息 | |


## 智能匹配订单
GET /api/om/part/match?access_token=:accessToken

### <span id="params">请求参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| page | int | - | - | 是 | 页数 | |
| pageSize | int | - | - | 是 | 页数大小 | |

## 修改配件
PUT,PATCH /api/om/part/update?access_token=:accessToken&id=:id
### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| sku | string | - | - | 是 | sku | |
| customized | string | - | - | 是 | 定制信息 | |

## 删除配件
DELETE /api/om/part/delete?access_token=:accessToken&id=:id

## 清除所有配件
DELETE /api/om/part/clear?access_token=:accessToken
