# 配置文件 属性和值

全大写 CONFIG 变量名，值是一个关联数组
初始化时会自动获取变量

```php
$CONFIG=[];
```

## 配置表

> ### mode 模式

配置值：String

- develoment
  开发模式，如果是视图，浏览器控制台会打印 GLANG 语言包
  后台的设置项会显示设置项的 mark
- production
  生产模式。开发模式有的都没有

```php
$CONFIG[
  "mode"=>"production"
]
```

> ### middlewares 中间件

配置值：[Function|Class]
配置值为一个索引数组，元素值为一个中间件的::class/单个函数

当执行中间时会根据 **数组的排序** 来执行
[A::class,B::class','C::class']
会从 A 开始执行一直到 C

```php
use a\b\middleware;

$CONFIG[
  "middlewares"=>[Auth::class,[],'...'];
]
```

> ### globalSets 全局设置项

配置值：[String]
当初始化了应用后，会查询数据库并且加入到$GLOBALS，通过$GLOBALS['GSETS']获取到。
配置值为一个索引数组，数组的元素值为设置项的 mark
默认白名单有：["uid", "username", "adminid", "groupid", "formhash", "charset", "setting/accessemail"]

```php
$CONFIG[
  "globalSets"=>['setMark1','setMark2','...'];
]
```

> ### dashboard -> use 使用后台

配置值：true | false
如果为 true 就会自动添加 dashboard 相关的控制器、中间件、视图

```php
$CONFIG[
  "dashboard"=>[
    "use"=>true
  ]
]
```

> ### dashboard -> tables 设置后台表的名称

后台表默认是 **表前缀*插件名称\_dashboard_set、表前缀*插件名称\_dashboard_nav**
可以通过该配置项修改表名称，不过后面可能废弃这个配置项，之前是为了兼容之前的插件

```php
$CONFIG[
  "dashboard"=>[
    "tables"=>[
      "pre_gstudio_a_dashboard_set"=>"pre_dashboard_set"
    ]
  ]
]
```

> ### token -> validPeriod token 有效期

配置项值：Number
单位：天
默认 token 有效期是 30 天，可以通过设置项修改

```php
$CONFIG[
  "token"=>[
    "validPeriod"=>15 //* 15天
  ]
]
```

> ### salt 加密用到的 salt

配置项值：String | Number
默认：gstudio_kernel

```php
$CONFIG[
  "salt"=>"AABBCC"
]
```

> ### bigGWhiteList DiscuzX 大 G 的 KEY 白名单
配置项值：[String]
自带有个接口是让前端获取 DZX 大 G 的值的，但是有些是敏感信息，所以要设置哪写可以获取的

```php
$CONFIG[
  "bigGWhiteList"=>["member","uid"]
]
```

> ### cors 跨域

- status : true | false 是否开启跨域，true 的话会自动添加 cros 中间件
- headers : [String=>String] headers 头配置

```php
$CONFIG[
  "status"=>true,
  'Access-Control-Allow-Origin' => [
    //设置允许跨域，默认包含本站site_url
  ],
  'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, X-HTTP-Method-Override',
  'Access-Control-Expose-Headers' => 'Authorization, authenticated',
  'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, OPTIONS, DELETE',
  'Access-Control-Allow-Credentials' => 'true'
]
```
