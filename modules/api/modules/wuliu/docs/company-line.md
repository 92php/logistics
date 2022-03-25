物流线路接口
=========

## 获取物流线路列表
GET /api/wuliu/company-line/index?access_token=:accessToken

### 查询参数
| 参数 | 说明 |
|---|---|
| name | 物流线路名称 |
| company_id | 所属物流公司 |
| enabled | 是否启用 |
| expand | 扩展，可用的有company => 所属公司, routes => 线路路由, templateFee => 模板计费, templateFee.template => 模板 |

## 添加物流线路信息
POST /api/wuliu/company-line/submit?access_token=:accessToken

## 编辑物流线路
POST /api/wuliu/company-line/submit?access_token=:accessToken&id=:id

### 参数
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| line | object | 是 | 无 | 物流线路 |
| templateFee | object | 是 | 无 | 线路计费详情 |
| template | object | 是 | 无 | 线路计费模板 |
| lineRoutes | array | 是 | 无 | 线路路由 |
| freightFeeRate | float | 否 | 无 | 运费基数折扣率 |
| baseFeeRate | float | 否 | 无 | 挂号费折扣率 |

### line参数详情
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| id | int | 是 | 无 | 线路id,新增时不传,修改时传原来的线路id |
| company_id | int | 是 | 无 | 线路所属物流公司 |
| country_id | int | 是 | 无 | 国家 |
| name | string | 是 | 无 | 线路名称 |
| estimate_days | int | 否 | 0 | 线路预估天数 |
| remark | string | 否 | 无 | 线路备注 |

提交示例
- line[company_id]:1
- line[country_id]:7
- line[name]:"line_name"
- line[estimate_days]:20
- line[remark]:"line_remark"

### templateFee参数详情
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| id | int | 否 | 无 | templateFee.id;添加时不传,修改时传原来的templateFee.id |
| line_id | int | 是 | 无 | 所属线路;添加时传0,修改时传原来的templateFee.line_id |
| template_id | string | 是 | 无 | 所属模板;添加时传0,修改时传原来的templateFee.template_id |
| min_weight | int | 是 | 无 | 最小重量 |
| max_weight | int | 是 | 无 | 最大重量 |
| first_weight | int | 否 | 1 | 首重 |
| first_fee | number | 否 | 无 | 首重费用 |
| continued_weight | int | 否 | 1 | 续重 |
| continued_fee | number | 否 | 无 | 续重费用 |
| base_fee | number | 是 | 无 | 挂号费 |
| fixed_fee | number | 否 | 无 | 固定费用 |
| enabled | int | 否 | 1 | 模板计费启用/停用;0 => 停用, 1 => 启用 |
| remark | string | 否 | 无 | 模板计费备注 |

提交示例
- templateFee[0][id]:1
- templateFee[0][line_id]:0
- templateFee[0][template_id]:0
- templateFee[0][min_weight]:1
- templateFee[0][max_weight]:50
- templateFee[0][first_weight]:2
- templateFee[0][first_fee]:5.0
- templateFee[0][continued_weight]:3
- templateFee[0][continued_fee]:7.5
- templateFee[0][base_fee]:12.5
- templateFee[0][fixed_fee]:12.5
- templateFee[0][remark]:"template_fee_remark"

### template参数详情
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| id | int | 否 | 无 | 模板id;添加时不传,修改时传原来的template.id |
| company_id | int | 是 | 无 | 模板所属物流公司 |
| fee_mode | int | 否 | 1 | 计费方式;0 => 按体积计费, 1 => 按重量计费, 2 => 固定运费 |
| remark | string | 否 | 无 | 模板备注 |

提交示例
- template[id]:0
- template[company_id]:1
- template[fee_mode]:1
- template[remark]:"template_remark"

### lineRoutes参数详情
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| id | int | 否 | 无 | 节点id;添加时不传,修改线路时传原来的routes.id |
| line_id | int | 是 | 无 | 节点所属线路;添加线路时传0,修改线路时传原来的线路id |
| step | int | 是 | 无 | 节点步骤,从1开始递增 |
| event | string | 是 | 无 | 节点事件 |
| package_status | int | 是 | 0 | 包裹状态;1=>已接单, 2=>,运输途中 3=>到达待取,4=>成功签收,7=>可能异常 ,8=>投递失败 |
| detection_keyword | string | 是 | 无 | 节点判断依据 |
| estimate_days | int | 否 | 0 | 节点预计花费天数 |

提交示例
- lineRoutes[0][id]:1
- lineRoutes[0][line_id]:0
- lineRoutes[0][step]:1
- lineRoutes[0][event]:"step_1 event"
- lineRoutes[0][package_status]:2
- lineRoutes[0][detection_keyword]:"step_1 detection_keyword"
- lineRoutes[0][estimate_days]:2
- lineRoutes[1][line_id]:1
- lineRoutes[1][step]:2
- lineRoutes[1][event]:"step_2 event"
- lineRoutes[1][package_status]:2
- lineRoutes[1][detection_keyword]:"step_2 detection_keyword"
- lineRoutes[1][estimate_days]:3

## 删除物流线路
DELETE /api/wuliu/company-line/delete?access_token=:accessToken&id=:id

## 启用/停用物流线路
PUT/PATCH /api/wuliu/company-line/update?access_token=:accessToken&id=:id

### 提交参数详情
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| enabled | int | 否 | 1 | 0 => 停用, 1 => 启用 |