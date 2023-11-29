<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 18.03.2016
 * Time: 20:08
 */
/*
$ar = array("winbinder.php","w32lib.inc.php","wb_generic.inc.php","wb_resources.inc.php","wb_windows.inc.php","WinApi.php","winbinderLib.php","classVersion.php","classPMTableCompare.php","classWNBInifile.php","classLogger.php","pathWrapper.php","classPMObjects.php");

foreach($ar as $v)
{
    printf("%-25s : %s\n", $v, strtoupper(md5($v)));
}
*/
$ar1 = array(0=>array(1,2,3,4,5,6),
             1=>array(1,2,3,4,5,6),
             2=>array(1,2,3,4,5,6));
$ar2 = array(0=>array(1,2,3,4,7,6),
             1=>array(1,2,3,4,5,6));

$diff = array_diff_assoc($ar2[0], $ar1[0]);

$x = 0;