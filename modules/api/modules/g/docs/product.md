产品接口
=======

## 新增
POST /api/g/product/create?access_token=:accessToken

## 修改
PUT/PATCH /api/g/product/update?id=:id&access_token=:accessToken

### 参数说明
| 参数 | 类型 | 必填 | 默认值 | 说明 |
|---|:---:|:---:|:---:|---|
| category_id | int | 是 | 无 | 分类id |
| english_name | string | 是 | 无 | 产品英文名称 |
| chinese_name | string | 是 | 无 | 产品中文名称 |
| sku | string | 是 | 无 | 产品sku |
| image | file | 是 | 无 | 图片 |
| type | int | 是 | 0 | 商品类型 |
| sale_method | int | 否 | 0 | 销售方式 |
| key | string | 否 | 无 | Key |
| weight | int | 否 | 0 | 重量 |
| size_length | float | 否 | 0 | 长 |
| size_width | float | 否 | 0 | 宽 |
| size_height | float | 否 | 0 | 高 |
| allow_offset_weight | float | 否 | 0 | 允许称重误差 |
| stock_quantity | int | 否 | 0 | 库存 |
| status | int | 否 | 0 | 商品状态 |
| price | float | 否 | 1 | 价格 |
| development_member_id | int | 否 | 1 | 开发员 |
| purchase_member_id | int | 否 | 0 | 采购员 |
| ext_images | array | 是 | 无 | 产品图片, 如果没有则填写空数组如：ext_images[], 有的话格式应为 ext_images[0=>[id=0,path='xxxx路径.jpg'],1=> [id=0,path='xxx路径.jpg']] |
| ext_sku_map | array | 是 | 无 | sku配对, 如果没有则填写空数组如：ext_sku_map[], 有的话格式应为  ext_sku_map[0=>[id=0,'product_id'=>1,'value'=>'xxx'],1=>[id=0,'product_id'=>2,'value'=>'xxx']] |
| ext_combine | array | 是 | 无 | 组合商品, 如果没有则填写空数组如：ext_combine[], 有的话格式应为  ext_combine[0=>[id=0,'product_id'=>1,'child_product_id'=>2],1=>[id=0,'product_id'=>2,'child_product_id'=>3]] |



## 删除
DELETE /api/g/product/delete?id=:id&access_token=:accessToken


## 查询
GET /api/g/product/index?access_token=:accessToken&expand=:expand

### 查询参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| category_id | int | - | 否 | 分类 |  |
| type | string | - | 否 | 商品类型 |  |
| sale_method | string | - | 否 | 销售方式 |  |
| status | int | - | 否 | 商品状态 | |
| sku | int | - | 否 | sku | 支持模糊查询 |
| chinese_name | int | - | 否 | 中文名 | 支持模糊查询 |
### expand参数
| 参数 | 值类型 | 默认值 | 必填 | 说明 | 备注 |
| --- | :---: | :---: | :---: | --- | --- |
| children | string | - | 否 | 组合商品 |  |
| Images | string | - | 否 | 产品图片 |  |
| sku-map | string | - | 否 | 商品配对 |  |