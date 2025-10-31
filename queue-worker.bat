@echo off
title KYC Email Queue Worker
color 0A
echo ============================================
echo  KYC Portal - Email Queue Worker
echo  Keep this window open to process emails
echo ============================================
echo.

:loop
echo [%date% %time%] Starting queue worker...
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
echo [%date% %time%] Queue worker stopped. Restarting in 5 seconds...
timeout /t 5
goto loop
