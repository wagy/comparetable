<?php
/**
 * Basis PM - Objekte  <br>
 * Abbildung in PHP
 * 
 * 
 * 
 * 
 * @author 		Walter Gyr GYW		WACOSOFT
 * 
 * @version 	$Revision: 1.17 $
 * @package 	base
 */

/**
 *
 * @author 		Walter Gyr (GYW)
 * @version  	$Revision: 1.17 $
 */
define("HIDESTATEOFF", "no");
define("HIDESTATEON",  "on");

function testHideState($hideState)
{
	if (is_bool($hideState))
	{
		return $hideState;
	}
	else if (is_string($hideState) && $hideState == HIDESTATEON)
	{
		return TRUE;
	}
	else if (is_numeric($hideState) && $hideState >= 1)
	{
		return TRUE;
	}	
	return FALSE;
}
function testBool($val)
{
	if (is_bool($val))
	{
		return $val;
	}
	$ival = (int)$val;
	if ($ival >= 1)
	{
		return TRUE;
	}	
	return FALSE;
}
function setValueType($val)
{
	static $valueArray = array(0=>"***none***", 1=>"s", 2=>"s1", 3=>"sr", 4=>"s1r");
	
	if (isset($valueArray[$val])) return $valueArray[$val];
	if (in_array($val, $valueArray)) return $val;
	return "";
}
function setFormatType($val)
{
	static $formatArray = array(0=>"***none***", 1=>"float", 2=>"integer", 3=>"string", 4=>"date", 5=>"mlstring", 6=>"time", 7=>"bool", 8=>"method");
	
	if (isset($formatArray[$val])) return $formatArray[$val];
	if (in_array($val, $formatArray)) return $val;
	return "";
}
function getFormatType($val)
{
	static $formatType = array(0=>"none", 1=>"attribute", 2=>"merkmal", 3=>"methode", 4=>"max");
	if (isset($formatType[$val])) return $formatType[$val];
	return "";
}

/** 
*	Represent a PM Modell object
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMClass
{
	public		$name					= null;
	public		$id						= null;
    /** @var null|PMClassRelation[] $forwardRelationArray */
	public		$forwardRelationArray	= null;
    /** @var null|PMClassProperty[] $propertiesArray */
	public		$propertiesArray		= null;
	public 		$superClassId			= null;
	public 		$isAbstract				= FALSE;
	public 		$hasGenerations			= FALSE;
	public 		$count					= 0;

    /**
     * Create PMClass class
     *
     * @param string  $id               : PMID of PM model class
     * @param integer $count            : number from xml parser
     * @param boolean $isAbstract       : class is abstract
     * @param string  $supperClassId    : PMID of parent class
     * @param boolean $hasGenerations
     */
	public function __construct($id, $count, $isAbstract, $supperClassId, $hasGenerations)
	{
		$this->id  				= $id;
		$this->count			= $count;
		$this->isAbstract		= testBool($isAbstract);
		$this->superClassId 	= $supperClassId;
		$this->hasGenerations	= testBool($hasGenerations);
	}
	/**
	* Get the first property at this PMClass
	*
	* @return PMClassProperty	
	*/
	public function getFirstProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return reset($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get the next property at this PMClass
	*
	* @return PMClassProperty
	*/
	public function getNextProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return next($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get property by name at this PMClass
	*
	* @param  string	=> Name of property
	* @return PMClassProperty
	*/
	public function getPropertyByName($name)
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			if (isset($this->propertiesArray[$name]))
			{
				return $this->propertiesArray[$name];
			}
		}
		return NULL;
	}
	/**
	* Get the first relation at this PMClass
	*
	* @return PMClassRelation
	*/
	public function getFirstRelation()
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			return reset($this->forwardRelationArray);
		}
		return NULL;
	}
	/**
	* Get the next relation at this PMClass
	*
	* @return PMClassRelation
	*/
	public function getNextRelation()
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			return next($this->forwardRelationArray);
		}
		return NULL;
	}
}

/** 
*	Represent a PM Modell relation
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMClassRelation
{
	public		$name			= NULL;
	public		$id				= NULL;
	public 		$sourceClassId	= NULL;
	public 		$targetClassId	= NULL;
	public		$properties		= NULL;
	public 		$isReverse		= FALSE;
	public 		$count			= 0;

	/**
	* Create PMClassRelation class
	*
	* @param string		$id				: PMID of PM model relation
	* @param boolean	$sourceClassId	: PMID of source model object
	* @param string		$targetClassId	: PMID of target model object
	* @param integer	$count			: number from xml parser
	*
	* @return object	void
	*/	
	public function __construct($id, $sourceClassId, $targetClassId, $count)
	{
		$this->id				= $id;
		$this->sourceClassId	= $sourceClassId;
		$this->targetClassId 	= $targetClassId;
		$this->count			= $count;
	}
	/**
	* Get the first property at this PMClassRelation
	*
	* @return PMClassProperty
	*/
	public function getFirstProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return reset($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get the next property at this PMClassRelation
	*
	* @return PMClassProperty
	*/
	public function getNextProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return next($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get property by name at this PMClassRelation
	*
	* @param  string	=> Name of property
	* @return PMClassProperty
	*/
	public function getPropertyByName($name)
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			if (isset($this->propertiesArray[$name]))
			{
				return $this->propertiesArray[$name];
			}
		}
		return NULL;
	}
}

/** 
*	Represent a PM Modell object property
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMClassProperty
{
	public		$name			= NULL;
	public		$id				= NULL;
	public 		$valueType		= NULL;
	public 		$formatType		= NULL;
	public 		$isRelation		= FALSE;
	public 		$value			= NULL;
	public 		$count			= 0;
	
	/**
	* Create PMClassProperty class
	*
	* @param string		$id			: PMID of PM model property
	* @param boolean	$valueType	: PM value type
	* @param string		$formatType	: PM format type
	* @param integer	$count		: number from xml parser
	*
	* @return object	void
	*/	
	public function __construct($id, $valueType, $formatType, $count)
	{
		$this->id	 			= $id;
		$this->valueType		= setValueType($valueType);
		$this->formatType 		= setFormatType($formatType);
		$this->count			= $count;
	}
	public function setRelation($rel)
	{
		$this->isRelation = testBool($rel);
	}
}


/** 
*	Represent a PM data for base object
*	     
* 	06.01.2005  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMObjectData
{
	public		$adaName				= NULL;
	public		$adaId					= NULL;
	public 		$adaClassId				= NULL;
	public 		$adaIsTemplate			= FALSE;
	public 		$adaHideState			= NULL;
	public		$forwardRelationArray	= NULL;		// Array of class PMRelation
	public		$reverseRelationArray	= NULL;		// Array of class PMRelation
	/**
	 * @var array PMProperty
	 */
	public		$propertiesArray		= NULL;		// Array of class PMProperty
	public 		$adaCount				= 0;
	
	/**
	* set data object by reference
	*
	* @return unknown
	*/
	public function setData($aobj)
	{
		$this->adaId				=& $aobj->adaId;
		$this->adaName				=& $aobj->adaName;
		$this->adaClassId			=& $aobj->adaClassId;
		$this->adaIsTemplate 		=& $aobj->adaIsTemplate;
		$this->adaHideState 		=& $aobj->adaHideState;
		if ($aobj->forwardRelationArray != NULL && is_array($aobj->forwardRelationArray))
		{
			$this->forwardRelationArray =& $aobj->forwardRelationArray;
		}
		if ($aobj->reverseRelationArray != NULL && is_array($aobj->reverseRelationArray))
		{
			$this->reverseRelationArray =& $aobj->reverseRelationArray;
		}
		if ($aobj->propertiesArray != NULL && is_array($aobj->propertiesArray))
		{
			$this->propertiesArray 		=& $aobj->propertiesArray;
		}
		$this->adaCount 			=& $aobj->adaCount;	
	}
}

/** 
*	Represent a PM base object
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMObject extends PMObjectData 
{
	public 		$id							= NULL;
	public 		$name						= NULL;
	public 		$classId					= NULL;
	public 		$className					= NULL;
	public 		$isTemplate					= FALSE;
	public 		$withAdaptorStageData		= FALSE;
	public 		$hideState					= FALSE;
    /** @var  PMGeneration[] $generationArray */
	public 		$generationArray			= NULL;     // Array of class PMGeneration
	public 		$generationFirstValidDay	= -9999999;
	public 		$generationLastValidDay		= 9999999;
	public 		$adaptorStageFirstValidDay	= -9999999;
	public 		$adaptorStageLastValidDay	= 9999999;
	public 		$count						= 0;
	
	/**
	* Create PMObject class
	*
	* @param string		$id			: PMID 
	* @param string		$classId	: PMID of model class
	* @param boolean	$template	: template class in PM
	* @param string		$hideState	: hide flag on PM
	* @param integer	$count		: number from xml parser
	*
	* @return object	void
	*/
	public function __construct($id, $classId, $template, $hideState, $count)
	{
		$this->id			= $id;
		$this->classId  	= $classId;
		$this->isTemplate	= testBool($template);
		$this->hideState 	= testHideState($hideState);
		$this->count		= $count;
	}
	public function destruct()
	{
		trigger_error("Delete object : ".$this->name, INFO);
	}
	/**
	* Get the first forward relation at this PMObject
	*
	* @param  string	Class name of target object
	* @return PMRelation
	*/
	public function getFirstRelation($targetClassName=NULL)
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			
			if (is_null($targetClassName))
			{
				return reset($this->forwardRelationArray);
			}
			$rel = reset($this->forwardRelationArray);
			if ($rel != NULL)
			{ 
				if (is_array($targetClassName))
				{
					if (in_array($rel->targetClassName, $targetClassName)) return $rel;
				}
				else 
				{
					if ($rel->targetClassName == $targetClassName) return $rel;
				}
				return $this->getNextRelation($targetClassName);
			}
			
		}
		return NULL;
	}
	/**
	* Get the next forward relation at this PMObject
	*
	* @param  string	Class name of target object
	* @return PMRelation
	*/
	public function getNextRelation($targetClassName=NULL)
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			if (is_null($targetClassName))
			{
				return next($this->forwardRelationArray);
			}
			$rel = next($this->forwardRelationArray);
			while($rel != NULL)
			{ 
				if (is_array($targetClassName))
				{
					if (in_array($rel->targetClassName, $targetClassName)) return $rel;
				}
				else 
				{
					if ($rel->targetClassName == $targetClassName) return $rel;
				}
				$rel = next($this->forwardRelationArray);
			}			
		}
		return NULL;
	}
	/**
	* Get the first reverse relation at this PMObject
	*
	* @param  string	: Class name of target object
	* @return PMRelation
	*/
	public function getFirstReverseRelation($targetClassName=NULL)
	{
		if ($this->reverseRelationArray && is_array($this->reverseRelationArray) )
		{
			if (is_null($targetClassName))
			{
				return reset($this->reverseRelationArray);
			}
			$rel = reset($this->reverseRelationArray);
			if ($rel != NULL)
			{ 
				if ($rel->sourceClassName == $targetClassName) return $rel;
				return $this->getNextReverseRelation($targetClassName);
			}
		}
		return NULL;
	}
	/**
	* Get the next reverse relation at this PMObject
	*
	* @param  string	: Class name of target object
	* @return PMRelation
	*/
	public function getNextReverseRelation($targetClassName=NULL)
	{
		if ($this->reverseRelationArray && is_array($this->reverseRelationArray) )
		{
			if (is_null($targetClassName))
			{
				return next($this->reverseRelationArray);
			}
			$rel = next($this->reverseRelationArray);
			while($rel != NULL)
			{ 
				if ($rel->sourceClassName == $targetClassName) return $rel;
				$rel = next($this->reverseRelationArray);
			}	
		}
		return NULL;
	}
	/**
	 *Get the first property at this PMObject
	 *
	 * @return PMProperty
	 */
	public function getFirstProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return reset($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get the next property at this PMObject
	*
	* @return PMProperty
	*/
	public function getNextProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return next($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get property by name at this PMObject
	*
	* @param  string	=> Name of property
	* @return PMProperty
	*/
	public function getPropertyByName($name)
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			if (isset($this->propertiesArray[$name]))
			{
				return $this->propertiesArray[$name];
			}
		}
		return NULL;
	}
	/**
	* Test of generation on this object 
	*
	* @return bool
	*/
	public function hasGeneration()
	{
		if ($this->generationArray && is_array($this->generationArray))
		{
			return TRUE;
		}
		return FALSE;
	}

    /**
     * Set generation and adaptorstage Date
     *
     * @param integer $generationDT
     * @param integer $adaptorStageDT
     * @return bool
     */
	public function setDate($generationDT, $adaptorStageDT)
	{
		if ($this->hasGeneration())
		{
			foreach($this->generationArray as $gobj)
			{
				if ($gobj->firstValidDay >= $generationDT && $gobj->lastValidDay <= $generationDT)
				{

					if ($gobj->adaptorStageArray && is_array($gobj->adaptorStageArray) )
					{
						foreach($gobj->adaptorStageArray as $aobj)
						{
							if ($aobj->firstValidDay >= $adaptorStageDT && $aobj->lastValidDay <= $adaptorStageDT)
							{
								$this->setData($aobj);
								return TRUE;
							}
						}
					}
					break;
				}
			}
		}
		return FALSE;
	}
	/**
	* get first adaptorstage 
	*
	* @return bool
	*/
	public function getFirstGeneration()
	{
		if ($this->hasGeneration())
		{
			return reset($this->generationArray);
		}		
		return NULL;
	}
	/**
	* get next adaptorstage 
	*
	* @return bool
	*/
	public function getNextGeneration()
	{
		if ($this->hasGeneration())
		{
			return next($this->generationArray);
		}
		return NULL;
	}
}


/** 
*	Represent a PM relation
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMRelation
{
	public		$name				= NULL;
	public		$id					= NULL;
	public 		$sourceId			= NULL;
	public 		$targetId			= NULL;
	public 		$targetClassName	= NULL;
	public 		$targetClassId		= NULL;
	public 		$sourceClassName	= NULL;
	public 		$sourceClassId		= NULL;
    /** @var null|PMProperty[] $propertiesArray */
	public		$propertiesArray	= NULL;
	public		$classId			= NULL;
	public 		$setReverse			= FALSE;
	public 		$reverseName		= NULL;
	public 		$count				= 0;
	
	/**
	* Create PMRelation class
	*
	* @param string		$id			: PMID 
	* @param string		$classId	: PMID of model class
	* @param boolean	$sourceId	: PMID of sorce object
	* @param string		$targetId	: PMID of target object
	* @param integer	$count		: number from xml parser
	*
	* @return object	void
	*/
	public function __construct($id, $classId, $sourceId, $targetId, $count)
	{
		$this->id	 		= $id;
		$this->classId  	= $classId;
		$this->sourceId		= $sourceId;
		$this->targetId 	= $targetId;
		$this->count		= $count;
	}
	/**
	* Get the first property at this PMRelation
	*
	* @return PMProperty
	*/
	public function getFirstProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return reset($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get the next property at this PMRelation
	*
	* @return PMProperty
	*/
	public function getNextProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return next($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get property by name at this PMRelation
	*
	* @param  string	=> Name of property
	* @return PMProperty
	*/
	public function getPropertyByName($name)
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			if (isset($this->propertiesArray[$name]))
			{
				return $this->propertiesArray[$name];
			}
		}
		return NULL;
	}
}

/** 
*	Represent a PM object property
*	     
* 	14.12.2004  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMProperty
{
	
	public		$name			= NULL;
	public 		$type			= NULL;
	public 		$valueType		= NULL;
	public 		$formatType		= NULL;
	public 		$value			= NULL;
	public 		$defaultValue	= NULL;
	public      $folder			= NULL;
	public 		$isReleation	= FALSE;
	public 		$isNull			= TRUE;
	public 		$isCalc			= FALSE;
	public		$hideState		= FALSE;
	public 		$range			= NULL;
	public 		$dimension		= 0;
	public 		$count			= 0;
	
	/**
	* Create PMProperty class
	*
	* @param string		$hideState	: hide flag on PM
	* @param string		$type		: object_attribute, class_attribute type definition on PM
	* @param boolean	$valueType	: PM value type
	* @param string		$formatType	: PM format type
	* @param integer	$count		: number from xml parser
	*
	* @return object	void
	*/
	public function __construct($hideState, $type, $valueType, $formatType, $count)
	{
		$this->hideState 		= testHideState($hideState);
		if ($type >= 100)
		{
			$this->isCalc = TRUE;
			$type -= 100;
		}
		$this->type				= $type;
		$this->valueType		= setValueType($valueType);
		$this->formatType 		= setFormatType($formatType);
		$this->count			= $count;
	}
	/**
	 * Set member isRelation for a relation property 
	 *
	 * @param boolean, int [0,1] $rel	
	 */
	public function setRelation($rel)
	{
		$this->isRelation = testBool($rel);
	}
	/**
	 * Set range array
	 *
	 * @param array, mixed
	 */
	public function setRange($range)
	{
		$this->range = $range;
	}
	/**
	 * Get value of property, when value not set get defaultValue
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if ($this->isNull) return NULL;
		if ($this->value == NULL) return $this->defaultValue;
		return $this->value;
	}
}

/** 
*	Represent a PM object generation
*	     
* 	06.01.2005  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMGeneration
{
	public		$name				= null;
	public		$id					= null;
    /** @var  PMAdaptorStage[] $adaptorStageArray*/
	public 		$adaptorStageArray	= null;
	public 		$firstValidDay		= -9999999;
	public 		$lastValidDay		= 9999999;
	public 		$endModification	= 9999999;
	public 		$endExtension		= 9999999;
	public 		$count				= 0;

    /**
     * Create PMGeneration class
     *
     * @param string     $id           : PMID
     * @param integer    $count        : number from xml parser
     */
	public function __construct($id, $count)
	{
		$this->id 			= $id;
		$this->count		= $count;
	}
	/**
	* get first adaptorstage 
	*
	* @return	PMAdaptorStage
	*/
	public function getFirstAdaptorStage()
	{
		if (is_array($this->adaptorStageArray))
		{
			return reset($this->adaptorStageArray);
		}
		return NULL;
	}
	/**
	* get next adaptorstage 
	*
	* @return	PMAdaptorStage
	*/
	public function getNextAdaptorStage()
	{
		if (is_array($this->adaptorStageArray))
		{
			return next($this->adaptorStageArray);
		}
		return NULL;
	}
}

/** 
*	Represent a PM object adaptorstage
*	     
* 	06.01.2005  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMAdaptorStage extends PMObjectData 
{
	public 		$firstValidDay			= -9999999;
	public 		$lastValidDay			= 9999999;

    /**
     * Create PMAdaptorStage class
     *
     * @param string     $id            : PMID
     * @param string     $classId       : PMID of adaptorstage object
     * @param string     $hideState     : hide flag on PM
     * @param integer    $count         : number from xml parser
     */
	public function __construct($id, $classId, $hideState, $count)
	{
		$this->adaId				= $id;
		$this->adaClassId			= $classId;
		$this->adaHideState 		= testHideState($hideState);
		$this->count				= $count;
	}
	/**
	* Get the first forward relation at this PMAdaptorStage
	*
	* @return PMRelation
	*/
	public function getFirstRelation()
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			return reset($this->forwardRelationArray);
		}
		return NULL;
	}
	/**
	* Get the next forward relation at this PMAdaptorStage
	*
	* @return PMRelation
	*/
	public function getNextRelation()
	{
		if ($this->forwardRelationArray && is_array($this->forwardRelationArray) )
		{
			return next($this->forwardRelationArray);
		}
		return NULL;
	}
	/**
	* Get the first reverse relation at this PMAdaptorStage
	*
	* @return PMRelation
	*/
	public function getFirstReverseRelation()
	{
		if ($this->reverseRelationArray && is_array($this->reverseRelationArray) )
		{
			return reset($this->reverseRelationArray);
		}
		return NULL;
	}
	/**
	* Get the next reverse relation at this PMAdaptorStage
	*
	* @return PMRelation
	*/
	public function getNextReverseRelation()
	{
		if ($this->reverseRelationArray && is_array($this->reverseRelationArray) )
		{
			return next($this->reverseRelationArray);
		}
		return NULL;
	}
	/**
	* Get the first property at this PMAdaptorStage
	*
	* @return PMProperty
	*/
	public function getFirstProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return reset($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get the next property at this PMAdaptorStage
	*
	* @return PMProperty
	*/
	public function getNextProperty()
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			return next($this->propertiesArray);
		}
		return NULL;
	}
	/**
	* Get property by name at this PMAdaptorStage
	*
	* @param  string	=> Name of property
	* @return PMProperty	
	*/
	public function getPropertyByName($name)
	{
		if ($this->propertiesArray && is_array($this->propertiesArray) )
		{
			if (isset($this->propertiesArray[$name]))
			{
				return $this->propertiesArray[$name];
			}
		}
		return NULL;
	}
}
/** 
*	Define on PMTable a column
*	     
* 	15.04.2005  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMColumnHeader
{
	/** @var null|string  */
	public $name				= null;
	/** @var null|string  */
	public $longName			= null;
	/** @var null|string  */
	public $type				= null;
	/** @var int  */
	public $pos					= -1;
	/** @var bool  */
	public $key					= false;
	/** @var null|string */
	public $externalName		= null;

	/**
	 * Set all values of this class
	 *
	 * @param string $name : Name of colum (PM technical name)
	 * @param string $longName : View name of column (PM description)  deprecated since PM3.4.6.x
	 * @param string $type : Datatype internal = PM type, external = DB type
	 * @param integer $idx : Column number 0...
	 * @param bool $key [optional]
	 * @param string $externalName : Column name in external table (when different from name)
	 */
	public function __construct($name, $longName, $type, $idx, $key=false, $externalName=null)
	{
		$this->name = $name;
		$this->longName = $longName;
		$this->type = $type;
		$this->pos = $idx;
		$this->key = $key;
		$this->externalName = $externalName;
	}
}
/** 
*	Represent a PM table (internal and external)
*	     
* 	15.04.2005  create  
*    
*	@author 	Walter Gyr      GYW
*	@version 	$Revision: 1.17 $
*	@package 	base  
*/
class PMTable
{
	public 	$id					= NULL;
	public  $name				= NULL;
	public	$rowCount			= 0;
	public  $colCount			= 0;
	public  $isExternal			= FALSE;
	/** @var null|array $tableArray */
	private $tableArray 		= NULL;
  	/** @var null|PMColumnHeader[] $columnDefinition  */
	private $columnDefinition	= NULL;
	private $externalDefinition	= NULL;
	private $odbc				= NULL;
	private $connectionDef		= NULL;
    	/** @var null|resource $result */
	private $result				= NULL;
	
	/**
	 * Set standard value for PM table
	 *
	 * @param string 	$id			: PMID
	 * @param string 	$name		: PM - Name of table
	 * @param integer 	$row		: Number of rows
	 * @param integer	$col		: Number of columns
	 * @param boolean 	$external	: External PM - Table
	 */
	public function __construct($id, $name, $row, $col, $external)
	{
		$this->id = $id;
		$this->name = $name;
		$this->rowCount = $row;
		$this->colCount = $col;
		$this->isExternal = testBool($external);
	}
	/**
	 * Close open odbc connection and release column definition objects
	 *
	 */
	public function __destruct()
	{
		if (is_array($this->columnDefinition))
		{
			foreach($this->columnDefinition as  $v)
			{
				unset($v);
			}
			$this->columnDefinition = NULL;
		}
		if (is_object($this->odbc))
		{
			$this->odbc->close();
			$this->odbc = NULL;
		}
	}
	/**
	 * Set external table flag
	 *
	 * @param boolean	$val
	 */
	public function setExternalDefinition($val)
	{
		$this->externalDefinition = $val;
	}
	/**
	 * Get external table flag
	 *
	 * @return boolean
	 */
	public function getExternalDefinition()
	{
		return $this->externalDefinition;
	}
	public function getConnectionDef()
	{
		if ($this->isExternal)
		{
			if (is_null($this->connectionDef))
			{
				$ar = explode(";", $this->externalDefinition);
				foreach($ar as $v)
				{
					if (strstr($v, "="))
					{
						$tr = explode("=", $v);
						$this->connectionDef[$tr[0]] = $tr[1];
					}
					else 
					{
						$this->connectionDef[$v] = TRUE;
					}
				}
			}
			return $this->connectionDef;
		}
		return NULL;
	}
	/**
	 * Add one column definition to the internal array
	 *
	 * @param integer 	$idx			: Logical position number of this column
	 * @param string 	$shortName		: Name of colum (PM technical name)
	 * @param string 	$longName		: View name of column (PM description)
	 * @param string 	$type			: Datatype internal = PM type, external = DB type
	 */
	public function addColumnDefinition($idx, $shortName, $longName, $type)
	{
		$this->columnDefinition[$shortName] = new PMColumnHeader($shortName, $longName, $type, $idx);
	}
	/**
	 * Get the first column definition
	 *
	 * @return PMColumnHeader 
	 */
	public function getFirstColumnDefinition()
	{
		if (!$this->isExternal || $this->initExternal())
		{
			if (is_array($this->columnDefinition))
			{
				reset($this->columnDefinition);
				return current($this->columnDefinition);
			}
		}
		return NULL;
	}
	/**
	 * Get the first column definition
	 *
	 * @return PMColumnHeader 
	 */
	public function getNextColumnDefinition()
	{
		if (!$this->isExternal || $this->initExternal())
		{
			if (is_array($this->columnDefinition))
			{
				return next($this->columnDefinition);
			}
		}
		return NULL;
	}
	/**
	 * Get column number 
	 *
	 * @return integer
	 */
	public function getColumnNumber($name)
	{
		if (!$this->isExternal || $this->initExternal())
		{
			if (is_array($this->columnDefinition))
			{
				reset($this->columnDefinition);
				foreach($this->columnDefinition as $ch)
				{
					if ($ch->name == $name) return $ch->pos;
					
				}
			}
		}
		return -1;
	}
/**
	 * Get column definition by name 
	 *
	 * @return PMColumnHeader 
	 */
	public function getColumnDefinitionByName($name)
	{
		if (!$this->isExternal || $this->initExternal())
		{
			if (is_array($this->columnDefinition))
			{
				if (isset($this->columnDefinition[$name])) return $this->columnDefinition[$name];
			}
		}
		return NULL;
	}
	/**
	* Get column definition array
	*
	* @return PMColumnHeader[]
	*/
	public function getColumnDefinition()
	{
		return $this->columnDefinition;
	}
	/**
	 * Add a row to the internal row array
	 *
	 * @param integer 	$row
	 * @param array 	$valueArray
	 */
	public function addRow($row, $valueArray)
	{
		$this->tableArray[$row] = $valueArray;
	}
	/**
	 * Get first row
	 *
	 * @return NULL | array		:  Value array(0=>value of column 0, 1=>value of column 1, ....
	 */
	public function getFirstRow()
	{
		if ($this->isExternal)
		{
			if ($this->initExternal())
			{
				if (is_resource($this->result))
				{
					odbc_free_result($this->result);
				}
				$this->result = $this->odbc->cmd("SELECT * FROM ".$this->connectionDef['TABLE']);
				if ($this->result)
				{
					if (odbc_fetch_row($this->result))
					{
						return odbc_fetch_array($this->result);
					}
				}
			}
			$this->result = NULL;
		}
		else if (is_array($this->tableArray))
		{
			reset($this->tableArray);
			return current($this->tableArray);
		}
		return NULL;
	}

    /**
     * Sortierung des Schlüssels
     */
    public function sortRows()
    {
        if (is_array($this->tableArray))
        {
            ksort($this->tableArray);
        }
    }
	/**
	 Get next row
	 *
	 * @return NULL | array		:  Value array(0=>value of column 0, 1=>value of column 1, ....
	 */
	public function getNextRow()
	{
		if ($this->isExternal)
		{
			if ($this->result)
			{
				if (odbc_fetch_row($this->result))
				{
					return odbc_fetch_array($this->result);
				}
				odbc_free_result($this->result);
				$this->result = NULL;
			}
		}
		else if (is_array($this->tableArray))
		{
			return next($this->tableArray);
		}
		return NULL;
	}	
	/**
	 * Initialize the odbc connection for external table
	 *
	 * @return boolean
	 */
	private function initExternal()
	{
		if ($this->isExternal)
		{
			if (is_null($this->odbc))
			{
				$this->getConnectionDef();
				if (isset($this->connectionDef['ODBC']) && $this->connectionDef['ODBC'])
				{
					$this->odbc = new ODBCSTD($this->connectionDef['DSN'], $this->connectionDef['UID'], $this->connectionDef['PWD']);
					if ($this->odbc->isConnected())
					{
						$tab = $this->odbc->getTableInfoArray(NULL, array($this->connectionDef['TABLE']));
						foreach($tab as $k => $v)
						{
							if (is_numeric($k))
							{
								$this->addColumnDefinition($k, $v['COLUMN_NAME'], $v['COLUMN_NAME'], strtoupper($v['TYPE_NAME']));
							}
						}
					}
					else 
					{
						$this->odbc = NULL;
						trigger_error("Keine Verbindung zu : ".$this->externalDefinition, ERROR);
						return FALSE;
					}
				}
				else 
				{
					trigger_error("Unbekannter DB Type in PM : ".$this->externalDefinition, ERROR);
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}
}

?>