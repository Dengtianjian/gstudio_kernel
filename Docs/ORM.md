# ORM 对象关联模型

> 最后必须走 get、getOne、delete、save 方法才会执行 SQL

```php
  $M=new ORM('pre_table1');
```

## 增加数据 insert
> insert($data)
* $data 插入的数据
```php
  $newId=$M->insert([
    "name"=>"Ming",
    "age"=>20
  ])->save();
```

### 批量增加 batchInsert
> batchInsert($fields:index Array,$insertDatas[[values],[values]...]:index array[index array])

```php
  $newId=$M->batchInsert([
    "name","age"
  ],[
    [
      "san zhang",33
    ],
    [
      "si li",44
    ]
  ])->save();
```

## 删除数据 delete

> delete($directly:boolean)

- $directly 默认 false 也就是软删除，会更新\_deleted_at 字段。 是否直接删除数据

```php
$M->where([
  "id"=>2
])->delete();

$M->where([
  "id"=>2
])->delete(true);
```

## 修改数据 update

> update($data:assoc array)

- $data 更新的数据。关联数组，key 为数据表的字段，value 为数据表的值

```php
  $M->where([
    "id"=>3
  ])->update([
    "name"=>"wu wang",
    "age"=>15
  ])->save();

  //* 递增增加where
  $M->where([
    "id"=>3
  ])->where("age",">","14")->update([
    "name"=>"wu wang",
    "age"=>15
  ])->save();

  //* 递增增加更新的数据
  $M->where([
    "id"=>3
  ])->update([
    "name"=>"wu wang",
    "age"=>15
  ])->update([
    "address"=>"Shen zhen"
  ])->save();
```

### 批量更新数据 batchUpdate

> batchUpdate($datas,$conditions)

- $datas[$updateDatas[]] 要更新的数据
- $conditons String | where[string|conditons[]]
  如果就一个字符串，就每一条更新语句都用同一条条件
  如果是一个索引数组，里面可放 SQL 语句和 where 数组

```php
$M->batchUpdate(
  [ //* 两条数据
    [
      "name"=>"YYY"
    ],
    [
      "age"=>28
    ]
  ]," WHERE `id`=2"
)->save();

$M->batchUpdate(
  [
    [
      "name"=>"YYY"
    ],
    [
      "age"=>18
    ]
  ],
  [
    [
      "id"=>"33"
    ],
    [
      "id",">",44
    ]
  ]
)->save();


$M->batchUpdate(
  [
    [
      "name"=>"YYY"
    ]
  ],[
    " WHERE `id`=66"
  ]
)->save();
```

## 查询数据 get

> get()
> 获取所有数据

```php
$M->get();

$M->where("age",">=",18)->get();
```

### 获取一条数据 getOne

> getOne()
> 本质还是 get，只是封装多一层

```php
$M->getOne();

$M->where("id","=",28)->getOne();
```

## 获取 SQL sql

> sql()
> 获取组成的 SQL
只要加上了sql方法，最后并不会执行，而是返回SQL
```php
$M->where([
  "id"=>1,
  "age"=>18
])->sql()->get();
//* 生成的SQL：select * from `TABLE NAME` WHERE `id`=1 AND `age`=18

$M->field(["id","name","school"])->sql()->get();
//* 生成的SQL：select `id`,`name`,`school` from `TABLE NAME`;

$M->where([
  "id"=>1
])->sql()->delete();
//* 生成的SQL：DELETE FROM `table name` WHERE `id`=1;

$M->insert([
  "name"=>"Mike",
  "age"=>14
])->sql()->save();
//* 生成的SQL：INSERT INTO `table name`(`name`,`age`) VALUES('Mike',14);

$M->update([
  "age"=>16
])->where([
  "id"=>28
])->sql()->save();
//* 生成的SQL：UPDATE FROM `table name` SET `age`=16 WHERE `id`=28;
```

## where 方法
> where($sql,$glue?="AND"),
* $sql:string|string[] sql语句
* $glue:string 如果是多条SQL可以指定连接符，默认是 AND
直接传sql语句
> where($fields,$value,$operator='=',$glue?="AND")
* $fields:string 字段名称
* $value:string | array 可以是数组，会自动转换成IN语句
* $operator:string 运算符 >、<、=、>=、LIKE...
* $glue:string 如果最后有多条条件语句才会起作用,并且最后一个条件语句的glue是无效的。连接语句 AND、OR... 可以直接传语句，例如：BEETWEEN 16 AND 26
通过传给函数的参数组成SQL语句
> where([[ $fields,$value,$operator,$glue ] ])
* $sql:string|string[] SQL语句
* $conditions:array 条件数组，最后会通过SQL::where转换成SQL语句
* $params:array 组成SQL语句的参数
可以传SQL或者多条SQL语句，条件数组，或者组成sql语句的参数

每用一次where方法，就会增加多一条条件，最后组成SQL
```php
$M->where("`id` = 26");
$M->where(["`id` = 26","age>=18"],"OR");

$M->where('id',11,">=","AND")->WHERE('city',"Guangzhou");
//* SQL:SELECT * FROM `table name` WHERE `id`=11 AND city="Guangzhou";

$M->where([
  [
    "id",11,"=","OR",
  ], [
    "age",28,">=","AND"，
  ]
]);
//* SQL:SELECT * FROM `talbe` WHERE `id`=11 OR age>=28;

//* 也适用于其它语句
$M->where("id","18")->delete();
//* SQL:DELETE FROM `table name` WHERE `id`=18;
```

## 查询的字段 field
> field($fieldName)
* $fieldName ...string|string[] 可以是传入多个参数，或者是一个数组
指定查询哪些字段
每用一次就增加传入的字段
```php
$M->field('id,age,name');
$M->field('id',"age","address");

$M->field(['id','age','school']);
$M->field('id')->field('age')->field('parent');
```

## limit
> limit($start|$limit,$limit?)
* $start|$limit:number 跟sql一句一样，如果传入了第二个参数这个就是起始的值。如果只传一个值，可以搭配skip使用
* $limit:number 查询的条数
```php
$M->limit(10,20); //* 从索引10开始，查询20条记录 11-30
$M->limit(15); //* 从索引0开始，查询10条记录
```

## 跳过前面几条记录 skip
> skip($start)
* $start:number
可搭配limit使用，分页
```php
$M->skip(10); //* 跳过前面10条记录，开始查询

$M->skip(5)->limit(10); //* 跳过前面5条记录，查询10条记录
```

## 分页 page
> page($pages,$pageLimit=10)
* $pages:number 页数
* $limit:number 每页的记录数量
本质还是跳动skip和limit，只是做了封装，自动计算出偏移值
```php
$M->page(2,10); ///* skip 10 limit 10
// ==等于==
$M->skip(10)->limit(10);
```

## 排序 order
> order($field,$order);
* $field:string 字段名
* $order:string(ASC,DESC) 默认是ASC 正序还是倒叙
没调用一次就会递增
```php
$M->order("id","DESC");

$M->order('age',"ASC")->order("id","DESC");
//* SQL:SELECT * FROM `table_name` ORDER BY `age` ASC, `id` DESC
```

## 计算条数 count
> count($field="*")
* $field:string 指定的字段名
计算查询到的记录数量。默认是 * ，也就是所有列。如果指定了字段名，就查询指定的字段名的数量
```php
$M->count();

$M->count("group");

$M->count("DISTINCT group"); //* 计算不重复的
```

## 参数 params
> params($params|$param,[$params]...)
* $params:array 参数。调用一次就累加一次
用于替换DiscuzX的 %参数。需要按照顺序传入
默认带一个参数就是表名，%t，后面传入的会push进数组里
```php
$M->where([
  "name"=>"%s"
])->params([
  "Jack"
]);

$M->insert([
  "age"=>"%i"
  "name"=>"%s"
])->params([
  18,
  "Mike"
]);

$M->insert([
  "age"=>"%i"
  "name"=>"%s",
  "school"=>"%s"
])->params([
  18,
  "Mike"
])->params([
  "Qinghua"
]);

$M->where("name","like","%s")->params("%gstudio_%");

$M->where([
  "age"=>"%n",
  "name"=>"%s"
])->params([18,19],"Flower");
```


## 相关查询 related
> related($tables)
* $primaryTableName:string 主表，先查出改表指定的数据，再查其它表，非SQL的关联查询
* $tables:array[ $tableName:[$localKey,$foreignKey,$saveArrayKey?]... ]
   * $tableName:关联的数据表名
   * $foreignKey:主表与localKey关联的键名
   * $relatedKey:关联的数据表的与主表关联的键名
   * $saveArrayKey:保存在主表查询到的数据的数组名称，如果没传，就用关联表名的分割连接符 ( _ ) 后的最后一个字符串作为数组键名。例如：common_member_profile -> profile
传入参数格式：
```php
[
  [
    "common_member"=>["uid","authorid"]
  ],[
    "common_member_profile"=>["uid","uid"]
  ]
]
```
```php
$M->related("common_member",[
  "common_member_profile"=>["uid","uid"],
  "forum_thread"=>["authorid","uid","threads"]
]);
/*
结果：
[
  uid:1,
  profile:[
    uid:1
  ],
  threads:[
    authorid:1,
  ]
]
*/
```

## hasOne 一对一关联查询
## hasMany 一对多关联查询
## manyHasMany 多对多关联查询
