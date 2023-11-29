<?php
/**
 * Klasse zum Verwalten von ini - Files
 * 
 * Im Inifile können Schlüssel -> Wert paare gespeichert werden.
 * Die Schlüssel können in Sektionen gruppiert werden. Standard Sektion = Global
 * Die Schlüssel werden in Kleinschreibung abgelegt (d.h Gross- Kleinschreibung wird nicht beachtet)
 * Im Namen der Sektion wird das erste Zeichen gross geschrieben der rest in Kleinschreibung.
 * Dieses Verhalten wird durch diese Klasse transparent erledigt.
 *
 */

/**
 * Class WNBInifile
 *
 * @package     helvetia
 * @category   winbinder
 */
class WNBInifile
{
	private $ini		= null;
	private $filename	= "";
	private $section	= null;
	
	/**
	 * Initialisierung eines ini - files
	 *
	 * @param string $filename				Filename des Inifile [NULL = kein File]
	 * @param string $section				Name der initialen Sektion
	 * @param array $ref					Wenn Filename = NULL, kann hier ein key -> value array übergeben werden.
	 */
	public function __construct($filename, $section=NULL, $ref=NULL)
	{
		if (!is_null($section)) 
		{
			$this->section = $this->getSectionName($section);
		}
		if (nis_file($filename))
		{
			$this->read($filename);
		}
		else 
		{
			if (is_array($ref))
				$this->ini = $ref;
			else 
				$this->ini = array();
			$this->filename = $filename;
		}
	}
	public function __destruct()
	{
		
	}
	public function getFilename()
	{
		return $this->filename;
	}
	public function save($filename=NULL)
	{
		$initext = generate_ini($this->ini, sprintf("; Save inifile by WNBInifile %s \r\n", date("m.d.Y H:i:s")));
		if (!is_null($filename))
		{
			nfile_put_contents($filename, $initext);
		}
		else 
		{
			nfile_put_contents($this->filename, $initext);
		}
	}
	public function read($filename)
	{
		$buf = nfile_get_contents($filename);
		if (strstr($buf, "\\\r\n"))
		{
			$ar = explode("\\\r\n", $buf);
			$buf = "";
			foreach($ar as $ss)
			{
				$buf .= ltrim($ss);
			}
		}
		$this->ini = parse_ini($buf);
		$this->filename = $filename;
	}
	public function set($val, $key=NULL, $section=NULL)
	{
		if (is_null($section)) 
		{
			$section = $this->section;
		}
		else 
		{
			$section = $this->getSectionName($section);
		}
		if (is_null($section))
		{
			if (is_null($key))
				$this->ini[] = $val;
			else 
				$this->ini[$key] = $val;
		}
		else 
		{
			if (is_null($key))
				$this->ini[$section][] = $val;
			else 
				$this->ini[$section][$key] = $val;
		}
	}
	public function setSection($section)
	{
		$this->section = $this->getSectionName($section);
	}
	public function setSectionValue($val, $section=NULL)
	{
		if (is_null($section)) 
		{
			$section = $this->section;
		}
		else 
		{
			$section = $this->getSectionName($section);
		}
		$this->ini[$section] = $val;
	}
	public function resetSection()
	{
		$this->section = NULL;
	}
	public function get($key=NULL, $type=NULL, $section=NULL)
	{
		$val = NULL;
		if (!is_null($key)) $key = strtolower($key);
		if (is_null($section)) 
		{
			$section = $this->section;
		}
		else 
		{
			$section = $this->getSectionName($section);
		}
		if (is_null($key) && isset($this->ini[$section]))
		{
			return $this->ini[$section];
		}
		else 
		{
			if (isset($this->ini[$section][$key])) 
			{
				$val = $this->ini[$section][$key];
			}
		}
		if (is_null($type)) return $val;
		switch ($type)
		{
			case "bool" : return (bool)$val;
			case "int" : return (int)$val;
			case "float" : return (float)$val;
			default : return (string)$val;
		}
	}
	public function delete($key=NULL, $section=NULL)
	{
		if (!is_null($key)) $key = strtolower($key);
		if (is_null($section))
		{
			if (is_null($key))  // key = null + section = null  delete all entries
			{
				unset($this->ini);
			}
			else if (is_null($this->section))
			{
				unset($this->ini[$key]);
			}
			else 
			{
				unset($this->ini[$section][$key]);
			}
		}
		else 
		{
			$section = $this->getSectionName($section);
			if (is_null($key))
			{
				unset($this->ini[$section]);
			}
			else 
			{
				unset($this->ini[$section][$key]);
			}
		}
	}
	public function getSectionsArray()
	{
		$ret = array();
		foreach($this->ini as $k => $v)
		{
			$ret[] = $k;
		}
		return $ret;
	}
	public function getNetPath($key, $alternateSection, $section=null)
	{
		$path = $this->get($key, null, $section);
		if ($this->testNetPath($path)) return $path;
		return $this->get($key, null, $alternateSection);
	}
	public function getNetFile($key, $alternateSection, $section=null)
	{
		$path = $this->get($key, null, $section);
		if ($this->testNetFile($path)) return $path;
		return $this->get($key, null, $alternateSection);
	}
	public function testNetFile($path)
	{
		if (is_null($path) || strlen($path) == 0) return false;
		$err = error_reporting(E_ERROR);
		$fp = nfopen($path, "r");
		error_reporting($err);
		if (is_resource($fp))
		{
			fclose($fp);
			return true;
		}
		return false;
	}
	public function testNetPath($path)
	{
		if (is_null($path) || strlen($path) == 0) return false;
		$err = error_reporting(E_ERROR);
		$fp = nopendir($path);
		error_reporting($err);
		if (is_resource($fp))
		{
			closedir($fp);
			return true;
		}
		return false;
	}
	private function getSectionName($section)
	{
		return strtoupper(substr($section, 0, 1)).strtolower(substr($section, 1));
	}

    /**
     * Setzen der Werte einer Sektion als Globale Variabeln
     *
     * @param string $section
     */
    public function setVariablen($section)
    {
        $ar = $this->get(null, null, $section);
        if (is_array($ar))
        {
            foreach($ar as $k => $v)
            {
                $cmd = sprintf("\$GLOBALS['%s'] = %s;", $k, $v);
                eval($cmd);
            }
        }
    }
}























?>