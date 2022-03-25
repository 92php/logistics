出入库单记录
=======
## 新增
POST /api/g/warehouse-sheet/create?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| warehouse_id | int | - | - | 是 | 仓库id | |
| type | int | 1~2 | - | 是 | 类型 | 0: 入库 1: 出库 2：调拨 |
| method | int | - | - | 是 | 出入库方式 | 见下详情 |
| change_datetime | datetime | - | - | 出入库时间 |  | |
| remark | string | 0 ~ 13 | - | 否 | 备注 | |
| ext_details | array | - | - | 否 | 扩展详情 |  |

### <span id="params">type=0时method参数</span>
         0 = 手工入库
         1 = 销售退货
         2 = 盘盈入库
         3 = 采购入库
         
### <span id="params">type=1时method参数</span>
        0=手工出库
        1=退货出库
        2=盘亏出库
        3=销售入库

### <span id="params">ext_details提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| rack_id | int | - | - | 是 | 货架id | |
| block | int | - | - | 是 | 分区 | |
| product_id | int |  | - | 是 | 商品 | |
| quantity | int | - | - | 是 | 数量 | |
| price | float | - | - | 否 | 价格 | |
| safely_quantity | int | - | 1 | 否 | 安全库存 |  |
| remark | string | 0 ~ 13 | - | 否 | 备注 | |


## 查询
GET /api/g/warehouse-sheet/create?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| warehouse_id | int | - | 否 | 仓库 |  |
| type | int | - | 否 | 类型 |  |
| method | int | - | 否 | 出入库方式 |  |
| change_datetime | date | - | 否 | 出入库时间 |  |
| number | string | - | 否 | 单号 | 0: 未激活 1: 激活 |

### <span id="params">expand参数</span>
        warehouse=仓库
        detail=详情