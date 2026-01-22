@echo off
call mago lint >mago.errors
php magoParse.php %1

