<?php

/**
 * Created by PhpStorm.
 * User: walter
 * Date: 18.03.2016
 * Time: 08:26
 */
class PMTableCompare
{
    /** Breite des Fenster */
    const win_width                 = 1000;
    /** Höhe des Fenster */
    const win_height                = 600;
    /** Windows ID des PM Benutzer */
    const IDC_B_EB_USR              = 11001;
    /** Windows ID des PM Passwort */
    const IDC_B_EB_PWD              = 11002;
    /** Windows ID des PM Serverr */
    const IDC_B_EB_SRV              = 11003;
    /** Windows ID des PM Port */
    const IDC_B_EB_PRT              = 11004;
    /** Windows ID des Tabelle 1 */
    const IDC_B_EB_TAB1             = 11005;
    /** Windows ID des Suffix 1 */
    const IDC_B_EB_SUX1             = 11006;
    /** Windows ID des Tabelle 2 */
    const IDC_B_EB_TAB2             = 11007;
    /** Windows ID des Suffix 2 */
    const IDC_B_EB_SUX2             = 11008;
    /** Windows ID von Spalten */
    const IDC_B_EB_COL              = 11009;
    /** Windows ID von Spalten auslesen */
    const IDC_B_PB_COL              = 11010;
    /** Windows ID des Liste */
    const IDC_B_LV_DIFF             = 11011;
    /** Windows ID des Excelfile Pfad */
    const IDC_B_EB_EXCEL            = 11012;
    /** Windows ID zur Auswahl des Excelfile Pfad */
    const IDC_B_PB_EXCEL            = 11013;




    /** Windows ID des Ausführen */
    const IDC_B_PB_EXEC             = 11091;
    /** Windows ID des Abbrechen */
    const IDC_B_PB_EXIT             = 11092;


    /** Positionen im Hauptfenster */
    const PX1                       = 5;
    const PX2                       = 100;
    const PX3                       = 820;
    const PX4                       = 870;


    /** @var int|null  */
    private $mainwin                = null;
    /** Name im Titel des Fenster */
    private $win_name               = "PMTableCompare Version ";
    /** @var null|array             Liste mit Windows ID der controls  */
    private $ctrl                   = null;
    /** @var null|int               Windows ID der Statusbar */
    private $statusbar              = null;
    /** @var null|int               Windows ID der Progressbar */
    private $gauge                  = null;
    /** @var null|callable          Messagehandler im Hauptprogramm  */
    private $msgHandler             = null;
    /** @var null| WNBInifile       ini File des Programm */
    private $ini                    = null;
    /** @var null| TableCompare      */
    private $tableCompare           = null;
    /** @var null|string            Pfad zum Excelprogramm */
    private $excelCmd               = null;


    /**
     * Version constructor.
     */
    public function __construct($version, $ini, $msgHandler)
    {
        $this->ini = $ini;
        $this->msgHandler = $msgHandler;
        //ModalDialog  AppWindow  ResizableWindow
        $this->mainwin = wb_create_window(null, AppWindow, $this->win_name.$version, WBC_CENTER, WBC_CENTER,
                            self::win_width, self::win_height, WBC_NOTIFY, WBC_HEADERSEL|WBC_REDRAW| WBC_DBLCLICK |WBC_MOUSEDOWN);
        $yofs = 5;
        wb_create_control($this->mainwin, Label, 'PM Benutzer', self::PX1, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_USR] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_USR),  self::PX2, $yofs, 100, 20, self::IDC_B_EB_USR, 0x00000000, 0, 0);
        wb_create_control($this->mainwin, Label, 'Passwort', self::PX2+140, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_PWD] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_PWD),  self::PX2+200, $yofs, 100, 20, self::IDC_B_EB_PWD, WBC_MASKED, 0, 0);
        wb_create_control($this->mainwin, Label, 'PM Server', 440, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_SRV] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_SRV),  500, $yofs, 100, 20, self::IDC_B_EB_SRV, 0, 0, 0);
        wb_create_control($this->mainwin, Label, 'PM Server Port', 660, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_PRT] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_PRT),  750, $yofs, 50, 20, self::IDC_B_EB_PRT, 0, 0, 0);

        $yofs = 30;
        wb_create_control($this->mainwin, Label, 'Tabellenpfad  1', self::PX1, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_TAB1] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_TAB1),  self::PX2, $yofs, 700, 20, self::IDC_B_EB_TAB1, 0x00000000, 0, 0);
        wb_set_focus($this->ctrl[self::IDC_B_EB_TAB1]);
        wb_create_control($this->mainwin, Label, 'Suffix 1', self::PX3, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_SUX1] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_SUX1),  self::PX4, $yofs, 100, 20, self::IDC_B_EB_SUX1, 0x00000000, 0, 0);

        $yofs = 53;
        wb_create_control($this->mainwin, Label, 'Tabellenpfad  2', self::PX1, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_TAB2] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_TAB2),  self::PX2, $yofs, 700, 20, self::IDC_B_EB_TAB2, 0x00000000, 0, 0);
        wb_set_focus($this->ctrl[self::IDC_B_EB_TAB2]);
        wb_create_control($this->mainwin, Label, 'Suffix 2', self::PX3, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_SUX2] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_SUX2),  self::PX4, $yofs, 100, 20, self::IDC_B_EB_SUX2, 0x00000000, 0, 0);

        $yofs = 80;
        wb_create_control($this->mainwin, Label, 'Tabellenspalten', self::PX1, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_COL] =  wb_create_control($this->mainwin, EditBox,'',  self::PX2, $yofs, 700, 20, self::IDC_B_EB_COL, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_PB_COL] =  wb_create_control($this->mainwin, PushButton, 'Tabellenspalten einlesen',  self::PX3, $yofs, 150, 20, self::IDC_B_PB_COL, 0x00000000, 0, 0);

        $yofs = 110;
        $stdheader =  array( array("Tabellenname 1", 420), array("Tabellenname 2", 420), array("Anzahl differenzen", 115, WBC_CENTER));
        $this->ctrl[self::IDC_B_LV_DIFF] =  wb_create_control($this->mainwin, ListView, "Head1,Head2,Head3",  self::PX1, $yofs, 965, 380, self::IDC_B_LV_DIFF, WBC_VISIBLE | WBC_ENABLED | WBC_LINES | WBC_SORT | WBC_MOUSEDOWN, 0, 0);
        wb_set_text($this->ctrl[self::IDC_B_LV_DIFF], $stdheader);

        $yofs = 493;
        wb_create_control($this->mainwin, Label, 'Excelfile Pfad', self::PX1, $yofs+2, 95, 15, 0, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_EB_EXCEL] =  wb_create_control($this->mainwin, EditBox, $this->ini->get(self::IDC_B_EB_EXCEL),  self::PX2, $yofs, 415, 20, self::IDC_B_EB_EXCEL, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_PB_EXCEL] =  wb_create_control($this->mainwin, PushButton, '...',  520, $yofs, 30, 20, self::IDC_B_PB_EXCEL, 0x00000000, 0, 0);

        $yofs = 517;
        $this->ctrl[self::IDC_B_PB_EXEC] =  wb_create_control($this->mainwin, PushButton, 'Vergleichen',  self::win_width / 2 -50, $yofs, 100, 20, self::IDC_B_PB_EXEC, 0x00000000, 0, 0);
        $this->ctrl[self::IDC_B_PB_EXIT] =  wb_create_control($this->mainwin, PushButton, 'Abbrechen',  self::win_width-130, $yofs, 100, 20, self::IDC_B_PB_EXIT, 0x00000000, 0, 0);

        //Intilalisierung der Statusbar
        $this->statusbar = wb_create_control($this->mainwin, StatusBar);
        $statustext = array(
            array('left text', 300), //Progressbar
            array('Start PM Compare Table', -1),
        );
        setMultiStatusBar( $this->statusbar, $statustext);
        // Create invisble Progressbar
        $this->gauge = wb_create_control($this->mainwin, Gauge, "", 0, 2, 300, 18, 0, WBC_INVISIBLE);
        wb_set_range($this->gauge, 0, 300);
        // Obtain real hWnds
        $gaugeHwnd = getHwnd($this->gauge);
        $statusHwnd = getHwnd($this->statusbar);
        // Set Statusbar as Progressbar Parent
        wb_call_function(API_SetParent, array($gaugeHwnd, $statusHwnd));
        // Make Progressbar visible
        wb_set_visible($this->gauge,true);
        wb_set_visible($this->mainwin, true);
        $this->excelCmd = $this->ini->get("excel");
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
        $this->tableCompare = null;
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
            case self::IDC_B_PB_EXCEL :
                $ipath = wb_get_text($this->ctrl[self::IDC_B_EB_EXCEL]);
                $path = wb_sys_dlg_path($this->mainwin, "Excel Pfad", $ipath);
                if (is_array($path)) $path = $path[0];
                if($path)
                {
                    wb_set_text($this->ctrl[self::IDC_B_EB_EXCEL], $path);
                    $this->ini->set($path, self::IDC_B_EB_EXCEL);
                }
                break;
            case self::IDC_B_PB_EXEC :
                $this->freezeDialog(false);
                $this->execCompare();
                $this->freezeDialog(true);
                break;
            case self::IDC_B_PB_COL :
                $this->freezeDialog(false);
                $this->testColumns();
                $this->freezeDialog(true);
                break;
            case self::IDC_B_LV_DIFF :
                if ($lparam1 == WBC_DBLCLICK) //doppel klick
                {
                    $entry = wb_get_text($ctrl);
                    if (is_array($entry) && isset($entry[0][0]) && strlen($entry[0][0]) > 0)
                    {
                        $excelpath = trim(wb_get_text($this->ctrl[self::IDC_B_EB_EXCEL]));
                        $excelfile = sprintf("%s\\%s.xlsx", $excelpath, trim($entry[0][0]));
                        if (is_file($excelfile))
                        {
                            $wsShell = new COM("WScript.Shell");
                            $wsShell->run(sprintf('"%s" "%s"', $this->excelCmd, $excelfile));
                        }
                        else
                        {
                            wb_message_box($this->mainwin, sprintf("File %s nicht vorhanden!", $excelfile), "Fehler", WBC_WARNING);
                        }
                    }
                }
                break;
            case self::IDC_B_PB_EXIT :
                $this->setInifile();
            case IDCLOSE :
                wb_destroy_window($this->mainwin);
                $this->mainwin = null;
                break;
        }
    }

    /**
     * Auslesen der Spalten aus Tabelle 1
     *
     * @return bool
     */
    private function testColumns()
    {
        $pmUser = wb_get_text($this->ctrl[self::IDC_B_EB_USR]);
        if (strlen($pmUser) == 0)
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Benutzer definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $pmServer = wb_get_text($this->ctrl[self::IDC_B_EB_SRV]);
        if (strlen($pmServer) == 0)
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Server definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $pmPort = wb_get_text($this->ctrl[self::IDC_B_EB_PRT]);
        if (strlen($pmPort) == 0 || !is_numeric($pmPort))
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Server Port als Zahl (1000-65000) definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $pmPassword = wb_get_text($this->ctrl[self::IDC_B_EB_PWD]);
        $tab1 = wb_get_text($this->ctrl[self::IDC_B_EB_TAB1]);
        if (strlen($tab1) == 0)
        {
            wb_message_box($this->mainwin, "Bitte Tabelle 1, Ordner oder PM Tabelle definieren", "Fehler", WBC_WARNING);
            return false;
        }
        if (($tar = $this->getWorkspaceId($tab1)) !== false)
        {
            $this->writeStatusbar("Start Tabellen Spalten einlesen");
            $this->setInifile();
            if (is_null($this->tableCompare))
            {
                $locations = $this->ini->get("locations", null, "Debug");
                $this->tableCompare = new TableCompare($this->mainwin, array($this, "msgFunction"), $locations);
            }
            if (is_object($this->tableCompare))
            {
                wb_set_text($this->ctrl[self::IDC_B_EB_COL], $this->tableCompare->getTableColumns($pmUser, $pmPassword, $pmServer, $pmPort, $tar));
                return true;
            }
        }
        return false;
    }

    /**
     * Vergleich der Tabellen
     *
     * @return bool
     */
    private function execCompare()
    {
        $pmUser = wb_get_text($this->ctrl[self::IDC_B_EB_USR]);
        if (strlen($pmUser) == 0)
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Benutzer definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $pmServer = wb_get_text($this->ctrl[self::IDC_B_EB_SRV]);
        if (strlen($pmServer) == 0)
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Server definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $pmPort = wb_get_text($this->ctrl[self::IDC_B_EB_PRT]);
        if (strlen($pmPort) == 0 || !is_numeric($pmPort))
        {
            wb_message_box($this->mainwin, "Bitte PMDesigner Server Port als Zahl (1000-65000) definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $tab1 = wb_get_text($this->ctrl[self::IDC_B_EB_TAB1]);
        if (strlen($tab1) == 0)
        {
            wb_message_box($this->mainwin, "Bitte Tabelle 1, Ordner oder PM Tabelle definieren", "Fehler", WBC_WARNING);
            return false;
        }
        $tab2 = wb_get_text($this->ctrl[self::IDC_B_EB_TAB2]);
        if (strlen($tab1) == 0)
        {
            wb_message_box($this->mainwin, "Bitte Tabelle 1, Ordner oder PM Tabelle definieren", "Fehler", WBC_WARNING);
            return false;
        }
        $excelPath = wb_get_text($this->ctrl[self::IDC_B_EB_EXCEL]);
        if (strlen($excelPath) == 0 || !nis_dir($excelPath))
        {
            wb_message_box($this->mainwin, "Bitte gültigen Excelfile Pfad definieren.", "Fehler", WBC_WARNING);
            return false;
        }
        $columns = wb_get_text($this->ctrl[self::IDC_B_EB_COL]);
        $pmPassword = wb_get_text($this->ctrl[self::IDC_B_EB_PWD]);
        $suffix1 = wb_get_text($this->ctrl[self::IDC_B_EB_SUX1]);
        $suffix2 = wb_get_text($this->ctrl[self::IDC_B_EB_SUX2]);
        if (($tar1 = $this->getWorkspaceId($tab1)) !== false && ($tar2 = $this->getWorkspaceId($tab2)) !== false)
        {
            $this->writeStatusbar("Start Tabellen vergleich");
            $this->setInifile();
            if (is_null($this->tableCompare))
            {
                $locations = $this->ini->get("locations", null, "Debug");
                $this->tableCompare = new TableCompare($this->mainwin, array($this, "msgFunction"), $locations);
            }
            if (is_object($this->tableCompare))
            {
                wb_delete_items($this->ctrl[self::IDC_B_LV_DIFF], null);
                $viewList = $this->tableCompare->compare($pmUser, $pmPassword, $pmServer, $pmPort, $columns, $excelPath, $suffix1, $suffix2, $tar1, $tar2);
                if (is_array($viewList))
                {
                    wb_create_items($this->ctrl[self::IDC_B_LV_DIFF], $viewList);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Ermitteln der Arbeitbereich PMID und der Baustein Pfad
     * Wenn vorhanden wird auch der Bausteintyp ausgegeben
     *
     * @param string    $tab
     * @return array|bool    Fehler = false, array(<PMID>, <Pfad>, <type>, <dsn>)
     */
    private function getWorkspaceId($tab)
    {
        $ret = null;
        if (strncmp($tab, "msgpm://", 8) == 0)
        {
            //msgpm://PMPRIVA1/Marktbereich_Privatpersonen/Entwicklung_SAT/Casino/Gebaeudesach_14/201511270816/TT_05_DZ_BV_Diebstahl_Mobilheim_14_201511270816?type=Interne Tabelle
            $type = null;
            $str = substr($tab, 8);
            $ar = explode("?", $str);
            if (isset($ar[1]))
            {
                $tr =  explode("=", $ar[1]);
                if (isset($tr[1]))
                {
                    $type = $tr[1];
                }
            }
            $dar =  explode("/", $ar[0]);
            $dsn = $dar[0];
            $wsId = null;
            $path = null;
            $tr = $this->ini->get(null, null, "Arbeitsbereiche");
            if (is_array($tr))
            {
                foreach($tr as $v)
                {
                    $wr = explode("=", $v);
                    if (strncmp($ar[0], $wr[0], strlen($wr[0])) == 0)
                    {
                        $path = substr($ar[0], strlen($wr[0]));
                        $wsId = $wr[1];
                        break;
                    }
                }
            }
            if (is_null($wsId))
            {
                wb_message_box($this->mainwin, "Arbeitsbereich in PPTableCompare.ini nicht vorhanden bitte definieren.", "Fehler", WBC_WARNING);
                return false;
            }
            return array($wsId, $path, $type, $dsn);
        }
        else
        {
            wb_message_box($this->mainwin, "Die Tabellen Definition muss mit msgpm:// beginnen.\n".$tab, "Fehler", WBC_WARNING);
        }
        return false;
    }
    /**
     * Ausgabe von Text in der Statusbar Zeile
     *
     * @param String $msg		// Text für die Statusbar Zeile.
     */
    private function writeStatusbar($msg)
    {
        $statustext = array( array('left text', 300), array($msg, -1), );
        setMultiStatusBar( $this->statusbar, $statustext);
        wb_refresh($this->statusbar, true);
    }

    /**
     *  Ini File mit den aktuellen Daten setzen
     */
    private function setInifile()
    {
        foreach($this->ctrl as $id => $ctrl)
        {
            if (wb_get_class($ctrl) == EditBox)
            {
                $this->ini->set(wb_get_text($ctrl), $id);
            }
        }
    }

    /**
     * Dialog sperren | entsperrsen
     *
     * @param $state
     */
    private function freezeDialog($state)
    {
        foreach($this->ctrl as $id => $ctrl)
        {
            if ($id == self::IDC_B_PB_EXIT) continue;
            wb_set_enabled($ctrl, $state);
        }
        wb_refresh($this->mainwin, true);
    }

    /**
     * Diese Methode leitet die Meldungen an den externen Messagehandler weiter.
     *
     * @param string $msg
     * @param integer $id
     */
    public function msgFunction($msg, $id)
    {
        if ($id == STATUSBAR)
        {
            $this->writeStatusbar($msg);
        }
        else if (!is_null($this->msgHandler))
        {
            call_user_func($this->msgHandler, $msg, $id);
        }
    }


}