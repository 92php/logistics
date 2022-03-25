账户接口
=========

## 获取账户列表
GET /api/wuliu/dxm-account/index?access_token=:accessToken

### 查询参数
| 参数 | 说明 |
|---|---|
| username | 用户名 |
| company_id | 物流公司 |
| platform_id | 所属平台,1=>量子星球,2=>跨客星球,3=>阿尔法星球 |
| expand | 可用的扩展有 company => 合作物流公司 |

## 添加账户信息
POST /api/wuliu/dxm-account/create?access_token=:accessToken

## 编辑账户
PUT/PATCH /api/wuliu/dxm-account/update?access_token=:accessToken&id=:id

### 参数
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| username | string | 是 | 无 | 用户名 |
| password | string | 是 | 无 | 密码 |
| company_id | int | 是 | 无 | 合作物流公司 |
| platform_id | int | 否 | 0 | 所属平台, 1=>量子星球,2=>跨客星球,3=>阿尔法星球 |
| is_valid | int | 否 | 1 | 是否有效,0 => 无效 1=> 有效 |
| remark | string | 否 | 无 | 备注 |

## 删除账户
DELETE /api/wuliu/dxm-account/delete?access_token=:accessToken&id=:id


