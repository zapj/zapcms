模块结构 mods/shop/
code
mods/shop/
├── mod.json                      # 模块元信息
├── Mod.php                       # 入口类（路由分发 + install/uninstall 钩子）
├── install.php                   # 建表：shop_product, shop_order, shop_order_item
├── uninstall.php                 # 删表清理
├── models/
│   ├── Product.php               # 产品模型（列表分页 / slug 查询 / 价格格式化）
│   └── Cart.php                  # 购物车模型（基于 Session）
├── controllers/
│   ├── IndexController.php       # 后台仪表盘 + 前端首页
│   ├── ProductController.php     # 产品增删改查 + 前端详情/列表
│   └── CartController.php        # 购物车（加入/更新/移除/AJAX）
└── views/
    ├── index.admin.php           # 后台仪表盘
    ├── index.home.php            # 前端首页
    ├── product.admin_list.php    # 后台产品列表
    ├── product.admin_form.php    # 后台新增/编辑表单
    ├── product.list.php          # 前端产品卡片列表
    ├── product.detail.php        # 前端产品详情页
    └── cart.index.php            # 前端购物车


```
访问方式
页面	URL	说明
后台仪表盘	/z-admin/mod/shop	统计卡片
后台产品列表	/z-admin/mod/shop/product	分页表格 + 新增/编辑/删除
前端首页	/mod/shop	推荐产品 + 购物车入口
前端产品列表	/mod/shop/product/lists	卡片展示 + 一键加购
前端产品详情	/mod/shop/product/view/{slug}	图片、价格、数量选择、加购
前端购物车	/mod/shop/cart	修改数量、删除、合计
```



启用无前缀 URL
在 config/module.php 加一行：

php
'prefixes' => [
    'shop' => 'shop',    // /shop/...  → mods/shop/
]
之后 /shop/product/view/xxx 直接访问，不需要 /mod/ 前缀。

