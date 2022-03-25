包裹接口
========

## 新增
POST /api/om/package/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| number | string | 1-20 | - | 是 | 包裹号，年月日时分秒+随机五位数 | |
| items_quantity | int | - | 0 | 是 | 物品数量 | |
| remaining_items_quantity | int | - | 0 | 否 | 待寄送物品数量 |  |

## 删除
DELETE /api/om/package/delete?id=:id&access_token=:accessToken

## 修改
PUT/PATCH /api/om/package/update?id=:id&access_token=:accessToken

## 查询
GET /api/om/package/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| number | string | - | 否 | 包裹号 | 支持模糊搜索 |
| title | string | - | 否 | 包裹名称 | 支持模糊查询 |

## 详情
GET /api/om/package/view?id=:id&access_token=:accessToken