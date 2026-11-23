@echo off
echo ========================================================
echo   CHÉRIE BOUTIQUE — XAMPP DEPLOYMENT HELPER
echo ========================================================
echo.

rem Target 1: C:\newfolder\htdocs (Your active running XAMPP root)
if exist "C:\newfolder\htdocs" (
    echo Copying into active XAMPP root: C:\newfolder\htdocs\cherie ...
    if not exist "C:\newfolder\htdocs\cherie" (
        mkdir "C:\newfolder\htdocs\cherie"
    )
    xcopy "%~dp0*" "C:\newfolder\htdocs\cherie\" /E /I /Y /Q
    echo [OK] Copied to C:\newfolder\htdocs\cherie
    echo.
)

rem Target 2: C:\xampp\htdocs (Standard XAMPP root)
if exist "C:\xampp\htdocs" (
    echo Copying into standard XAMPP root: C:\xampp\htdocs\cherie ...
    if not exist "C:\xampp\htdocs\cherie" (
        mkdir "C:\xampp\htdocs\cherie"
    )
    xcopy "%~dp0*" "C:\xampp\htdocs\cherie\" /E /I /Y /Q
    echo [OK] Copied to C:\xampp\htdocs\cherie
    echo.
)

echo ========================================================
echo   SUCCESS! Deployment complete.
echo ========================================================
echo.
echo NEXT STEPS:
echo 1. Open your browser and go to:
echo    http://localhost/cherie/install.php
echo.
pause
