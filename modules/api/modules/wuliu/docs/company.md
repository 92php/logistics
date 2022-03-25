物流商接口
=========

## 获取物流商列表
GET /api/wuliu/company/index?access_token=:accessToken

### 查询参数
| 参数 | 说明 |
|---|---|
| name | 物流公司名称 |
| linkman | 负责人 |

## 添加物流商信息
POST /api/wuliu/company/create?access_token=:accessToken

## 编辑物流商
PUT/PATCH /api/wuliu/company/update?access_token=:accessToken&id=:id

### 参数
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| name | string | 是 | 无 | 物流公司名称 |
| mobile_phone | string | 是 | 无 | 联系方式 |
| linkman | string | 是 | 无 | 负责人 |
| code | string | 是 | 无 | 代码 |
| website_url | string | 是 | 无 | 公司网址 |
| enabled | int | 否 | 1 | 激活,0 => 否, 1 => 是 |
| remark | string | 否 | 无 | 备注 |

## 删除物流商
DELETE /api/wuliu/company/delete?access_token=:accessToken&id=:id


