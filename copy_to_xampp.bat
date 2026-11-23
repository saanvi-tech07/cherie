@echo off
echo ========================================================
echo   CHÉRIE BOUTIQUE — XAMPP DEPLOYMENT HELPER
echo ========================================================
echo.
echo Copying CHÉRIE project into C:\xampp\htdocs\cherie ...
echo.

if not exist "C:\xampp\htdocs" (
    echo [ERROR] Destination C:\xampp\htdocs was not found!
    echo Please make sure XAMPP is installed at C:\xampp.
    pause
    exit /b 1
)

if not exist "C:\xampp\htdocs\cherie" (
    mkdir "C:\xampp\htdocs\cherie"
)

xcopy "%~dp0*" "C:\xampp\htdocs\cherie\" /E /I /Y /Q

echo.
echo ========================================================
echo   SUCCESS! CHÉRIE has been copied to:
echo   C:\xampp\htdocs\cherie
echo ========================================================
echo.
echo NEXT STEPS:
echo 1. Open XAMPP Control Panel and START Apache and MySQL.
echo 2. Open your browser and go to:
echo    http://localhost/cherie/install.php
echo.
pause
