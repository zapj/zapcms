#!/bin/sh

#
# Copyright (c) 2023.  ZAP.CN  - ZAP CMS
#

# 后台启动
php-fpm -D
# 关闭后台启动，hold住进程
nginx -g 'daemon off;'
