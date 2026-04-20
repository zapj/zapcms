# zap-php-framework

## PHP Min

> PHP 7.4


## Installing Zap-PHP-Framework via Composer

```
composer require zapj/zap-php-framework
```



### URL Rewrite

#### Apache
````apacheconf
Options +FollowSymLinks -Indexes
RewriteEngine On
 
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
 
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,PT,L]
````


#### Nginx
````text
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
````
