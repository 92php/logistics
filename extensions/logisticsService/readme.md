数据验证规则
===========

```php
['验证规则', '...其他参数']
```

## 规则列表
### notBlank [ 验证是否为空 ]
无参数

### type [ 验证数据类型 ]
1. 参数：int, float, bool, string, array

2. 示例
```php
['type', 'int']
```

### count [ 验证数组或者对象长度 ]
参数: min
```php
['count', 'min' => 1]
```

### email [ 验证邮件地址 ]
无参数

### date [ 验证日期 ]
无参数

### datetime [ 验证日期时间 ]
无参数