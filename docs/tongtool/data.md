通途订单数据字典
============
## 自发货订单 - 亚马逊订单
| 序号 | 字段名称 | 类型  | 备注 | 
| :---: | --- | --- | --- | 
|  1 | actualTotalPrice | number | 实付金额 | 
|  2 | assignstockCompleteTime | string | 配货时间 | 
|  3 | buyerAccountId | string | 买家id | 
|  4 | buyerCity | string | 买家城市 | 
|  5 | buyerCountry | string | 买家国家 | 
|  6 | buyerEmail | string | 买家邮箱 | 
|  7 | buyerMobile | string | 买家手机 | 
|  8 | buyerName | string   | 买家名称 | 
|  9 | buyerPhone | string   | 买家电话 | 
| 10 | buyerState | string   | 买家省份 | 
| 11 | carrier | string | 上传物流的carrier | 
| 12 | carrierType | string | 物流商类型( 0:通途API对接、 1:通途Excel文件导出、 2:通途离线生成跟踪号 3:无对接、 4:自定义Excel对接)  | 
| 13 | carrierUrl | string  | 物流网络地址 | 
| 14 | despatchCompleteTime | string  | 订单发货完成时间  | 
| 15 | dispathTypeName  | string  | 邮寄方式名称 | 
| 16 | goodsInfo | array  | 订单商品信息 | 
| 17 | insuranceIncome | number  | 买家所付保费 | 
| 18 | insuranceIncomeCurrency  | string  | 买家所付保费币种 | 
| 19 | isInvalid | string | 是否作废(0,''，null 未作废，1 手工作废 2 订单任务下载永久作废 3 拆分单主单作废 4 拆分单子单作废) | 
| 20 | isSuspended | string | 是否需要人工审核 (1需要人工审核,0或null不需要) | 
| 21 | merchantCarrierShortname  | string | 承运人简称 | 
| 22 | orderAmount | number | 订单总金额(商品金额+运费+保费) | 
| 23 | orderAmountCurrency | string  | 总金额币种 | 
| 24 | orderDetails | array | 订单明细 | 
| 25 | orderIdCode | string | 通途订单号 | 
| 26 | orderIdKey | string | 通途订单Key | 
| 27 | orderStatus | string | 订单状态 waitPacking=>等待配货 ,waitPrinting=>等待打印 ,waitingDespatching=>等待发货 ,despatched=>已发货| 
| 28 | packageInfoList | array  | 订单包裹信息 | 
| 29 | paidTime | string | 付款时间 | 
| 30 | platformCode | string | 通途中平台代码 | 
| 31 | platformFee | number | 平台手续费 | 
| 32 | postalCode | string | 买家邮编 | 
| 33 | printCompleteTime | string | 订单打印完成时间 | 
| 34 | productsTotalCurrency | string | 金额小计币种 | 
| 35 | productsTotalPrice | number | 金额小计（仅商品） | 
| 36 | receiveAddress | string | 收货地址 | 
| 37 | refundedTime | string | 退款时间 | 
| 38 | saleAccount | string | 卖家帐号 | 
| 39 | saleTime | string | 订单生成时间 | 
| 40 | salesRecordNumber | string | 平台订单号 | 
| 41 | shippingFeeIncome | number | 买家所支付运费 | 
| 42 | shippingFeeIncomeCurrency | string | 运费币种 | 
| 43 | shippingLimiteDate | string | 发货戒指时间 | 
| 44 | taxCurrency | string | 税费币种 | 
| 45 | taxIncome | number | 税费 | 
| 46 | warehouseIdKey | string | 通途仓库id key | 
| 47 | warehouseName | string | 仓库名称 | 
| 48 | webFinalFee | number | 平台佣金 | 
| 49 | webstoreOrderId | string | 平台交易号 | 
| 50 | webstore_item_site | string | 平台站点id | 
| 51 | ebayNotes | string | 订单备注 | 
| 52 | ebaySiteEnName | string | 站点名称 | 

## 平台仓订单 - FBA订单
| 序号 | 字段名称 | 类型  | 备注 | 
| :---: | --- | --- | --- | 
| 1 | buyerEmail | string | 买家邮箱 | 
| 2 | buyerName | string   | 买家名称 | 
| 3 | buyerPhoneNumber | string   | 买家电话 | 
| 4 | currency | string | 币种 | 
| 5 | shipCity | string | 城市 | 
| 6 | purchaseDate | string  | 购买时间 | 
| 7 | totalItemTax | string  | 商品税费总计  | 
| 8 | orderId  | string  | 订单号 | 
| 9 | shipAddress1 | string  | 地址1 | 
| 10 | shipAddress2 | string  | 地址2 | 
| 11 | shipAddress3  | string  | 地址3 | 
| 12 | shipServiceLevel | string | 物流服务等级 | 
| 13 | totalShippingPrice | string | 物流费用总计 | 
| 14 | shipPostalCode  | string | 邮编 | 
| 15 | salesChannel | number | 销售站点 | 
| 16 | shipCountry | string  | 国家 |
| 17 | totalShippingTax | string  | 物流税费总计 |
| 18 | shipPhoneNumber | string  | 收件人电话 |
| 19 | recipientName | string  |  |
| 20 | shipState | string  | 州/省 |
| 21 | totalItemPrice | string  | 货品总计 |
| 22 | paymentsDate | string  | 付款时间 |
| 23 | account | string | 帐号 |