# Deploying to production notes

(taken from https://dev.to/elabftw/optimizing-your-php-app-speed-3hd4 )

1. Use `composer install --no-dev -a`

2. Ensure opcache is turned on in php.ini
```
opcache_enable=1
opcache.revalidate_freq = 10 # number of seconds to check timetamps for changes
opcache.fast_shutdown=1
opcache.file_update=protection = 0 # prevents caching of files less than this number of seconds old
```

3. Consider an opcache dashboard as per https://haydenjames.io/php-performance-opcache-control-panels/

4. Ensure Twig's cache is enabled.

5. Use backslashes with standard functions.