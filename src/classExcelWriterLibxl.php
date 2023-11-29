<?php

/**
 * Diese KLasse implementiert die Schnittstelle zum schreiben eines Excelfile mit der LIBXL Bibliothek.
 * Sie ist kompatibel zur KLasse excelWriter welche auf der PEARL spreadsheet Erweiterung basiert.
 *
 * @author walter
 * @package mlp
 */

/**
 * LIBXL based excel writer
 *
 * @package base
 */
class ExcelWriterLibxl
{
    /**  @var ExcelBook */
    private $workbook = null;
    /**  @var ExcelSheet[] */
    private $worksheet = array();
    private $lin = array();
    /** @var ExcelFormat[] */
    private $format = array();
    private $lastLine = array(0=>"",1=>"",2=>"",3=>1.0);
    private $actSheet = -1;
    private $sheetNames = array();
    private $excelZoom = 100;
    private $excelPassword = "";
    private $filename = null;
    /**
     * Initialize a new excel workbook
     *
     * @param string	$filename	: Filepath of new excelfile
     * @param string    $sheetName      [optional]      Name of sheet in work book or NULL for default
     * @param boolean   $setSheet       [optional]      Set new sheet name
     * @param bool      $setVersion     [optional]      false = xls, true = xlsx
     * @param int       $zoom           [optional]
     * @param string    $password       [optional]
     */
    public function __construct($filename, $sheetName=null, $setSheet=TRUE, $setVersion=FALSE, $zoom=100, $password="")
    {
        $this->filename = $filename;
        $this->excelPassword = $password;
        $this->excelZoom = $zoom;


        $par = pathinfo($filename);
        switch(strtolower($par['extension']))
        {
            case "xls" : $setVersion = false; break;
            case "xlsx" : $setVersion = true; break;
        }
        $this->workbook = new ExcelBook(null, null, $setVersion);

        $this->setNewFormat("bold");
        $this->format['bold']->getFont()->bold(true);
        $this->format['bold']->horizontalAlign(ExcelFormat::ALIGNH_CENTER);
        $this->format['bold']->borderStyle(ExcelFormat::BORDERSTYLE_THIN);

        $this->setNewFormat("title");
        $this->format['title']->getFont()->bold(true);
        $this->format['title']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);
        $this->format['title']->borderStyle(ExcelFormat::BORDERSTYLE_THIN);

        $this->setNewFormat("new");
        $this->format['new']->horizontalAlign(ExcelFormat::ALIGNH_RIGHT);
        $this->format['new']->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $this->format['new']->numberFormat($this->workbook->addCustomFormat("0.000000000000000"));
        $this->format['new']->fillPattern(ExcelFormat::FILLPATTERN_SOLID);
        $this->format['new']->patternForegroundColor(ExcelFormat::COLOR_YELLOW);

        $this->setNewFormat("red");
        $this->format['red']->getFont()->color(ExcelFormat::COLOR_RED);
        $this->format['red']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);
        $this->format['red']->wrap(true);

        $this->setNewFormat("green");
        $this->format['green']->getFont()->color(ExcelFormat::COLOR_GREEN);
        $this->format['green']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);

        $this->setNewFormat("dgreen");
        $this->format['dgreen']->getFont()->color(ExcelFormat::COLOR_BRIGHTGREEN);

        $this->setNewFormat("yellow");
        $this->format['yellow']->getFont()->color(ExcelFormat::COLOR_YELLOW);

        $this->setNewFormat("magenta");
        $this->format['magenta']->getFont()->color(ExcelFormat::COLOR_ROSE);
        $this->format['dmagenta'] = $this->setNewFormat("");;
        $this->format['dmagenta']->getFont()->color(ExcelFormat::COLOR_VIOLET);

        $this->setNewFormat("blue");
        $this->format['blue']->getFont()->color(ExcelFormat::COLOR_BLUE);
        $this->format['blue']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);

        $this->setNewFormat("std");
        $this->format['std']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);
        $this->format['std']->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $this->format['std']->getFont()->color(ExcelFormat::COLOR_BLACK);

        $this->setNewFormat("text");
        $this->format['text']->horizontalAlign(ExcelFormat::ALIGNH_LEFT);
        $this->format['text']->wrap(true);

        $this->setNewFormat("stz");
        $this->format['stz']->horizontalAlign(ExcelFormat::ALIGNH_RIGHT);
        $this->format['stz']->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $this->format['stz']->getFont()->bold(false);
        $this->format['stz']->numberFormat($this->workbook->addCustomFormat("0.000000000000000"));

        $this->setNewFormat("bg_yellow");
        $this->format['bg_yellow']->patternBackgroundColor(ExcelFormat::COLOR_YELLOW);

        $this->setNewFormat("bg_blue");
        $this->format['bg_blue']->patternBackgroundColor(ExcelFormat::COLOR_BLUE);

        $this->setNewFormat("bg_green");
        $this->format['bg_green']->patternBackgroundColor(ExcelFormat::COLOR_GREEN);

        $this->setNewFormat("bg_red");
        $this->format['bg_red']->patternBackgroundColor(ExcelFormat::COLOR_RED);

        $this->setNewFormat("bg_magenta");
        $this->format['bg_magenta']->patternBackgroundColor(ExcelFormat::COLOR_ROSE);
        if ($setSheet)
        {
            $this->addNewSheet($sheetName);
        }
    }
    /**
     * Set a new format object by name
     *
     * @param string 	$formatName			: Name of the format if already exists the return this
     * @return ExcelFormat                  : Format object by name
     */
    function setNewFormat($formatName)
    {
        if (!isset($this->format[$formatName]) )
        {
            $this->format[$formatName] = $this->workbook->addFormat();
            $this->format[$formatName]->setFont(clone $this->format[$formatName]->getFont());
        }
        return $this->format[$formatName];
    }
    /**
     * Set custom number format
     *
     * @param string 	$format			    : format string
     * @return int	    ID des Format
     */
    function customFormat($format)
    {
        return $this->workbook->addCustomFormat($format);
    }
    /**
     * Add a new sheet on the workbook
     *
     * @param string 	$sheetName	: Name of the sheet or NULL
     * @return ExcelSheet object
     */
    function addNewSheet($sheetName=null)
    {
        $cnt = $this->workbook->sheetCount();
        if ($sheetName)
        {
            $i = 0;
            $name = substr($sheetName, 0, 31);
            /** @var ExcelSheet $v */
            foreach($this->worksheet as  $v)
            {
                if($name == $v->name())
                {
                    $name = substr($sheetName, 0, 30);
                    $name .= $i;
                    break;
                }
            }
            //TODO test exists sheet names
            $this->worksheet[$cnt] = $this->workbook->addSheet($name);
        }
        else
        {
            $this->worksheet[$cnt] = $this->workbook->addSheet("Sheet1");
        }
        $this->workbook->setActiveSheet($cnt);
        $this->lin[$this->workbook->getActiveSheet()] = 0;
        $this->setZoom($this->excelZoom);
        if (strlen($this->excelPassword) > 0)
        {
            $this->protect($this->excelPassword);
        }
        return $this->workbook->getActiveSheet();
    }
    /**
     * Set the number of sheet active
     *
     * @param integer 	$act	: Number of sheet
     */
    function setSheet($act)
    {
        $this->workbook->setActiveSheet($act);
    }
    /**
     * Get the active sheet number
     *
     * @return integer
     */
    function getSheet()
    {
        return $this->workbook->getActiveSheet();
    }
    /**
     * Get the name of the active sheet
     *
     * @return string
     */
    function getSheetName()
    {
        return $this->worksheet[$this->workbook->getActiveSheet()]->name();
    }
    /**
     * Get the line number of active sheet
     *
     * @return int
     */
    function getLine()
    {
        if (isset($this->lin[$this->workbook->getActiveSheet()]) )
            return $this->lin[$this->workbook->getActiveSheet()];
        return 0;
    }
    /**
     * Get cell of last saved line
     *
     * @param integer 	$idx	: Number of cell
     * @return mixed			: value of cell
     */
    function getLastItem($idx)
    {
        if ( isset($this->lastLine[$idx]) )
        {
            return $this->lastLine[$idx];
        }
        return "";
    }
    /**
     * Reset last line array
     *
     */
    function resetLastLine()
    {
        $this->lastLine = array();
    }

    /**
     * Write title of a sheet
     *
     * @param array  		$tar	: Title of each cell in a array
     * @param array         $format	: Format name of each cell in a array
     * @return integer						: Count of line on this sheet
     */
    function writeTitle($tar, $format=null)
    {
        $i = 0;
        foreach($tar as $w)
        {
            if ( isset($format[$i]) )
            {
                if (isset($this->format[$format[$i]]))
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format[$format[$i]]);
                }
                else
                {
                    trigger_error(sprintf("writeTitle Format nicht gesetzt : %s", $format[$i]));
                }
            }
            else
            {
                $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['title']);
            }
            $i += 1;
        }
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $this->lin[$this->workbook->getActiveSheet()];
    }
    /**
     * Write a line of cell values
     *
     * @param array     $tar	: Value for each cell in the line
     * @param boolean	$normal	: Write cell in standard cell format
     * @param integer	$maxcol
     * @return integer			: Count of line on this sheet
     */
    function writeLine($tar, $normal=TRUE, $maxcol=3)
    {
        $i = 0;
        $overwrite = FALSE;
        foreach($tar as $k => $w)
        {
            if ($overwrite || $i > $maxcol || $this->lastLine[$k] != $w )
            {
                $overwrite = TRUE;
                if ($i < $maxcol)
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['std']);
                }
                else if ($normal)
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['stz']);
                }
                else
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['new']);
                }
            }
            $i++;
        }
        if ($i > 0)
        {
            $this->lastLine = $tar;
        }
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $this->lin[$this->workbook->getActiveSheet()];
    }

    /**
     * Write a line of cell values
     *
     * @param array     $tar    : Value for each cell in the line
     * @param boolean   $normal : Write cell in standard cell format
     * @param array     $redcol
     * @param int       $maxcol
     * @return integer                    : Count of line on this sheet
     */
    function writeLineEx($tar, $normal=TRUE, $redcol=array(), $maxcol=0)
    {
        $author = "Objetkttyp:";
        $i = 0;
        foreach($tar as $k => $w)
        {
            if (!in_array($i, $redcol) || $this->lastLine[$k] != $w )
            {
                $ctext = "";
                if (strstr($w, "###"))
                {
                    $ar = explode("###", $w);
                    $w = $ar[0];
                    $ctext = $ar[1];
                }
                if ($i < $maxcol)
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['std']);
                }
                else if ($normal)
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['stz']);
                }
                else
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['new']);
                }
                if (strlen($ctext) > 0)
                {
                    $c = substr_count($ctext, "\n") +1;
                    $this->worksheet[$this->workbook->getActiveSheet()]->writeComment($this->lin[$this->workbook->getActiveSheet()], $i, $ctext, $author, 300, $c*17);
                }
            }
            $i++;
        }
        if ($i > 0)
        {
            $this->lastLine = $tar;
        }
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $this->lin[$this->workbook->getActiveSheet()];
    }
    /**
     * Write a line of cells in struct mode (the same value only once)
     *
     * @param array  	$tar	: Value for each cell in the line
     * @param integer   $max	: Maximum of cells
     * @param string    $lnk	: Link to other cell
     * @param boolean   $prop	: Save property
     * @return mixed			: FALSE = Error, array with 'lin' and 'sheet'
     */
    function writeStructLine($tar, $max, $lnk="", $prop=FALSE)
    {
        if ($this->workbook->getActiveSheet() == -1) return FALSE;
        $overwrite=FALSE;
        $cnt = count($tar);
        if ($cnt > $max) $cnt = $max;
        $i = 0;
        if ($prop)
        {
            if ($max > 1) $i = 1;
        }
        $ret['lin'] = $this->lin[$this->workbook->getActiveSheet()] +1;
        $ret['sheet'] = $this->worksheet[$this->workbook->getActiveSheet()]->name();
        foreach($tar as $k => $w)
        {
            if ($k < $max)
            {
                if ($overwrite || $i > $cnt || !isset($this->lastLine[$k]) || $this->lastLine[$k] != $w )
                {
                    $overwrite = TRUE;
                    $c = substr($w, 0, 1);
                    $v = substr($w, 1);
                    if ($c == '@')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->writeUrl($this->lin[$this->workbook->getActiveSheet()], $i, $lnk, $v, $this->format['blue']);
                    }
                    else if ($c == '*')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['blue']);
                        $ret['col'] = $i;
                    }
                    else if ($c == '=')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['red']);
                    }
                    else if ($c == '&')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['green']);
                    }
                    else if ($c == '/')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['dgreen']);
                    }
                    else if ($c == '+')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['magenta']);
                    }
                    else if ($c == '-')
                    {
                        $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $v, $this->format['dmagenta']);
                    }
                }
            }
            else
            {
                unset($tar[$k]);
            }
            $i++;
        }
        if (!$prop && $i > 0)
        {
            $this->lastLine = $tar;
        }
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $ret;
    }
    /**
     * Write line of cells
     *
     * @param array     $tar	        : Value for each cell in the line
     * @param array     $format	        : Format name of each cell in a array
     * @param boolean   $useArrayIndex  : Use the index of cell value array not the logical
     * @return integer					: Count of line on this sheet
     */
    function writeSingleLine($tar, $format=null, $useArrayIndex=FALSE)
    {
        $i = 0;
        foreach($tar as $k => $w)
        {
            if ($useArrayIndex)
            {
                $i = $k;
            }
            if ( !isset($format[$i]) )
            {
                $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format['std']);
            }
            else
            {
                if (isset($this->format[$format[$i]]))
                {
                    $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $this->format[$format[$i]]);
                }
                else
                {
                    trigger_error(sprintf("writeSingleLine Format nicht gesetzt : %s", $format[$i]));
                }
            }
            $i++;
        }
        $this->lastLine = $tar;
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $this->lin[$this->workbook->getActiveSheet()];
    }
    /**
     * Write a value of a cell
     *
     * @param integer 	$lin		: Line number
     * @param integer 	$col		: Cell number
     * @param mixed 	$val		: Value of cell
     * @param string 	$format		: NULL = std format, or format name
     */
    function writeCell($lin, $col, $val, $format=null)
    {
        if ( !isset($format) )
        {
            $this->worksheet[$this->workbook->getActiveSheet()]->write($lin, $col, $val, $this->format['std']);
        }
        else
        {
            if (isset( $this->format[$format]))
            {
                $this->worksheet[$this->workbook->getActiveSheet()]->write($lin, $col, $val, $this->format[$format]);
            }
            else
            {
                trigger_error(sprintf("writeCell  Format nicht gesetzt : %s", $format));
            }
        }
    }
    /**
     * Enter description here...
     *
     * @param array         $tar	: Value for each cell in the line
     * @param boolean 		$normal	: TRUE = std format else new format
     * @return integer				: Count of line on this sheet
     */
    function writeArray($tar, $normal=TRUE)
    {
        $i = 0;
        if ($normal)
            $frmt = &$this->format['std'];
        else
            $frmt = &$this->format['new'];
        foreach($tar as $k => $w)
        {
            $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $k, $frmt);
            $i++;
            $this->worksheet[$this->workbook->getActiveSheet()]->write($this->lin[$this->workbook->getActiveSheet()], $i, $w, $frmt);
            $i++;
        }
        $this->lastLine = $tar;
        $this->lin[$this->workbook->getActiveSheet()]++;
        return $this->lin[$this->workbook->getActiveSheet()];
    }
    /**
     * Write a formel to a cell
     *
     * @param integer 	$lin		: Line number
     * @param integer 	$col		: Cell number
     * @param string 	$formel		: formel text
     * @param string 	$format		: NULL = std format or format name
     * @return integer				: Count of line on this sheet
     */
    function writeFormel($lin, $col, $formel, $format=null)
    {
        if ( !isset($format) )
        {
            $this->worksheet[$this->workbook->getActiveSheet()]->write($lin, $col, $formel, $this->format['std'], ExcelFormat::AS_FORMULA);
        }
        else
        {
            if (isset($this->format[$format]))
            {
                $this->worksheet[$this->workbook->getActiveSheet()]->write($lin, $col, $formel, $this->format[$format], ExcelFormat::AS_FORMULA);
            }
            else
            {
                trigger_error(sprintf("writeFormel Format nicht gesetzt : %s", $format));
            }
        }
        $this->lin[$this->workbook->getActiveSheet()] = $lin +1;
        return $this->lin[$this->workbook->getActiveSheet()];
    }
    /**
     * Write a excel hyperlink to the given cell....
     *
     * @param integer   $lin
     * @param integer   $col
     * @param string    $lnk
     * @param mixed     $val
     * @param string    $format
     */
    public function writeURL($lin, $col, $lnk, $val, $format=null)
    {
        if ( !isset($format) )
        {
            $this->worksheet[$this->workbook->getActiveSheet()]->writeUrl($lin, $col, $lnk, $val, $this->format['std']);
        }
        else
        {
            if (isset($this->format[$format]))
            {
                $this->worksheet[$this->workbook->getActiveSheet()]->writeUrl($lin, $col, $lnk, $val, $this->format[$format]);
            }
            else
            {
                trigger_error(sprintf("writeURL Format nicht gesetzt : %s", $format));
            }
        }
    }
    /**
     * Close excel workbook  (write file)
     *
     */
    public function close()
    {
        if (!is_null($this->workbook))$this->workbook->save($this->filename);
        $this->workbook = null;
    }
    /**
     * Set the column width
     *
     * @param integer $firstcol
     * @param integer $lastcol
     * @param integer $width
     */
    public function setColumn($firstcol, $lastcol, $width)
    {
        $this->worksheet[$this->workbook->getActiveSheet()]->setColWidth($firstcol, $lastcol, $width);
    }
    public function setZoom($scale=100)
    {
        $this->worksheet[$this->workbook->getActiveSheet()]->setZoom($scale);
    }
    public function protect($password)
    {
        $this->worksheet[$this->workbook->getActiveSheet()]->setProtect($password);
    }
    public function writeNote($lin, $col, $text)
    {
        $this->worksheet[$this->workbook->getActiveSheet()]->writeComment($lin, $col, $text, "", 200, 200);
    }

}
?>