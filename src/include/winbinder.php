<?php

/*******************************************************************************

 WINBINDER - The native Windows binding for PHP for PHP

 Copyright � Hypervisual - see LICENSE.TXT for details
 Author: Rubem Pechansky (http://winbinder.org/contact.php)

 Main inclusion file for WinBinder

*******************************************************************************/

if (EMBEDED)
{
    require_once('res:///PHP/'.strtoupper(md5('w32lib.inc.php')));
    require_once('res:///PHP/'.strtoupper(md5('wb_generic.inc.php')));
    require_once('res:///PHP/'.strtoupper(md5('wb_resources.inc.php')));
    require_once('res:///PHP/'.strtoupper(md5('wb_windows.inc.php')));
    require_once('res:///PHP/'.strtoupper(md5('WinApi.php')));
}
else
{
    $_mainpath = pathinfo(__FILE__);
    $_mainpath = $_mainpath["dirname"] . "/";

    // WinBinder PHP functions
    include_once $_mainpath . "w32lib.inc.php";
    include_once $_mainpath . "wb_generic.inc.php";
    include_once $_mainpath . "wb_resources.inc.php";
    include_once $_mainpath . "wb_windows.inc.php";
    include_once $_mainpath . "WinApi.php";
}





//------------------------------------------------------------------ END OF FILE

?>
