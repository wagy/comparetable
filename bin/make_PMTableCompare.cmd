@echo on
@del PMTableCompare.exe
embeder2.exe new PMTableCompare
embeder2.exe main PMTableCompare ../src/bootstrap.php
set WINTYPE=CONSOLE
if "%1%" == "WINDOWS" set WINTYPE=WINDOWS
embeder2.exe type PMTableCompare %WINTYPE%
embeder2.exe add PMTableCompare ../src/include/winbinder.php winbinder.php
embeder2.exe add PMTableCompare ../src/include/w32lib.inc.php w32lib.inc.php
embeder2.exe add PMTableCompare ../src/include/wb_generic.inc.php wb_generic.inc.php
embeder2.exe add PMTableCompare ../src/include/wb_resources.inc.php wb_resources.inc.php
embeder2.exe add PMTableCompare ../src/include/wb_windows.inc.php wb_windows.inc.php
embeder2.exe add PMTableCompare ../src/include/WinApi.php WinApi.php
embeder2.exe add PMTableCompare ../src/include/winbinderLib.php winbinderLib.php
embeder2.exe add PMTableCompare ../src/classVersion.php classVersion.php
embeder2.exe add PMTableCompare ../src/classPMTableCompare.php classPMTableCompare.php
embeder2.exe add PMTableCompare ../src/classTableCompare.php classTableCompare.php
embeder2.exe add PMTableCompare ../src/classWNBInifile.php classWNBInifile.php
embeder2.exe add PMTableCompare ../src/classLogger.php classLogger.php
embeder2.exe add PMTableCompare ../src/pathWrapper.php pathWrapper.php
embeder2.exe add PMTableCompare ../src/classPMObjects.php classPMObjects.php
embeder2.exe add PMTableCompare ../src/classExcelWriterLibxl.php classExcelWriterLibxl.php

embeder2.exe list PMTableCompare
