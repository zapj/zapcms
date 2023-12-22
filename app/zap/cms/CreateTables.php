<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\cms;


use zap\db\AlertTable;
use zap\db\Schema;
use zap\db\TableSchema;

class CreateTables
{
    protected $verbose = 0;
    public function __construct($verbose = 0)
    {
        $this->verbose = $verbose;
    }

    public function createSchema()
    {
        Schema::create('admin',function(TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->varchar('username',255);
            $table->varchar('password',255);
            $table->varchar('full_name',255);
            $table->varchar('phone_number',128);
            $table->varchar('email',255);
            $table->varchar('avatar_url',255);
            $table->varchar('last_ip',255);
            $table->integer('last_access_time');
            $table->varchar('status',32);
            $table->integer('updated_at');
            $table->integer('created_at');
            $table->addPrimary(null,'id');
            $table->addUnique('username','username');
            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });


        Schema::create('admin_logs',function(TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->integer('uid');
            $table->varchar('username',255);
            $table->varchar('title',255);
            $table->text('content');
            $table->varchar('ipaddress',128);
            $table->varchar('request_url',255);
            $table->varchar('user_agent',255);
            $table->integer('request_time');
            $table->addPrimary(null,'id');
            $table->addIndex('uid','uid');
            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('admin_menu',function(TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->varchar('title',255);
            $table->integer('pid');
            $table->varchar('path',255)->nullable()->default(null);
            $table->integer('level')->nullable()->default(0);
            $table->varchar('icon',255)->nullable()->default(null);
            $table->varchar('link_to',255)->nullable()->default(null);
            $table->varchar('link_target',64)->nullable()->default(null);
            $table->varchar('link_type',32)->nullable()->default('action');
            $table->varchar('active_rule',255)->nullable()->default(null);
            $table->varchar('show_position',32)->nullable()->default(null);
            $table->integer('sort_order')->nullable()->default(0);
            $table->integer('updated_at')->nullable()->default(null);
            $table->integer('created_at')->nullable()->default(null);
            $table->addPrimary('admin_menu_pk','id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('admin_meta',function(TableSchema $table){
            $table->integer('meta_id')->autoIncrement();
            $table->integer('object_id');
            $table->varchar('meta_name',255);
            $table->text('meta_value');
            $table->addPrimary('admin_meta_pk','meta_id');
            $table->addIndex('object_id','object_id');
            $table->addIndex('meta_name','meta_name');
            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('admin_roles',function(TableSchema $table){
            $table->integer('admin_id');
            $table->integer('role_id');
            $table->integer('assignment_time')->nullable()->default(null);

            $table->addPrimary('admin_roles_pk','admin_id','role_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('catalog',function(TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->varchar('slug',191)->default(null)->nullable();
            $table->varchar('title',255)->default(null)->nullable();
            $table->text('content')->nullable();
            $table->integer('pid')->default(null)->nullable();
            $table->varchar('path',255)->default(null)->nullable();
            $table->integer('level')->default(0)->nullable();
            $table->integer('sort_order')->default(0)->nullable();
            $table->varchar('icon',255)->default(null)->nullable();
            $table->varchar('thumb_url',255)->default(null)->nullable();
            $table->varchar('link_to',255)->default(null)->nullable();
            $table->varchar('link_type',32)->default(null)->nullable();
            $table->integer('link_object')->default(null)->nullable();
            $table->varchar('link_target',32)->default(null)->nullable();
            $table->varchar('show_position',32)->default(null)->nullable();
            $table->varchar('node_type',64)->default(null)->nullable();
            $table->integer('created_at')->default(null)->nullable();

            $table->addPrimary('catalog_pk','id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('comments',function(TableSchema $table){
            $table->integer('comment_id')->autoIncrement();
            $table->integer('node_id');
            $table->varchar('title',255);
            $table->varchar('author',255)->default(null)->nullable();
            $table->varchar('author_email',255)->nullable()->default(null);
            $table->varchar('author_url',255)->nullable()->default(null);
            $table->varchar('author_ip',255)->nullable()->default(null);
            $table->text('content');
            $table->integer('approved')->default(0)->nullable();
            $table->varchar('agent',255);
            $table->integer('parent')->default(null)->nullable();
            $table->integer('created_at')->default(null)->nullable();

            $table->addPrimary('comments_pk','comment_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('node',function(TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->integer('author_id');
            $table->varchar('node_type',64)->default(null)->nullable();
            $table->varchar('title',255);
            $table->varchar('slug',191)->nullable()->default(null);
            $table->text('excerpt')->nullable()->default(null);
            $table->text('content')->nullable()->default(null);
            $table->varchar('keywords',255)->nullable()->default(null);
            $table->varchar('description',255)->nullable()->default(null);
            $table->varchar('image',255)->nullable()->default(null);
            $table->integer('hits')->nullable()->default(0);
            $table->integer('sort_order')->nullable()->default(0);
            $table->varchar('comment_status',20)->nullable()->default(null);
            $table->integer('comment_count')->nullable()->default(0);
            $table->varchar('mime_type',128)->nullable()->default(null);
            $table->varchar('status',64)->nullable()->default(null);
            $table->integer('pub_time')->default(0)->nullable();
            $table->integer('update_time')->default(0)->nullable();
            $table->integer('add_time')->default(0)->nullable();


            $table->addPrimary('node_pk','id');
            $table->addIndex('author_id','author_id');
            $table->addIndex('status','status');
            $table->addIndex('node_type','node_type');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('node_meta',function(TableSchema $table){
            $table->integer('meta_id')->autoIncrement();
            $table->integer('object_id');
            $table->varchar('meta_name',255);
            $table->text('meta_value');

            $table->addPrimary('node_meta_pk','meta_id');
            $table->addIndex('object_id','object_id');
            $table->addIndex('meta_name','meta_name');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('node_relation',function(TableSchema $table){
            $table->integer('catalog_id');
            $table->integer('node_id');
            $table->integer('level')->nullable()->default(0);

            $table->addPrimary('node_relation_pk','catalog_id','node_id');
            $table->addIndex('node_id','node_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('node_types',function(TableSchema $table){
            $table->integer('type_id')->autoIncrement();
            $table->varchar('type_name',64);
            $table->varchar('title',255);
            $table->text('description')->nullable()->default(null);
            $table->varchar('node_type',255)->nullable()->default(null);
            $table->varchar('version',32)->nullable()->default('0.0.0');
            $table->integer('sort_order')->nullable()->default(0);
            $table->integer('status')->nullable()->default(1);
            $table->integer('updated_at')->nullable()->default(null);
            $table->integer('created_at')->nullable()->default(null);

            $table->addPrimary('node_types_pk','type_id');
            $table->addIndex('type_name','type_name');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('node_type_field',function(TableSchema $table){
            $table->integer('field_id')->autoIncrement();
            $table->integer('node_type_id');
            $table->varchar('field_name',255);
            $table->varchar('field_label',255);
            $table->text('field_value')->nullable();
            $table->integer('sort_order')->nullable()->default(0);
            $table->varchar('type',64)->nullable()->default(null);
            $table->varchar('placeholder',255)->nullable()->default(null);

            $table->addPrimary('node_type_field_pk','field_id');
            $table->addIndex('node_type_id','node_type_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('options',function (TableSchema $table){
            $table->integer('id')->autoIncrement();
            $table->varchar('option_name',255);
            $table->text('option_value')->nullable();
            $table->integer('sort_order')->nullable()->default(0);
            $table->integer('autoload')->nullable()->default(0);

            $table->addPrimary('options_pk','id');
            $table->addUnique('option_name','option_name');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('permissions',function (TableSchema $table){
            $table->integer('perm_id')->autoIncrement();
            $table->varchar('title',255);
            $table->integer('pid');
            $table->varchar('path',255)->nullable();
            $table->integer('level')->nullable()->default(0);
            $table->varchar('perm_key',255)->nullable()->default(null);
            $table->text('extras')->nullable();
            $table->varchar('description',255)->nullable()->default(null);
            $table->integer('updated_at')->nullable()->default(null);
            $table->integer('created_at')->nullable()->default(null);


            $table->addPrimary('permissions_pk','perm_id');


            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('permissions_path',function (TableSchema $table){
            $table->integer('perm_id');
            $table->integer('path_id');
            $table->integer('level')->nullable();

            $table->addPrimary('permissions_path_pk','perm_id','path_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('roles',function (TableSchema $table){
            $table->integer('role_id')->autoIncrement();
            $table->varchar('name',128);
            $table->varchar('description',255)->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->integer('updated_at')->nullable()->default(0);
            $table->integer('created_at')->nullable()->default(0);

            $table->addPrimary('roles_pk','role_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('roles_permissions',function (TableSchema $table){
            $table->integer('role_id');
            $table->varchar('perm_key',255);
            $table->text('extras')->nullable();
            $table->integer('assignment_time')->nullable()->default(null);

            $table->addPrimary('roles_permissions_pk','role_id','perm_key');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });

        Schema::create('tags',function (TableSchema $table){
            $table->integer('tag_id')->autoIncrement();
            $table->varchar('title',255);
            $table->varchar('slug',255);
            $table->integer('count')->nullable()->default(0);

            $table->addPrimary('tags_pk','tag_id');

            $table->dropTableIfExists();
            $table->setTableEngine(TableSchema::ENGINE_INNODB);
        });


    }

    public function installBaseData(){
        Schema::table('admin',function (AlertTable $table){
            $table->batchInert([
                [
                    'id'=>1,
                    'username'=>'admin',
                    'password'=>'$2y$10$NFxxIy8cV6MEjIqRYsihGuo5ZQwpVDMm43QfKdMMSI3f3wFrBA9oK',
                    'full_name'=>'admin',
                    'phone_number'=>'',
                    'email'=>'zapcms-demo@zap.cn',
                    'avatar_url'=>'',
                    'last_ip'=>'127.0.0.1',
                    'last_access_time'=>1702302665,
                    'status'=>'activated',
                    'updated_at'=>1698205141,
                    'created_at'=>1693297157
                ],
            ]);
        });


        Schema::table('admin_menu',function (AlertTable $table){
            $table->setColumns(['id', 'title', 'pid', 'path', 'level', 'icon', 'link_to', 'link_target', 'link_type', 'active_rule', 'show_position', 'sort_order', 'updated_at', 'created_at']);
            $table->batchInert([
                [1, '内容管理', 0, '1,', 1, 'fa fa-cube', 'Node', '_self', 'action', '(node/.*)', '1', 1, 1697786443, 1694683755],
                [2, '栏目', 0, '2,', 1, 'fa fa-square-poll-horizontal', 'Catalog', '_self', 'action', 'catalog/.*', '1', 2, 1694684638, 1694684638],
                [3, '系统管理', 0, '3,', 1, 'fa fa-gear', '', '_self', 'action', '(admin-menu/.*|system/.*|user/.*)', '1', 4, 1694684685, 1694684685],
                [4, '基础设置', 3, '3,4,', 2, 'fa-solid fa-angle-right', 'System@settings', '_self', 'action', '(system/.*)', '1,2', 0, 1694684704, 1694684704],
                [5, '系统菜单设置', 3, '3,5,', 2, 'fa-solid fa-angle-right', 'AdminMenu', '_self', 'action', '(admin-menu/.*|system/.*)', '1,2', 1, 1694684714, 1694684714],
                [7, '主题', 0, '7,', 1, 'fa-solid fa-wand-magic-sparkles', 'Theme', '_self', 'action', '(theme/.*)', '1', 3, 1697790328, 1697700805],
                [8, '用户管理', 3, '38,', 2, 'fa-solid fa-chevron-right', 'User', '_self', 'action', '(user/.*)', '1', 2, 1697700876, 1697700876]
            ]);
        });

        Schema::table('admin_roles',function (AlertTable $table){
            $table->setColumns(['admin_id', 'role_id', 'assignment_time']);
            $table->batchInert([
                [1, 1, 1698205141],
                [1, 2, 1698496791],
                [1, 3, 1698205141],
                [1, 5, 1698205141],
                [2, 1, 1698205144],
                [2, 2, 1698205144],
                [2, 3, 1698205144],
                [2, 5, 1698205144]
            ]);
        });

        Schema::table('catalog',function (AlertTable $table){
            $table->setColumns(['id', 'slug', 'title', 'content', 'pid', 'path', 'level', 'sort_order', 'icon', 'thumb_url', 'link_to', 'link_type', 'link_object', 'link_target', 'show_position', 'node_type', 'created_at']);
            $table->batchInert([
                [28, 'about-us', '公司简介', NULL, 0, '28,', 0, 0, NULL, NULL, '', '', 0, '', '1,4', 'page', 1698301216],
                [29, 'company-honor', '公司荣耀', NULL, 28, '28,29,', 1, 0, NULL, NULL, '', '', 0, '', '1,2,4', 'page', 1698301226],
                [30, 'news', '新闻中心', NULL, 0, '30,', 0, 0, NULL, NULL, '', '', 0, '', '1,4', 'article', 1698308332],
                [31, 'company', '公司新闻', NULL, 30, '30,31,', 1, 0, NULL, NULL, '', '', 0, '', '1,4', 'article', 1698308345],
                [33, 'products', '产品中心', NULL, 0, '33,', 0, 0, NULL, NULL, '', '', 0, '', '1,2,3,4', 'product', 1698479595],
                [34, 'phone', '手机', NULL, 33, '33,34,', 1, 0, NULL, NULL, '', '', 0, '', '1,2,3,4', 'product', 1698479610],
                [35, 'computer', '电脑', NULL, 33, '33,35,', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '1,4', 'product', 1698479633],
                [38, '行业新闻', '行业新闻', NULL, 30, '30,38,', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, '1,2,3,4', 'article', 1698568233],
                [39, 'history', '发展历程', NULL, 28, '28,39,', 1, 0, NULL, NULL, '', '', 0, '', '1,4', 'page', 1698572057],
                [40, 'contact', '联系我们', NULL, 28, '28,40,', 1, 0, NULL, NULL, '0', '', NULL, '', '1,2,3,4', 'page', 1698572075],
                [41, '--zap-link-url', '联系我们', 'page', 0, '41,', 0, 0, NULL, NULL, 'contact', 'catalog', 40, '_self', '1,2,3', 'link-url', 1698573297],
                [46, 'faq', '常见问题', NULL, 0, '46,', 0, 0, NULL, NULL, '', '', 0, '', '1', 'faq', 1698723044]
            ]);
        });

        Schema::table('node',function (AlertTable $table){
            $table->setColumns(['id', 'author_id', 'node_type', 'title', 'slug', 'excerpt', 'content', 'keywords', 'description', 'image', 'hits', 'sort_order', 'comment_status', 'comment_count', 'mime_type', 'status', 'pub_time', 'update_time', 'add_time']);
            $table->batchInert([
                [28, 1, 'catalog', '公司简介', 'about-us', NULL, '<div><img src=\"/storage/images/02522a2b2726fb0a03bb19f2d8d9524d.jpg\" style=\"width:422.667px;float:left;height:327.99px;\" class=\"note-float-left\" alt=\"02522a2b2726fb0a03bb19f2d8d9524d.jpg\" /><span>作为一家专业从事软件开发、SSL证书申请以及网站建设的公司，嘉兴领格信息技术有限公司以其专业的技术实力和丰富的经验，为客户提供优质的一站式服务。在当今数字化时代，软件开发和网站建设已经成为企业必不可少的一部分。而SSL证书的申请和应用也变得愈发重要，为保障用户数据安全和网络安全提供了有力支持。嘉兴领格信息技术有限公司正是在这一背景下应运而生，致力于为客户解决各种软件开发、SSL证书申请和网站建设的难题。首先，让我们来看看嘉兴领格信息技术有限公司的专业软件开发能力。公司拥有一支高素质的开发团队，他们具备丰富的编程经验和深厚的技术功底。无论是定制化的软件项目还是标准化的软件解决方案，嘉兴领格信息技术有限公司都能提供最佳的解决方案。他们深入了解客户的需求，并根据不同行业的特点，为其量身定制适合的软件产品。通过持续的技术创新和严格的质量控制，嘉兴领格信息技术有限公司的软件产品在市场上赢得了良好的口碑。其次，嘉兴领格信息技术有限公司在SSL证书申请方面也有着丰富的经验。随着网络攻击的不断增加，保护用户数据安全的重要性日益凸显。SSL证书作为一种加密传输协议，能够有效防止用户数据的泄露和篡改。嘉兴领格信息技术有限公司为客户提供全方位的SSL证书申请服务，从证书的申请到安装配置，全程为客户保驾护航。他们与多家知名的SSL证书颁发机构建立了良好的合作关系，确保客户能够获得最权威、最可靠的SSL证书。</span></div><div><br /></div><div>最后，让我们来了解一下嘉兴领格信息技术有限公司的网站建设能力。作为一个专业的网站建设公司，嘉兴领格信息技术有限公司拥有一支富有创造力和执行力的团队。他们能够为客户提供高质量、创新性的网站设计和开发服务。无论是企业官网、电子商务平台还是社交媒体网站，嘉兴领格信息技术有限公司都能够提供一站式解决方案。他们的设计团队注重用户体验和界面美观性，同时结合搜索引擎优化（SEO）的原则，使客户的网站能够在搜索结果中排名更靠前。</div><div><br /></div>', '', '', NULL, 0, 0, NULL, 0, 'page', 'publish', 1698478986, 1702522239, 1698478986],
                [29, 1, 'catalog', '公司荣耀', 'company-honor', NULL, '<p>作为一家专业的软件开发、SSL证书与网站建设公司，嘉兴领格信息技术有限公司以其卓越的技术实力和丰富的经验，赢得了客户的信任和赞誉。</p><p><br></p><p>专业团队：我们拥有一支高素质的开发团队，他们具备丰富的编程经验和深厚的技术功底。团队成员经过严格的选拔和培训，能够为客户提供最优质的软件开发、SSL证书申请和网站建设服务。</p><p><br></p><p>定制化解决方案：我们深入了解客户的需求，并根据不同行业的特点，为其量身定制适合的软件产品和网站设计方案。通过持续的技术创新和严格的质量控制，我们的产品和服务在市场上赢得了良好的口碑。</p><p><br></p><p>优质客户服务：我们始终将客户的利益放在首位，致力于为客户提供全方位、个性化的服务。无论是在软件开发、SSL证书申请还是网站建设过程中，我们都与客户保持密切的沟通，确保项目的顺利进行和客户的满意度。</p><p><br></p><p>合作伙伴关系：我们与多家知名的软件开发公司、SSL证书颁发机构以及网络服务提供商建立了长期稳定的合作伙伴关系。这些合作关系使我们能够为客户提供更全面、更高效的服务。</p><p><br></p><p>客户案例：我们为众多知名企业提供了软件开发、SSL证书申请和网站建设服务，并取得了显著的成果。这些成功案例充分证明了我们在行业内的专业能力和市场竞争力。</p><p><br></p><p>社会责任：我们积极履行企业社会责任，关注环境保护和社会公益事业。我们致力于通过技术创新和优质服务，为客户创造更大的价值，为社会做出贡献。</p><p><br></p><p><br></p>', '', '', NULL, 0, 0, NULL, 0, 'page', 'publish', 1698478986, 1702521147, 1698478986],
                [30, 0, 'catalog', '新闻中心', 'news', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'article', 'publish', 1698478986, 1698645355, 1698478986],
                [31, 0, 'catalog', '公司新闻', 'company', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'article', 'publish', 1698478986, 1698645358, 1698478986],
                [32, 1, 'article', '消息称vivo X100系列手机将搭载行业唯一APO长焦', '新闻测试', NULL, '<p>IT之家 10 月 30 日消息，供应链消息透露，即将发布的 vivo X100 系列将全系搭载潜望长焦镜头，并带来行业唯一获得蔡司 APO 标准认证的浮动长焦摄像头。据悉，APO 标准通常用于评价专业光学影像器材的色差控制水平，通过认证的镜头都拥有顶级的颜色校正能力。</p><p>此外，vivo X100 系列还将全系实现长焦微距功能。\r\n根据此前消息，vivo X100 系列还有多个首发技术，包括首发搭载海力士 LPDDR5T 内存，相比主流 LPDDR5X 内存，LPDDR5T 的读取速度提升达 13%，是目前行业最快的移动 DRAM。此外，新机还将首发搭载联发科天玑 9300 处理器和 vivo 6nm 自研影像芯片 V3。</p>', '', '', '/storage/images/84d9ee44e457ddef7f2c4f25dc8fa865.jpg', 115, 0, NULL, 0, NULL, 'publish', 1698308815, 1702520217, 1698308820],
                [33, 0, 'catalog', '产品中心', 'products', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'product', 'publish', 1698479595, 1698645255, 1698479595],
                [34, 0, 'catalog', '手机', 'phone', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'product', 'publish', 1698479610, 1698645259, 1698479610],
                [35, 0, 'catalog', '电脑', 'computer', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'product', 'publish', 1698479633, 1698479633, 1698479633],
                [36, 1, 'product', 'iPhone 15', 'iphone-15', NULL, '<p>iPhone 15</p><p>iPhone 15</p><p>iPhone 15</p><p>iPhone 15</p><p>iPhone 15<br></p>', '', '', '/storage/images/84d9ee44e457ddef7f2c4f25dc8fa865.jpg', 18, 0, NULL, 0, NULL, 'publish', 1698495012, 1699519805, 1698495036],
                [37, 1, 'product', 'Macbook Pro 15', 'macbook-pro-15', NULL, '<p>Macbook Pro 15</p><p>Macbook Pro 15</p><p>Macbook Pro 15</p><p>Macbook Pro 15</p><p>Macbook Pro 15<img style=\"width: 800px;\" src=\"/storage/images/3644a684f98ea8fe223c713b77189a77.jpg\"><br></p>', '', '', '/storage/images/7ef605fc8dba5425d6965fbd4c8fbe1f.jpg', 30, 0, NULL, 0, NULL, 'publish', 1698495259, 1699845643, 1698495272],
                [38, 0, 'catalog', '行业新闻', '行业新闻', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'article', 'publish', 1698568233, 1698568233, 1698568233],
                [39, 1, 'catalog', '发展历程', 'history', NULL, '<p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p><p>发展历程</p>', '', '', NULL, 0, 0, NULL, 0, 'page', 'publish', 1698572057, 1699614981, 1698572057],
                [40, 1, 'catalog', '联系我们', 'contact', NULL, '<p>如果您有任何关于软件开发、SSL证书申请或网站建设的需求，欢迎随时联系我们。</p><p><br></p><p>以下是我们的联系方式：</p><p>电话：+86 1234567890</p><p>邮箱：info@jiaxinglingge.com</p><p>地址：中国浙江省嘉兴市南湖区XX街道XX号</p><p>官方网站：https://www.jiaxinglingge.com/</p><p><br></p><p>您可以通过电话、电子邮件或访问我们的官方网站与我们取得联系。我们的专业团队将竭诚为您提供最优质的服务和解决方案。期待与您的合作！</p><p><br></p><p><br></p><p><br></p><p><br></p>', '', '', NULL, 0, 0, NULL, 0, 'page', 'publish', 1698572075, 1702521103, 1698572075],
                [41, 0, 'catalog', '联系我们', '--zap-link-url', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'link-url', 'publish', 1698573297, 1699621050, 1698573297],
                [42, 1, 'article', '华为申请“遥遥领先”商标！新品发布会曾被“遥遥领先”刷屏', '华为申请遥遥领先商标新品发布会曾被遥遥领先刷屏', NULL, '<div>近期，因华为Mate60系列手机发售，“遥遥领先”成为网络热词。</div><div>据企查查数据显示，近日，华为技术有限公司申请注册“遥遥领先”商标，国际分类为运输工具、科学仪器，当前商标状态为等待实质审查。</div><div>近期，因华为Mate60系列手机发售，“遥遥领先”成为网络热词。</div><div>据企查查数据显示，近日，华为技术有限公司申请注册“遥遥领先”商标，国际分类为运输工具、科学仪器，当前商标状态为等待实质审查。</div><div><br></div>', '', '', '/storage/images/02522a2b2726fb0a03bb19f2d8d9524d.jpg', 26, 0, NULL, 0, NULL, 'publish', 1698650814, 1702455212, 1698650832],
                [43, 1, 'product', 'iPhone 14 Pro MAX', 'iphone-14-pro-max', NULL, '<p>iPhone 14 Pro MAX</p><p>iPhone 14 Pro MAX</p><hr><p>iPhone 14 Pro MAX</p><p>iPhone 14 Pro MAX</p><p>iPhone 14 Pro MAX</p><p>iPhone 14 Pro MAX</p><p>iPhone 14 Pro MAXiPhone 14 Pro MAX</p><hr><p><br></p><hr><p>iPhone 14 Pro MAX</p><hr><p>iPhone 14 Pro MAX</p><hr><p>iPhone 14 Pro MAX<br></p>', '', '', '/storage/images/7ef605fc8dba5425d6965fbd4c8fbe1f.jpg', 1, 0, NULL, 0, NULL, 'publish', 1698653114, 1699622837, 1698653137],
                [44, 1, 'product', 'HUAWEI MateBook', 'huawei-matebook', NULL, '<p>HUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBookHUAWEI MateBook<br></p>', '', '', '/storage/images/013d407166ec4fa56eb1e1f8cbe183b9.jpg', 3, 0, NULL, 0, NULL, 'publish', 1698653141, 1699845753, 1698653159],
                [45, 1, 'product', '这是一篇中文产品测试超长标题', '这是一篇中文产品测试超长标题', NULL, '<p>这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题这是一篇中文产品测试超长标题<br></p>', '', '', NULL, 27, 0, NULL, 0, NULL, 'publish', 1698718369, 1698718387, 1698718387],
                [46, 0, 'catalog', '常见问题', 'faq', NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'faq', 'publish', 1698723044, 1702455386, 1698723044],
                [47, 1, 'faq', 'Zap CMS 的系统要求', 'zapcms-的系统要求', NULL, '<p>ZapCMS 需要以下服务器配置才能运行：</p><p><br /></p><p>需求配置</p><p>Web服务器Apache Web server 2.4或任何更高版本。（Apache Web Server 2.2 在2018年已经停止维护，因此不建议使用它）。</p><p>PHP我们推荐使用PHP 7.4+。</p><p>MySQL最低版本为5.6，建议使用最新版本。</p><p>服务器RAM越多越好。我们建议将每个脚本的内存分配（memory_limit）设置为至少128M+。</p><p><span>ZapCMS </span>可以运行在大多数操作系统，Linux （CentOS、Debian、Ubuntu）、Unix（FreeBSD、MacOS）、Windows</p>', '', '', '/storage/d1f491a404d6854880943e5c3cd9ca25.jpg', 4, 0, NULL, 0, NULL, 'publish', 1698731746, 1702522094, 1698732845],
                [48, 1, 'faq', 'Zap CMS 可以用于商业网站吗？', 'zapcms-可以用于商业网站吗', NULL, '<p>您可以用ZAPCMS搭建任何站点，未获得商业授权的客户，必须保留网站底部 【Powered by ZapCMS】 版权标识。\r\n</p><p>\r\n禁止使用ZAPCMS搭建任何违法国家法律法规的站点，已经发现立即举报至相关部门。</p>', '', '', '', 0, 0, NULL, 0, NULL, 'publish', 1698733712, 1702521319, 1698733978],
                [49, 1, 'faq', '支持什么数据库?', '支持什么数据库', NULL, '<p>推荐使用MySQL、MariaDB</p><p>SQLite3、PostgreSQL还未完全适配。</p>', '', '', '', 0, 0, NULL, 0, NULL, 'publish', 1698734821, 1699613196, 1698734905]

            ],true);
        });

        Schema::table('node_types',function (AlertTable $table){
            $table->setColumns(['type_id', 'type_name', 'title', 'description', 'node_type', 'version', 'sort_order', 'status', 'updated_at', 'created_at']);
            $table->batchInert([
                [1, 'article', '文章', '文章、新闻', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650],
                [2, 'product', '产品', '简单企业产品展示', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650],
                [3, 'page', '单页', '单页', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650],
                [4, 'faq', 'FAQ', '常见问题', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650],
                [5, 'link-url', '链接', '链接地址', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650]
            ]);
        });

        Schema::table('node_relation',function (AlertTable $table){
            $table->setColumns(['catalog_id', 'node_id', 'level']);
            $table->batchInert([
                [28, 28, 0],
                [29, 29, 1],
                [30, 32, 0],
                [30, 42, 0],
                [31, 32, 1],
                [33, 36, 0],
                [33, 37, 0],
                [33, 43, 0],
                [33, 44, 0],
                [33, 45, 0],
                [34, 36, 1],
                [34, 43, 1],
                [34, 45, 1],
                [35, 37, 1],
                [35, 44, 1],
                [38, 42, 1],
                [39, 39, 1],
                [40, 40, 1],
                [46, 47, 0],
                [46, 48, 0],
                [46, 49, 0]
            ]);
        });

        Schema::table('options',function (AlertTable $table){
            $table->setColumns(['id', 'option_name', 'option_value', 'sort_order', 'autoload']);
            $table->batchInert([
                [11, 'website.title', 'ZAP CMS', 0, 1],
                [12, 'website.slogan', 'OpenSource', 0, 1],
                [13, 'website.keywords', 'ZAP,CMS,PHP OpenSource CMS', 0, 1],
                [14, 'website.description', '', 0, 1],
                [15, 'website.icp', '浙ICP备0000000000号', 0, 1],
                [16, 'website.copyright', 'ZAPCMS 版权所有', 0, 1],
                [17, 'website.address', '公司详细地址', 0, 1],
                [18, 'website.tel', '057388888888', 0, 1],
                [19, 'website.head_script', '', 0, 1],
                [20, 'website.foot_script', '', 0, 1],
                [21, 'website.theme', 'basic', 0, 1],
                [22, 'website.route', '0', 0, 0],
                [23, 'website.route_rule', NULL, 0, 0],
                [24, 'website.email', 'admin@yourdomain.com', 0, 1],
                [33, 'cache.ttl', '10000', 0, 0],
                // zap cms install info
                [50, 'zap_cms.install_date', time(), 0, 0],

                // mail settings
                [60, 'mail.engine', 'mail', 0, 0],
                [61, 'mail.host', 'localhost', 0, 0],
                [62, 'mail.username', '', 0, 0],
                [63, 'mail.password', '', 0, 0],
                [64, 'mail.port', '25', 0, 0],
                [65, 'mail.timeout', '5', 0, 0],

                [1034, 'basic_home.slide', '[{\"img_path\":\"\\/themes\\/basic\\/img\\/banner1.png\",\"link\":\"https:\\/\\/zap.cn\"},{\"img_path\":\"\\/themes\\/basic\\/img\\/banner2.png\",\"link\":\"https:\\/\\/zap.cn\"}]', 0, 0],
                [1036, 'basic_home.service_title', '服务项目', 0, 0],
                [1037, 'basic_home.service_subtitle', 'SERVICES', 0, 0],
                [1038, 'basic_home.service1_title', '软件开发与定制服务', 0, 0],
                [1039, 'basic_home.service1_content', '根据客户的具体需求进行软件设计和开发，以满足客户的个性化需求。', 0, 0],
                [1040, 'basic_home.service1_icon', 'fa fa-code', 0, 0],
                [1041, 'basic_home.service2_title', '移动应用开发', 0, 0],
                [1042, 'basic_home.service2_content', '提供移动应用开发服务，帮助他们将业务拓展到移动设备上。无论是为iOS、Android还是跨平台开发，软件公司都能够提供专业的技术支持和解决方案。', 0, 0],
                [1043, 'basic_home.service2_icon', 'fa fa-cubes', 0, 0],
                [1044, 'basic_home.service3_title', '网站设计与开发', 0, 0],
                [1045, 'basic_home.service3_content', '网站是企业展示形象、传递信息的重要渠道。软件公司为客户提供网站设计与开发服务，帮助他们建立专业、易用的网站。从网站的规划、设计到开发和测试，软件公司都能够提供全方位的支持，确保网站能够满足客户的需求并具有良好的用户体验。', 0, 0],
                [1046, 'basic_home.service3_icon', 'fa fa-television', 0, 0],
                [1047, 'basic_home.service4_title', '云计算与数据管理', 0, 0],
                [1048, 'basic_home.service4_content', '随着数据量的不断增长，数据管理和存储成为了一个重要的问题。软件公司提供云计算和数据管理服务，帮助客户有效地管理和分析数据。通过使用云计算技术，客户可以将数据存储在云端，实现数据的高效管理和共享。同时，软件公司还提供数据分析和挖掘服务，帮助客户从数据中获取有价值的信息和洞察。', 0, 0],
                [1049, 'basic_home.service4_icon', 'fa fa-cloud', 0, 0],
                [1050, 'basic_home.service5_title', '人工智能与机器学习', 0, 0],
                [1051, 'basic_home.service5_content', '人工智能和机器学习是当前科技领域的热门话题。软件公司利用先进的技术和算法，为客户提供人工智能和机器学习解决方案。这些解决方案可以帮助客户实现自动化、智能化的业务处理，提高工作效率和准确性。无论是自然语言处理、图像识别还是智能推荐系统，软件公司都能够提供专业的技术支持和解决方案。', 0, 0],
                [1052, 'basic_home.service5_icon', 'fa fa-graduation-cap', 0, 0],
                [1053, 'basic_home.service6_title', '安全与隐私保护', 0, 0],
                [1054, 'basic_home.service6_content', '随着网络攻击和数据泄露事件的不断增多，安全与隐私保护成为了一个紧迫的问题。软件公司提供安全与隐私保护服务，帮助客户保护其数据和系统的安全。通过使用先进的安全技术和策略，软件公司可以有效地防止黑客攻击和数据泄露，确保客户的数据和系统的安全性。', 0, 0],
                [1055, 'basic_home.service6_icon', 'fa fa-diamond', 0, 0],
                [1056, 'basic_home.about_us', '<p style="margin-right: 0px; margin-bottom: 0px; margin-left: 0px; outline: none; padding: 0px; line-height: 26px; word-break: break-word;"><img src="http://zapcms.local/themes/basic/img/aboutus.png" class="note-float-right" style="float: right;"><span >&nbsp; &nbsp; &nbsp; &nbsp;作为一家专业从事软件开发、SSL证书申请以及网站建设的公司，嘉兴领格信息技术有限公司以其专业的技术实力和丰富的经验，为客户提供优质的一站式服务。在当今数字化时代，软件开发和网站建设已经成为企业必不可少的一部分。而SSL证书的申请和应用也变得愈发重要，为保障用户数据安全和网络安全提供了有力支持。嘉兴领格信息技术有限公司正是在这一背景下应运而生，致力于为客户解决各种软件开发、SSL证书申请和网站建设的难题。</span><span >首先，让我们来看看嘉兴领格信息技术有限公司的专业软件开发能力。公司拥有一支高素质的开发团队，他们具备丰富的编程经验和深厚的技术功底。无论是定制化的软件项目还是标准化的软件解决方案，嘉兴领格信息技术有限公司都能提供最佳的解决方案。他们深入了解客户的需求，并根据不同行业的特点，为其量身定制适合的软件产品。通过持续的技术创新和严格的质量控制，嘉兴领格信息技术有限公司的软件产品在市场上赢得了良好的口碑。</span><span >其次，嘉兴领格信息技术有限公司在SSL证书申请方面也有着丰富的经验。随着网络攻击的不断增加，保护用户数据安全的重要性日益凸显。SSL证书作为一种加密传输协议，能够有效防止用户数据的泄露和篡改。嘉兴领格信息技术有限公司为客户提供全方位的SSL证书申请服务，从证书的申请到安装配置，全程为客户保驾护航。他们与多家知名的SSL证书颁发机构建立了良好的合作关系，确保客户能够获得最权威、最可靠的SSL证书。<br></span></p><p ><span >&nbsp; &nbsp; &nbsp; &nbsp;最后，让我们来了解一下嘉兴领格信息技术有限公司的网站建设能力。作为一个专业的网站建设公司，嘉兴领格信息技术有限公司拥有一支富有创造力和执行力的团队。他们能够为客户提供高质量、创新性的网站设计和开发服务。无论是企业官网、电子商务平台还是社交媒体网站，嘉兴领格信息技术有限公司都能够提供一站式解决方案。他们的设计团队注重用户体验和界面美观性，同时结合搜索引擎优化（SEO）的原则，使客户的网站能够在搜索结果中排名更靠前。</span></p>', 0, 0]

            ],true);
        });

        Schema::table('permissions',function (AlertTable $table){
            $table->setColumns(['perm_id', 'title', 'pid', 'path', 'level', 'perm_key', 'extras', 'description', 'updated_at', 'created_at']);
            $table->batchInert([
                [7, '用户管理', 0, '7,', 0, 'user', '{\"modify\":\"修改\",\"access\":\"访问\"}', '', 1698204898, 1698204409]
            ]);
        });

        Schema::table('permissions_path',function (AlertTable $table){
            $table->setColumns(['perm_id', 'path_id', 'level']);
            $table->batchInert([
                [7, 7, 0]
            ]);
        });

        Schema::table('roles',function (AlertTable $table){
            $table->setColumns(['role_id', 'name', 'description', 'status', 'updated_at', 'created_at']);
            $table->batchInert([
                [1, '超级管理员', '系统管理员', 1, 1698571937, 1697774895],
                [2, '编辑', '发布文章', 1, 1698571949, 1698110905],
                [3, '内容管理员', '内容编辑', 1, 1698571944, 1698110928],
                [5, '发布者', '发布文章', NULL, 1698205114, 1698205114]
            ]);
        });

        Schema::table('roles_permissions',function (AlertTable $table){
            $table->setColumns(['role_id', 'perm_key', 'extras', 'assignment_time']);
            $table->batchInert([
                [1, 'admin_menu_1', '', 1698571937],
                [1, 'admin_menu_2', '', 1698571937],
                [1, 'admin_menu_3', '', 1698571937],
                [1, 'admin_menu_4', '', 1698571937],
                [1, 'admin_menu_5', '', 1698571937],
                [1, 'admin_menu_7', '', 1698571937],
                [1, 'admin_menu_8', '', 1698571937],
                [1, 'user', 'modify,access', 1698571937],
                [2, 'admin_menu_1', '', 1698571949],
                [2, 'admin_menu_2', '', 1698571949],
                [2, 'admin_menu_3', '', 1698571949],
                [2, 'admin_menu_4', '', 1698571949],
                [2, 'admin_menu_5', '', 1698571949],
                [2, 'admin_menu_7', '', 1698571949],
                [2, 'admin_menu_8', '', 1698571949],
                [2, 'user', '', 1698571949],
                [3, 'admin_menu_1', '', 1698571945],
                [3, 'admin_menu_2', '', 1698571945],
                [3, 'admin_menu_3', '', 1698571945],
                [3, 'admin_menu_4', '', 1698571945],
                [3, 'admin_menu_5', '', 1698571945],
                [3, 'admin_menu_7', '', 1698571945],
                [3, 'admin_menu_8', '', 1698571945],
                [3, 'user', '', 1698571945],
                [5, 'admin_menu_1', '', 1698205114],
                [5, 'admin_menu_2', '', 1698205114],
                [5, 'admin_menu_3', '', 1698205114],
                [5, 'admin_menu_4', '', 1698205114],
                [5, 'admin_menu_5', '', 1698205114],
                [5, 'admin_menu_7', '', 1698205114],
                [5, 'admin_menu_8', '', 1698205114],
                [5, 'user', 'modify,access', 1698205114]
            ]);
        });




    }

    public function installDemoData()
    {
        
    }
}
