@echo off
call make_PMTableCompare.cmd WINDOWS
copy PMTableCompare.exe ..\build
copy php_xdebug.dll ..\build
copy php_winbinder.dll ..\build
copy php5ts.dll ..\build
copy php_soap.dll ..\build
copy php_ibm_db2.dll ..\build
copy php_excel.dll ..\build
copy libxl.dll ..\build
copy php-embed.ini ..\build
copy ..\src\include\PMSoap_3_4_14.phar ..\build









