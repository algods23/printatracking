# Portable Runtime Files

Place the Windows portable runtime binaries here before building the installer.

Required layout:

```text
desktop/runtime/
  php/
    php.exe
    php.ini
    php8apache2_4.dll
    ext/
  apache/
    bin/httpd.exe
    modules/
  mariadb/
    bin/mysqld.exe
    bin/mysql.exe
    bin/mysqldump.exe
```

PHP must include these enabled extensions:

```ini
extension=fileinfo
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

Use PHP 8.2 or newer for this Laravel 12 project.
