海关报关信息接口
=============

## 新增
POST /api/g/customs-declaration-document/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| code | string | 1~10 | - | 是 | 海关编码 | |
| chinese_name | string | 1 ~ 100 | - | 是 | 中文名 | |
| english_name | string | 1 ~ 100 | - | 是 | 英文名 | |
| weight | float | - | - | 是 | 申报重量 | |
| amount | float | - | - | 是 | 申报金额 | |
| danger_level | float | - | - | 是 | 危险等级 | |
| default | float | - | - | 是 | 默认 | |
| enabled | int | - | 1 | 否 | 0: 未激活 1: 激活 |

## 删除
DELETE /api/g/customs-declaration-document/delete?id=:id&access_token=:accessToken

## 修改
PUT/PATCH /api/g/customs-declaration-document/update?id=:id&access_token=:accessToken

## 查询
GET /api/g/customs-declaration-document/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| code | string | - | 否 | 海关编码 | 支持模糊搜索 |
| chinese_name | string | - | 否 | 中文名 | 支持模糊搜索 |
| english_name | string | - | 否 | 英文名 | 支持模糊搜索 |
| enabled | int | - | 否 | 激活 | 0: 未激活 1: 激活 |

## 详情
GET /api/g/customs-declaration-document/view?id=:id&access_token=:accessToken