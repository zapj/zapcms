# zap-php-framework

## require

> PHP 7.2




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