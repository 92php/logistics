客户接口
=======

## 添加
POST /api/g/customer/create?access_token=:accessToken

### 表单值
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| platform_id | int | 是 | 无 | 所属平台 |
| key | string | 否 | 无 | 外部系统编号 |
| email | string | 否 | 无 | 邮箱 |
| first_name | string | 否 | 无 | 姓 |
| last_name | string | 否 | 无 | 名 |
| phone | string | 否 | 无 | 联系电话 |
| currency | string | 否 | 无 | 货币 |
| remark | string | 否 | 无 | 备注 |
| status | int | 否 | 1 | 1: 有效、2: 无效 |

## 删除
DELETE /api/g/customer/delete?id=:id&access_token=:accessToken

## 修改
PUT|PATCH /api/g/customer/update?id=:id&access_token=:accessToken

## 查询
GET /api/g/customer/index?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| platform_id | int | - | 否 | 平台id |  |
| status | int | - | 否 | 状态 | 1: 有效、2: 无效  |
| email | string | - | 否 | 邮箱 |  |
| phone | string | - | 否 | 电话 |  |
| first_name | string | - | 否 | 姓 |  |
| last_name | string | - | 否 | 名 |  |

### 平台 id（platform_id）对应关系
- shopify = 1;
- Amazon = 2;
- eBay = 3;
- Wish = 4;
- TopHatter = 5;
- AliExpress = 6;