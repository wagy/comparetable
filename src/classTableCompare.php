<?php

/**
 * Created by PhpStorm.
 * User: walter
 * Date: 18.03.2016
 * Time: 13:57
 */
class TableCompare
{
    /** @var null|string                PMDesigner Benutzer */
    private $pmUser                     = null;
    /** @var null|string                PMDesigner Passwort */
    private $pmPassword                 = null;
    /** @var null|string                PMDesigner Server */
    private $pmServer                     = null;
    /** @var null|string                PMDesigner Server Port*/
    private $pmPort                     = null;
    /** @var null|int                   Windows ID des Hauptfenster */
    private $mainwin                    = null;
    /** @var null|int                   Windows ID der Statusbar */
    private $statusbar                  = null;
    /** @var null|callable              Messaghandler zum Logfile */
    private $messageHandler             = null;
    /** @var null|string                 */
    private $locations                  = null;
    /** @var null|string                Fehlermeldun aus PMService */
    private $lastError                  = null;


    /**
     * TableCompare constructor.
     *
     * @param int $mainwin
     * @param callable $messageHandler
     * @param null|string $locations
     */
    public function __construct($mainwin, $messageHandler, $locations=null)
    {
        $this->mainwin = $mainwin;
        $this->messageHandler = $messageHandler;
        $this->locations = $locations;
    }

    /**
     * Auslesen der Spalten einer Tabelle
     *
     * @param string $pmUser
     * @param string $pmPassword
     * @param string $pmServer
     * @param int $pmPort
     * @param $tabArr array(<PMID>, <Pfad>, <type>, <dsn>)
     * @return string
     */
    public function getTableColumns($pmUser, $pmPassword, $pmServer, $pmPort, $tabArr)
    {
        $ret = "";
        $this->pmUser = $pmUser;
        $this->pmPassword = $pmPassword;
        $this->pmServer = $pmServer;
        $this->pmPort = $pmPort;
        try
        {
            $ps = $this->openPMService($tabArr[0], $tabArr[3]);
            if ($ps === false)
            {
                wb_message_box($this->mainwin, sprintf("Fehler Verbindung zum PMDesignerService\n%s", $this->lastError), "Fehler", WBC_WARNING);
                return $ret;
            }
            if ($tabArr[2] == "Tabellenordner")
            {
                $type = PMEnumObjectType::VALUE_TABLE_FOLDER;
            }
            else
            {
                $type = PMEnumObjectType::VALUE_INTERNAL_TABLE;
            }
            /** @var PMStructBaseObjectType $objectInfo */
            $objectInfo = null;
            if ($ps->getObjectInfoByPath($type, str_replace("/", "\\", $tabArr[1]), $objectInfo))
            {
                if ($type == PMEnumObjectType::VALUE_TABLE_FOLDER)
                {
                    $this->msgFunction("Ordner = ", $objectInfo->getName());
                    /** @var  PMStructBaseObjectListType $list */
                    $list = null;;
                    if ($ps->getChildren($objectInfo->getId(), $type, $list, PMEnumObjectType::VALUE_INTERNAL_TABLE))
                    {
                        /** @var PMStructBaseObjectType $info */
                        foreach ($list->objectInfo as $info)
                        {
                            $objectInfo = $info;
                            break;
                        }
                    }
                }
                if ($objectInfo->getTypeName() == PMEnumObjectType::VALUE_INTERNAL_TABLE)
                {
                    $this->msgFunction("Tabelle = ", $objectInfo->getName());
                    /** @var  PMStructTableRowValuesListType $data */
                    $data = null;
                    /** @var  PMStructTableLayoutColumnType[] $column */
                    $column = null;
                    if ($ps->getTableData($objectInfo->getId(), $data, $column))
                    {
                        foreach($column as $col)
                        {
                            if (strlen($ret) > 0) $ret .= ",";
                            $ret .= $col->getName();
                        }
                        $this->msgFunction(sprintf("Spalten aus Tabelle %s erfolgreich ausgelesen.", $objectInfo->getName()), STATUSBAR);
                    }
                }
                else
                {
                    wb_message_box($this->mainwin, "Fehler beim auslesen des Pfad. keine Tabelle vorhanden.", "Fehler", WBC_WARNING);
                }
            }
            else
            {
                wb_message_box($this->mainwin, "Fehler beim auslesen des Pfad.", "Fehler", WBC_WARNING);
            }
            $ps->closeSession();
        } catch (Exception $ex){
            wb_message_box($this->mainwin, $ex->getMessage(), "Fehler", WBC_WARNING);
        }
        $ps = null;
        return $ret;
    }

    /**
     * Auslesen der Spalten einer Tabelle
     *
     * @param string $pmUser
     * @param string $pmPassword
     * @param string $pmServer
     * @param int $pmPort
     * @param string $column
     * @param string $excelPath
     * @param string $suffix1
     * @param string $suffix2
     * @param $tabArr1 array(<PMID>, <Pfad>, <type>, <dsn>)
     * @param $tabArr2 array(<PMID>, <Pfad>, <type>, <dsn>)
     * @return string
     */
    public function compare($pmUser, $pmPassword, $pmServer, $pmPort, $column, $excelPath, $suffix1, $suffix2, $tabArr1, $tabArr2)
    {
        $ret = null;
        $this->pmUser = $pmUser;
        $this->pmPassword = $pmPassword;
        $this->pmServer = $pmServer;
        $this->pmPort = $pmPort;
        $equal = false;
        try
        {
            $ps1 = $this->openPMService($tabArr1[0], $tabArr1[3]);
            if ($ps1 === false)
            {
                wb_message_box($this->mainwin, sprintf("Fehler Verbindung zum PMDesignerService\n%s", $this->lastError), "Fehler", WBC_WARNING);
                return $ret;
            }
            if ($tabArr1[0] == $tabArr2[0] && $tabArr1[3] == $tabArr2[3])
            {
                $ps2 = $ps1;
                $equal = true;
            }
            else
            {
                $ps2 = $this->openPMService($tabArr2[0], $tabArr2[3]);
                if ($ps2 === false)
                {
                    wb_message_box($this->mainwin, sprintf("Fehler Verbindung zum PMDesignerService\n%s", $this->lastError), "Fehler", WBC_WARNING);
                    $ps1 = null;
                    return $ret;
                }
            }
            $id = null;
            if ($tabArr1[2] == "Tabellenordner")
            {
                $type1 = PMEnumObjectType::VALUE_TABLE_FOLDER;
            }
            else
            {
                $type1 = PMEnumObjectType::VALUE_INTERNAL_TABLE;
            }
            if ($tabArr2[2] == "Tabellenordner")
            {
                $type2 = PMEnumObjectType::VALUE_TABLE_FOLDER;
            }
            else
            {
                $type2 = PMEnumObjectType::VALUE_INTERNAL_TABLE;
            }
            /** @var  PMStructBaseObjectType[] $list1 */
            if (($list1 = $this->getTableList($ps1, $type1, $tabArr1[1], $suffix1)))
            {
                /** @var  PMStructBaseObjectType[] $list2 */
                if (($list2 = $this->getTableList($ps2, $type2, $tabArr2[1], $suffix2)))
                {
                    if ($type1 == PMEnumObjectType::VALUE_TABLE_FOLDER && $type2 == PMEnumObjectType::VALUE_TABLE_FOLDER)
                    {
                        foreach ($list1 as $tnam => $tab1)
                        {
                            if (isset($list2[$tnam]))
                            {
                                $diff = $this->compareTable($ps1, $ps2, $tab1, $list2[$tnam], $column, $excelPath);
                                $ret[] = array($tab1->getName(), $list2[$tnam]->getName(), $diff);
                            }
                            else
                            {
                                $ret[] = array($tab1->getName(), "", -1);
                            }
                        }
                        foreach ($list2 as $tnam => $tab2)
                        {
                            if (!isset($list1[$tnam]))
                            {
                                $ret[] = array("", $tab2->getName(), -2);
                            }
                        }
                    }
                    else if ($type1 == PMEnumObjectType::VALUE_INTERNAL_TABLE && $type2 == PMEnumObjectType::VALUE_INTERNAL_TABLE)
                    {
                        $diff = $this->compareTable($ps1, $ps2, current($list1), current($list2), $column, $excelPath);
                        $ret[] = array(current($list1)->getName(), current($list2)->getName(), $diff);
                    }
                    else if (count($list1) == 1)
                    {
                        foreach ($list1 as $tnam => $tab1)
                        {
                            if (isset($list2[$tnam]))
                            {
                                $diff = $this->compareTable($ps1, $ps2, $tab1, $list2[$tnam], $column, $excelPath);
                                $ret[] = array($tab1->getName(), $list2[$tnam]->getName(), $diff);
                            }
                            else
                            {
                                $ret[] = array($tab1->getName(), "", -1);
                            }
                        }
                    }
                    else if (count($list2) == 1)
                    {
                        foreach ($list2 as $tnam => $tab2)
                        {
                            if (isset($list1[$tnam]))
                            {
                                $diff = $this->compareTable($ps1, $ps2, $list1[$tnam], $tab2, $column, $excelPath);
                                $ret[] = array($list1[$tnam]->getName(), $tab2->getName(), $diff);
                            }
                            else
                            {
                                $ret[] = array("", $tab2->getName(), -2);
                            }
                        }
                    }
                }
            }
            $ps1->closeSession();
            if (!$equal) $ps2->closeSession();
            wb_message_box($this->mainwin, "Vergleich der Tabellen erfolgreich beendet.", "Meldung", WBC_OK);
        } catch (Exception $ex){
            wb_message_box($this->mainwin, $ex->getMessage(), "Fehler", WBC_WARNING);
        }
        $ps1 = null;
        $ps1 = null;
        return $ret;
    }

    /**
     * Vergleich von 2 Tabellen Ausgabe in Excelfile
     *
     * @param PMService $ps1
     * @param PMService $ps2
     * @param PMStructBaseObjectType $tab1
     * @param PMStructBaseObjectType $tab2
     * @param string $column
     * @param string $excelPath
     * @return int      Anzahl der differenzen
     * @throws PMServiceException
     */
    private function compareTable($ps1, $ps2, $tab1, $tab2, $column, $excelPath)
    {
        $columnArr = null;
        if (strlen($column) > 0)
        {
            $columnArr = explode(",", $column);
        }
        $ret = 0;
        /** @var PMStructTableRowValuesListType $dat1 */
        $dat1 = null;
        /** @var PMStructTableLayoutColumnType[] $col1 */
        $col1 = null;
        if ($ps1->getTableData($tab1->id, $dat1, $col1, false))
        {
            /** @var PMStructTableLayoutColumnType[] $col1 */
            $col2 = null;
            /** @var PMStructTableRowValuesListType $dat1 */
            $dat2 = null;
            if ($ps2->getTableData($tab2->id, $dat2, $col2, false))
            {
                $key = 1;
                switch($col1[0]->name)
                {
                    case "Nummer" :
                    case "IDX" : $key = 2; break;
                    case "NUMBER" : $key = 3; break;
                    case "INDEX_1" : $key = 4; break;

                }
                $ar1 = $this->extractData($dat1, $key, $this->calcColumn($col1, $columnArr, $key));
                //$dat1 = null;
                $ar2 = $this->extractData($dat2, $key, $this->calcColumn($col2, $columnArr, $key));
                //$dat2 = null;
                $excelFile = sprintf("%s/%s.xlsx", $excelPath, $tab1->name);
                if (is_file($excelFile)) @unlink($excelFile);
                if ($this->testDiff($ar1, $ar2))
                {
                    $this->msgFunction(sprintf("Erstellen Excelfile %s.xlsx", $tab1->name), STATUSBAR);
                    $ret = $this->writeExcel($col1, $excelFile, $ar1, $ar2, $key);
                }
            }
            else
            {
                wb_message_box($this->mainwin, sprintf("Fehler beim auslesen von 2. Tabelle  %s", $tab2->name), "Fehler", WBC_WARNING);
            }
        }
        else
        {
            wb_message_box($this->mainwin, sprintf("Fehler beim auslesen von 1. Tabelle  %s", $tab1->name), "Fehler", WBC_WARNING);
        }
        return $ret;
    }

    /**
     * Berechnet die zu vergleichenden Spalten
     *
     * @param PMStructTableLayoutColumnType[] $col
     * @param array $ref
     * @param integer $key
     * @return array
     */
    private function calcColumn($col, $ref, $key)
    {
        $ret = array();
        $neg = false;
        if (is_null($ref)) return $ret;
        if ($ref[0]{0} == "-") $neg = true;
        foreach($col as $k => $v)
        {
            if ($k >= $key)
            {
                if ($neg)
                {
                    if (in_array("-".$v->name, $ref))
                    {
                        $ret[] = $k;
                    }
                }
                else if (!in_array($v->name, $ref))
                {
                    $ret[] = $k;
                }
            }
        }
        return $ret;
    }

    /**
     * Umwandeln der Tabellendaten in key value array
     *
     * @param PMStructTableRowValuesListType $dat
     * @param Integer $key
     * @param array $exclArr
     * @return array|null
     */
    private function extractData($dat, $key, $exclArr)
    {
        $ret = null;
        foreach($dat->rowValues as $k => $r)
        {
            $row = $r->columnValue;
            foreach($exclArr as $excl)
            {
                unset($row[$excl]);
            }
            if ($key == 1)
            {
                $ret[$row[0]] = array_slice($row, $key);
            }
            else if ($key == 2)
            {
                $ret[$row[0]][$row[1]] = array_slice($row, $key);
            }
            else
            {
                $dat = array_slice($row, $key);
                $ret[$row[0]][implode(";", array_splice($row, 1, $key -1))] = $dat;
            }
        }
        return $ret;
    }

    private function array_diff($ar1, $ar2)
    {
        $err = error_reporting (E_ERROR );
        $d1 = array_diff_assoc($ar1, $ar2);
        $d2 = array_diff_assoc($ar2, $ar1);
        error_reporting ( $err);
        return $d1 !== $d2;
    }

    /**
     * Prüfung auf differenzen
     *
     * @param array $ar1
     * @param array $ar2
     * @return bool
     */
    private function testDiff($ar1, $ar2)
    {
        if ($this->array_diff($ar1, $ar2)) return true; //Unterschiedliche Zeilen
        foreach($ar1 as $k1 => $v1)
        {
            $v2 = $ar2[$k1];
            if ($this->array_diff($v1, $v2)) return true; //Unterschiedliche Schlüssel
            foreach ($v1 as $k => $ar)
            {
                if ($this->array_diff($ar, $v2[$k])) return true; //Unterschiedliche Werte
            }
        }
        return false;
    }

    /**
     * Erstellung einer Liste mit Tabellen info
     *
     * @param PMService $ps
     * @param string $type PMEnumObjectType
     * @param string $pfad
     * @param string $suffix
     * @return bool|PMStructBaseObjectType[]
     * @throws PMServiceException
     */
    private function getTableList($ps, $type, $pfad, $suffix)
    {
        /** @var  PMStructBaseObjectType[] $ret */
        $ret = null;
        /** @var PMStructBaseObjectType $objectInfo */
        $objectInfo = null;
        if ($ps->getObjectInfoByPath($type, str_replace("/", "\\", $pfad), $objectInfo))
        {
            $len = strlen($suffix);
            if ($len > 0)
            {
                $len = 0 - $len;
            }
            if ($type == PMEnumObjectType::VALUE_TABLE_FOLDER)
            {
                $this->msgFunction("Ordner = ", $objectInfo->getName());
                /** @var  PMStructBaseObjectListType $list */
                $list = null;
                if ($ps->getChildren($objectInfo->getId(), $type, $list, PMEnumObjectType::VALUE_INTERNAL_TABLE))
                {
                    /** @var PMStructBaseObjectType $info */
                    foreach ($list->objectInfo as $info)
                    {
                        if ($len == 0 || substr($info->getName(), $len) != $suffix)
                        {
                            $ret[$info->getName()] = $info;
                        }
                        else
                        {
                            $ret[substr($info->getName(), 0, $len)] = $info;
                        }
                    }
                   return $ret;
                }
            }
            if ($objectInfo->getTypeName() == PMEnumObjectType::VALUE_INTERNAL_TABLE)
            {
                if ($len == 0 || substr($objectInfo->getName(), $len) != $suffix)
                {
                    $ret[$objectInfo->getName()] = $objectInfo;
                }
                else
                {
                    $ret[substr($objectInfo->getName(), 0, $len)] = $objectInfo;
                }
                return $ret;
            }
            else
            {
                wb_message_box($this->mainwin, "Fehler beim auslesen des Pfad. keine Tabelle vorhanden.", "Fehler", WBC_WARNING);
            }
        }
        else
        {
            wb_message_box($this->mainwin, "Fehler beim auslesen des Pfad.", "Fehler", WBC_WARNING);
        }
        return false;
    }

    /**
     * Öffnen des PMService
     *
     * @param string $wsId Arbeitsbereich PMID
     * @param $dsn
     * @return PMService|false
     * @throws PMServiceException
     */
    private function openPMService($wsId, $dsn)
    {
        $disableDataCheck=false;
        $compileAsync=false;
        $allowTemplateModification=true;
        $allowNewPropertiesAtProductModule=false;
        $disableDeleteOnMasterRegistration=false;
        $synchronizeAsync=false;
        $clientCulture="de-DE";

        $error = error_reporting(0);
        //Setup PMService Schnittstelle
        $ps = new PMService($this->pmServer, $this->pmUser, $this->messageHandler, $this->pmPassword, $this->pmPort, $this->locations, true, false);
        error_reporting($error);
        if ($ps->openSessionEx($dsn, $wsId, $this->pmUser, $this->pmPassword,
            $disableDataCheck, $compileAsync, $allowTemplateModification,
            $allowNewPropertiesAtProductModule,$disableDeleteOnMasterRegistration,
            $synchronizeAsync, $clientCulture))
        {
            return $ps;
        }
        $ps = null;
        return false;
    }

    /**
     * Diese Methode leitet die Meldungen an den externen Messagehandler weiter.
     *
     * @param string $msg
     * @param integer $id
     */
    private function msgFunction($msg, $id)
    {
        if ($id == ERROR)
        {
            $this->lastError = $msg;
        }
        if (!is_null($this->messageHandler))
        {
            call_user_func($this->messageHandler, $msg, $id);
        }
    }

    /**
     * Ausgabe der differenzen ins Excelfile
     *
     * @param PMStructTableLayoutColumnType[] $col1
     * @param string $excelFile
     * @param array $ar1
     * @param array $ar2
     * @param integer $key
     * @return integer
     */
    private function writeExcel($col1, $excelFile, $ar1, $ar2, $key)
    {
        $diff = 0;
        $title = array();
        foreach($col1 as $col)
        {
            $title[] = $col->name;
        }
        $excel = new ExcelWriterLibxl($excelFile, "Differenzen", true, true, 80);
        $red = $excel->setNewFormat("red");
        $red->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $green = $excel->setNewFormat("green");
        $green->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $blue = $excel->setNewFormat("blue");
        $blue->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $excel->setColumn(0, $key, 40);
        $excel->setColumn($key, 10, 25);
        $excel->writeTitle($title);
        $err = error_reporting (E_ERROR );
        $only2 =  array_diff_assoc($ar2, $ar1);
        error_reporting ( $err );
        $fgr = array_fill(0, 10, "red");
        $fblue = array_fill(0, 10, "blue");
        foreach($ar1 as $k1 => $v1)
        {
            $target = null;
            $hasParent = true;
            $v2 = array();
            if (isset($ar2[$k1]))
            {
                $v2 = $ar2[$k1];
                $keyArr1 = array_keys($v1);
                $keyArr2 = array_keys($v2);
                $pos = 0;
                foreach ($keyArr1 as $key1)
                {
                    $sr = array_search($key1, $keyArr2);
                    if ($sr === false)
                    {
                        $target[] = $key1;
                    }
                    else
                    {
                        for (; $pos <= $sr; $pos++)
                        {
                            $target[] = $keyArr2[$pos];
                        }
                    }
                }
            }
            else
            {
                $hasParent = false;
                $target = array_keys($v1);
            }
            if (is_array($target))
            {
                foreach($target as $idx)
                {
                    if (!$hasParent)
                    {
                        $kar = explode(";", $idx);
                        array_unshift($kar, $k1);
                        $excel->writeSingleLine(array_merge($kar, $v1[$idx]), $fblue);
                    }
                    else if (isset($v1[$idx]) && isset($v2[$idx]))
                    {
                        $kar = explode(";", $idx);
                        array_unshift($kar, $k1);
                        $format = array();
                        if ($this->array_diff($v1[$idx], $v2[$idx]))
                        {
                            $far = $fgr;
                            foreach($v1[$idx] as $k => $v)
                            {
                                if ($v != $v2[$idx][$k])
                                {
                                    $far[$key + $k] = "red";
                                    $format[$key + $k] = "red";
                                    $diff++;
                                }
                            }
                            $excel->writeSingleLine(array_merge($kar, $v1[$idx]), $format);
                            $excel->writeSingleLine(array_merge($kar, $v2[$idx]), $far);
                        }
                        else
                        {
                            $excel->writeSingleLine(array_merge($kar, $v1[$idx]));
                        }

                    }
                    else if (isset($v1[$idx]))
                    {
                        $kar = explode(";", $idx);
                        array_unshift($kar, $k1);
                        $excel->writeSingleLine(array_merge($kar, $v1[$idx]), $fblue);
                        $diff++;
                    }
                    else if (isset($v2[$idx]))
                    {
                        $kar = explode(";", $idx);
                        array_unshift($kar, $k1);
                        $excel->writeSingleLine(array_merge($kar, $v2[$idx]), $fgr);
                        $diff++;
                    }

                }
            }
        }
        if (is_array($only2) && count($only2) > 0)
        {
            foreach($only2 as $k1 => $v2)
            {
                foreach($v2 as $idx => $v)
                {
                    $kar = explode(";", $idx);
                    array_unshift($kar, $k1);
                    $excel->writeSingleLine(array_merge($kar, $v), $fgr);
                    $diff++;
                }
            }
        }

        $excel->close();
        return $diff;

    }


}