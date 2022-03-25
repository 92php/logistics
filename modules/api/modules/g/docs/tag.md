标签接口
=======

## 新增
POST /api/g/tag/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| type | int | 1 | - | 是 | 类型 | |
| parent_id | int | 1 | - | 是 | 父类 | |
| name | string |  | - | 是 | 标签名 | |
| ordering | int | 1 | - | 是 | 排序 | |
| enabled | int | - | 1 | 否 | 激活 | 0: 未激活 1: 激活 |

## 删除
DELETE /api/g/tag/delete?id=:id&access_token=:accessToken

## 修改
PUT/PATCH /api/g/tag/update?id=:id&access_token=:accessToken

## 查询
GET /api/g/tag/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| name | string | - | 否 | 供应商名称 | 支持模糊搜索 |
| type | int | - | 否 | 类型 |  |
| parent_id | int | - | 否 | 父类 |  |
| enabled | int | - | 否 | 激活 | 0: 未激活 1: 激活 |

## 详情
GET /api/g/tag/view?id=:id&access_token=:accessToken