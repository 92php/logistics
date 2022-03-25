仓库接口
=======

## 新增
POST /api/g/warehouse/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| name | string | 1 ~ 30 | - | 是 | 供应商名称 | |
| address | string | 1 ~ 100 | - | 是 | 地址 | |
| linkman | string | 1 ~ 10 | - | 是 | 联系人 | |
| tel | string | 0 ~ 13 | - | 是 | 联系电话 | |
| remark | string | 0 ~ 13 | - | 否 | 备注 | |
| enabled | int | - | 1 | 否 | 激活 | 0: 未激活 1: 激活 |

## 删除
DELETE /api/g/warehouse/delete?id=:id&access_token=:accessToken

## 修改
PUT/PATCH /api/g/warehouse/update?id=:id&access_token=:accessToken

## 查询
GET /api/g/warehouse/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| name | string | - | 否 | 供应商名称 | 支持模糊搜索 |
| tel | string | - | 否 | 联系电话 | 支持模糊搜索 |
| linkman | string | - | 否 | 联系人 | 支持模糊搜索 |
| enabled | int | - | 否 | 激活 | 0: 未激活 1: 激活 |

## 详情
GET /api/g/warehouse/view?id=:id&access_token=:accessToken