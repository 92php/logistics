包裹接口
=======
## 列表
GET /api/g/package/index?access_token=:accessToken
### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| organization_id | int | - | 否 | 组织 |  |
| begin_date | string | - | 否 | 开始时间 |  |
| end_date | string | - | 否 | 结束时间 |  |
| number | string | - | 否 | 包裹号 |  |
| waybill_number | string | - | 否 | 运单号 |  |
| order_number | string | - | 否 | 订单号 | 多个订单号使用小写的逗号或者空格进行分隔 |
| logistics_line_id | int | - | 否 | 物流线路 |  |
| country_id | int | - | 否 | 国家 |  |
| order_begin_place_date | string | - | 否 | 订单下单开始时间 |  |
| order_end_place_date | string | - | 否 | 订单下单结束时间 |  |
| order_begin_payment_date | string | - | 否 | 订单付款开始时间 |  |
| order_end_payment_date | string | - | 否 | 订单付款结束时间 |  |
| remaining_delivery_days | int | - | 否 | 剩余发货天数 | 0: 过期、1: 1 天内、2: 2 天内、3: 3 天内、5: 5 天内、6: 大于 5 天  |

## 统计
GET /api/g/package/statistics?access_token=:accessToken

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| begin_date | string | - | 否 | 开始时间，默认当天 |  |
| end_date | string | - | 否 | 结束时间，默认当天 |  |
| group_by | string | - | 否 | 可选项：organization（根据组织统计）、company（根据物流公司统计）、delivery（发货统计） |  |

### 返回结果
```json
{
    "success": true,
    "data": {
        "summary": {
            "count": 2694,
            "amount": 0
        },
        "items": [
            {
                "id": 10,
                "name": "DHL（快递业务）",
                "count": 940,
                "amount": 0,
                "percent": "34.89%"
            },
            {
                "id": 1,
                "name": "燕文",
                "count": 376,
                "amount": 0,
                "percent": "13.96%"
            }
        ]
    }
}
```