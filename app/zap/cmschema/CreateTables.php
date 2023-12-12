<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\cmschema;

use zap\db\Schema;
use zap\db\TableSchema;

class CreateTables
{
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
            $table->integer('admin_id')->autoIncrement();
            $table->integer('role_id');
            $table->integer('assignment_time')->default(null);

            $table->addPrimary('admin_roles_pk','admin_id');

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
            $table->integer('comment_count')->nullable()->default(null);
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
    }
}