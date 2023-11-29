<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 05.12.2015
 * Time: 15:30
 */
//$_SERVER['PHP_IDE_CONFIG'] = "PHPSTORM";

$version = "1.0.0";



if (defined('EMBEDED') && EMBEDED)
{
    require_once('res:///PHP/'.strtoupper(md5('winbinder.php')));
    require_once('res:///PHP/'.strtoupper(md5('pathWrapper.php')));
    require_once('PMSoap_3_4_14.phar');
}
else
{
    define('EMBEDED', false);
    require_once('include/winbinder.php');
    require_once('pathWrapper.php');
    require_once('include/PMSoap_3_4_14.phar');
}
//Class loader
spl_autoload_register(function ($class) {

    Global $lg;

    if (is_object($lg))
    {
        $lg->msg("Load class :".$class);
    }
    if ($class == "PMSoapClient") return;
    if (EMBEDED)
    {
        require_once('res:///PHP/' . strtoupper(md5('class' . $class . '.php')));
    }
    else
    {
        require_once('class' . $class . '.php');
    }
}
);

$lg = new Logger("pmTableCompare.log");
//$lg->setStdOutput(TRUE, TRUE, TRUE); Darf nicht gesetzt sein im WINDOWS mode
$lg->setShutDownHandler();
$lg->setErrorHandler(FALSE);
$lg->setAssertHandler();
$lg->setExceptionHandler();
$lg->infoMsg("Start PMTableCompare Version ".$version);

init_dlls();

//ini File des Programm öffnen
$ini = new WNBInifile("PMTableCompare.ini", "Global");

/** Registrierung des shut down handler */
$lg->registerShutDownFunction('pmExtenderShutDown');

//$argv[1] = "-version";

if (isset($argv[1]))
{
    $lg->infoMsg("Start ohne winbinder");
    switch(strtolower($argv[1]))
    {
        case "-version" :
            /** @var Version $verwin */
            $verwin = new Version();
            eventHandlerWindow($verwin);
            wb_main_loop();
            break;
        case "-exec" :
            break;
        default :
            wb_message_box(null, "Aufruf PMTableCompare \nParameter: -version\n                    -exec <phpfile> ", "PMTableCompare", WBC_WARNING);
    }

}
else
{
    $lg->infoMsg("Start mit winbinder");
    //Start PMTableCompare
    $pmtc = new PMTableCompare($version, $ini, "msgHandler");
    eventHandlerWindow($pmtc);
    wb_main_loop();
}

/**
 * Statische Funktion für den Eventhandler
 *
 * @param int|object $handle       Windows ID
 * @param int $id           Lokale control ID
 * @param int $ctrl         Windows ID des control
 * @param int $lparam1
 * @param int $lparam2
 * @param int $lparam3
 * @internal param int $window Windows ID
 * @return bool|int
 */
function eventHandlerWindow($handle, $id=0, $ctrl=0, $lparam1=0, $lparam2=0, $lparam3=0)
{
    /** @var PMTableCompare|Version $window */
    static $window = null;
    if ( is_null($window)  ) {
        $window = $handle;
        return wb_set_handler( $window->getWindow(), __FUNCTION__ );
    }
    return $window->eventHandler( $handle, $id, $ctrl, $lparam1, $lparam2, $lparam3 );
}

/**
 * Initialisierung der Multi-Statusbar
 *
 * @param $statusHwnd
 * @param $parts
 */
function setMultiStatusBar($statusHwnd, $parts)
{
    $strings = array();
    $sizes = null;
    // Split Part Array
    $parts_count = count($parts);
    for ($i = 0; $i < $parts_count; $i++)
    {
        $strings[] = (string)$parts[$i][0];
        $sizes[] = (int)$parts[$i][1];
    }

    // Build the "pack" call
    $size = call_user_func_array('pack', array_merge( array('i' . $parts_count) ,  $sizes));

    // Create the Bars
    wb_send_message($statusHwnd, SB_SETPARTS, count($sizes), wb_get_address($size));

    // Set the Text
    for($p=0;$p < count($strings);$p++)
    {
        wb_send_message($statusHwnd, SB_SETTEXT, $p, wb_get_address($strings[$p]));
    }
}

/**
 * Ausgabe von Text in der Statusbar
 *
 * @param int $statusHwnd
 * @param string $text
 * @param int $pos
 */
function multiStatusBarText($statusHwnd, $text, $pos=1)
{
    $txt = mb_convert_encoding($text, "CP1252", "UTF-8");
    wb_send_message($statusHwnd, SB_SETTEXT, $pos, wb_get_address($txt));
}

/**
 * Intitialisierung der Windows DLL
 */
function init_dlls()
{
    global $USER, $KERNEL, $GDI, $USER32, $OLE32 ;

    // Most common Windows libraries

    $USER = wb_load_library("USER");
    $USER32 = wb_load_library("USER32");
    $KERNEL = wb_load_library("KERNEL");
    $GDI = wb_load_library("GDI");
    $OLE32 = wb_load_library("OLE32.DLL");

    // Declare constants related to Windows and WinBinder structures

    define("WBOBJ",			    "Vhwnd/Vid/Vuclass/litem/lsubitem/Vstyle/Vparent/Vhandler/Vlparam/V8lparams/Vpbuffer");
    define("WBOBJ_RAW",		    "V3l2V13");
    define("WBOBJ_SIZE",	    72);

    define("IDC_ARROW",		    32512);
    define("IDC_SIZEWE", 	    32644);
    define("IDC_SIZENS", 	    32645);
    define("IDC_IBEAM", 	    32513);
    define("IDC_WAIT", 		    32514);
    define("IDC_CROSS", 	    32515);
    define("IDC_UPARROW", 	    32516);
    define("IDC_SIZE", 		    32646);
    define("IDC_ICON", 		    32512);
    define("IDC_SIZENWSE", 	    32642);
    define("IDC_SIZENESW", 	    32643);
    define("IDC_SIZEALL", 	    32646);
    define("IDC_NO", 		    32648);
    define("IDC_APPSTARTING",   32650);
    define("IDC_HELP", 		    32651);
    define("IDC_HAND", 		    32649);
    define("API_SetParent",     wb_get_function_address("SetParent", $USER32));
    define("SB_SETPARTS",       1028);
    define("SB_SETTEXT",        1025);

    define("WM_USER", 0x0400);
    define("EM_GETEVENTMASK",   WM_USER +59);
    define("EM_SETEVENTMASK",   WM_USER +69);
    define("EM_AUTOURLDETECT",  WM_USER +91);

    define("EM_SETSEL",         0x00B1);
    define("EM_REPLACESEL",     0x00C2);
    define("EM_LINEINDEX",      0x00BB);
    define("EM_SETREADONLY",    0x00CF);

    define("ENM_LINK",          0x04000000);
}
/**
 * Windows ID aus Object auslesen
 *
 * @param int $wbHwnd
 * @return mixed
 */
function getHwnd($wbHwnd)
{
    $wbobj = unpack(WBOBJ, wb_peek($wbHwnd, WBOBJ_SIZE));
    return $wbobj["hwnd"];
}

/**
 * Setzen eines Cursor
 *
 * @param int $parm
 */
function SetMyCursor($parm)
{
    global $USER;
    static $pfn = NULL, $pfn2 = NULL;

    if($pfn === NULL)
        $pfn = wb_get_function_address("SetCursor", $USER);
    if($pfn2 === NULL)
        $pfn2 = wb_get_function_address("LoadCursor", $USER);

    $hcursor = wb_call_function($pfn2, array(0, $parm));
    wb_call_function($pfn, array($hcursor));
}

/**
 * Message Handler für abgeleitete Klassen
 *
 * @param string    $msg
 * @param int       $id
 */
function msgHandler($msg, $id)
{
    GLOBAL $lg;

    if (is_object($lg))
    {
        switch ($id)
        {
            case ERROR :
                $lg->errorMsg($msg);
                break;
            case WARNING :
                $lg->warnMsg($msg);
                break;
            case INFO :
                $lg->infoMsg($msg);
                break;
            case -1 :    //write without crlf
                $lg->msg($msg, FALSE);
                break;
            default    :
                $lg->msg($msg);
        }
    }
}

/**
 * Shutdown Funktion wird beim schliessen des Programms aufgerufen.
 * Wenn ein Fehler vorhanden ist wird dieser mit einer Messagebox ausgegeben.
 */
function pmExtenderShutDown()
{
    GLOBAL $lg, $ini;

    $err = error_get_last();
    if ($err['type'] == E_ERROR)
    {
        $msg = sprintf("%s, FILE = %s, LINE =  %s", $err['message'], $err['file'], $err['line']);
        $lg->errorMsg($msg);
        wb_message_box(null, $msg, "Fehler", WBC_WARNING);
    }
    //close logger
    if (is_object($ini))
    {
        $ini->save();
    }
    if (is_object($lg))
    {
        $lg->close();
    }
    $lg = null;
}