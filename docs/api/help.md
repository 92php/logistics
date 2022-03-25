帮助文档
=======

## 帮助手册目录
GET /api/manual/index

### 返回值
```json
[
{
    "title": "name",
    "file": "filename"
},
{
    "title": "name",
    "file": "filename"
}
]
```
## 帮助手册文档
GET /api/manual/view

### 查询参数
| 参数 | 类型 | 说明 |
|---|:---:|---|
| file | string | 文件名,帮助手册目录接口中的file |