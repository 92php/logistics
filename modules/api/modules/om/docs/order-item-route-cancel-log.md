商品取消接口
===========
## 仓库商品取消下单接口
POST /api/om/order-item-route-cancel-log/cancel?access_token=:accessToken

### <span id="params">提交参数</span>
| 参数 | 值类型 | 长度 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | :---: | --- | --- |
| order_item_route_id | int | 1-11 | - | 是 | 商品route id | |
| canceled_reason | string | - | - | 否 | 取消原因 | 商品已接单时，取消原因是必填的 |
| canceled_quantity | int | 1-3 | - | 是 | 取消数量 | |
| type | int | - | 0 | 否 | 取消类型 | 0 => 取消下单, 1 => 取消商品 |

## 供应商查看申请取消列表
GET /api/om/order-item-route-cancel-log/index?&access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 说明 | 备注 |
| --- | :---: | :---: | --- | --- |
| confirmed_status |  取消状态筛选 | 0=>待取消,1=>同意取消,2=>拒绝取消 | |
| extend | string | 扩展数据,route => 路由数据,route.item => 商品数据 | 多个以,连接 |


## 商品状态选项以及统计
GET /api/om/order-item-route-cancel-log/status-options?access_token=:accessToken


## 判断商品是否可取消
GET /api/om/order-item-route-cancel-log/is-cancel?access_token=:accessToken
