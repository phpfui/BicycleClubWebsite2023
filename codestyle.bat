@echo off
copy .htaccess.local www\\.htaccess
vendor\bin\php-cs-fixer fix -vv --allow-risky=yes
