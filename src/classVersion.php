<?php

/**
 * Created by PhpStorm.
 * User: walter
 * Date: 06.12.2015
 * Time: 13:16
 */
class Version
{
    /** Breite des Fenster */
    const win_width                 = 1200;
    /** Höhe des Fenster */
    const win_height                = 600;
    /** Name im Titel des Fenster */
    const win_name                  = "PMTableCompare Version";
    /** Windows ID des Html control */
    const IDC_A_HT_VERSION          = 10001;

    /** @var int|null  */
    private $mainwin                = null;

    /**
     * Version constructor.
     */
    public function __construct()
    {
        //ModalDialog  AppWindow  ResizableWindow
        $this->mainwin = wb_create_window(null, ResizableWindow, self::win_name, WBC_CENTER, WBC_CENTER, self::win_width,
                                            self::win_height, WBC_NOTIFY, WBC_HEADERSEL|WBC_REDRAW| WBC_MOUSEDOWN);
        $this->html = wb_create_control($this->mainwin, HTMLControl,'', 5, 5, self::win_width -25, self::win_height-70, self::IDC_A_HT_VERSION, 0x00000000, 0, 0);
        $parts = pathinfo(tempnam(sys_get_temp_dir(), ""));
        $htmlFile = sprintf("%s\\%s.html", $parts['dirname'],  $parts['filename']);
        ob_start();
        phpinfo();
        file_put_contents($htmlFile, ob_get_clean());
        wb_set_location($this->html, "file://".str_replace("\\", "/", realpath($htmlFile)));

    }

    /**
     * Schliessen des Fenster
     */
    public function __destruct()
    {
        if (!is_null($this->mainwin))
        {
            wb_destroy_window($this->mainwin);
            $this->mainwin = null;
        }

    }

    /**
     * Auslesen der aktuellen Windows ID
     *
     * @return int|null
     */
    public function getWindow()
    {
        return $this->mainwin;
    }
    /**
     * Behandelt alle events dieses Windows
     *
     * @param int $handle       Windows ID
     * @param int $id           Lokale control ID
     * @param int $ctrl         Windows ID des control
     * @param int $lparam1
     * @param int $lparam2
     * @param int $lparam3
     * @return bool
     */
    public function eventHandler($handle, &$id, $ctrl, $lparam1, $lparam2, $lparam3 )
    {
        switch ($id)
        {
            case IDCLOSE :
                wb_destroy_window($this->mainwin);
                $this->mainwin = null;
                break;
        }
    }

}