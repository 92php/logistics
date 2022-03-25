公共订单接口
=========

## 订单数据查询
GET /api/g/order/statistics?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| organizationId | int | - | 否 | 组织id |  |
| platformId | int | - | 否 | 平台id |  |
| shopId | int | - | 否 | 店铺id |  |
| beginDate | string | - | 否 | 开始时间，默认当天 |  |
| endDate | string | - | 否 | 结束时间，默认当天 |  |
| index | string | - | 否 | 索引 | 可根据year,month,day获取数据，默认为day |

### 组织id（organizationId）对应关系
- 数客 = 1
- 量子 = 2
- 跨客 = 3 
- 原子 = 4 
- 阿尔法 = 5
- 贝塔 = 6
- 流量 = 7 
- 蚂蚁 = 8 
- 诺亚 = 9 
- 三体 = 10
- 星球仓 = 11 
- 神州物流 = 12 
- 其他 = 0

### 平台id（platformId）对应关系
- shopify = 1;
- AMAZON = 2;
- EBAY = 3;
- WISH = 4;
- TOPHATTER = 5;
- ALIEXPRESS = 6;

