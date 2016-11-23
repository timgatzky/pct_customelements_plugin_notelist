<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_plugin_notelist
 * @link		http://contao.org
 * @license     LGPL
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Plugins\NoteList;

/**
 * Class file
 * Variants
 */
class Variants
{
	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;

	/**
	 * Instantiate this class and return it (Factory)
	 * @return object
	 * @throws Exception
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new static();
		}
		return self::$objInstance;
	}
	
	
	/**
	 * Create widgets and return the widget object
	 * @param array
	 * @return object
	 */
	public function loadFormField($arrFieldDef,$objAttribute=null)
	{
		switch($objAttribute->get('type'))
		{
			case 'select':
			case 'checkbox':
			case 'radio':
				// generate widget by type
				$strClass = $GLOBALS['TL_FFL'][$arrFieldDef['inputType']];
				if (!class_exists($strClass))
				{
					continue;
				}
		
				$objWidget = new $strClass($strClass::getAttributesFromDca($arrFieldDef, $arrFieldDef['name'], $arrFieldDef['value']));
				break;
			case 'tags':
				$arrFieldDef['inputType'] = 'checkbox';
				
				// generate widget by type
				$strClass = $GLOBALS['TL_FFL'][$arrFieldDef['inputType']];
				if (!class_exists($strClass))
				{
					continue;
				}
				$arrFieldDef['eval']['multiple'] = true;
				
				$objWidget = new $strClass($strClass::getAttributesFromDca($arrFieldDef, $arrFieldDef['name'], $arrFieldDef['value']));
				$objRow = $objAttribute->get('objActiveRecord');
				$varValue = deserialize($objRow->{$objAttribute->get('alias')});
				if(!is_array($varValue))
				{
					$varValue = array_filter(array($varValue),'strlen');
				}
				
				if(empty($varValue))
				{
					return null;
				}
				
				// fetch the readable values
				$strSource = $arrFieldDef['tabletree']['source'] ?: $objAttribute->get('tag_table');
				$strValueField = $arrFieldDef['tabletree']['valueField'] ?: $objAttribute->get('tag_value');
				$strKeyField = $arrFieldDef['tabletree']['keyField'] ?: $objAttribute->get('tag_key') ?: 'id';
				$strSorting = $arrFieldDef['tabletree']['sortingField'] ?: $objAttribute->get('tag_sorting');
				
				$objResult = \Database::getInstance()->prepare("SELECT * FROM ".$strSource." WHERE id IN(".implode(',', $varValue).")".($strSorting ? " ORDER BY ".$strSorting:"") )->execute();
				if($objResult->numRows < 1)
				{
					return null;
				}
				
				$arrOptions = array();
				while($objResult->next())
				{
					$arrOptions[] = array('value'=>$objResult->{$strKeyField}, 'label'=>$objResult->{$strValueField});
				}
				$objWidget->__set('options',$arrOptions);
				
				break;
			case 'selectdb':
				$strClass = $GLOBALS['TL_FFL'][$arrFieldDef['inputType']];
				if (!class_exists($strClass))
				{
					continue;
				}
				
				unset($arrFieldDef['options_callback']);
				$objWidget = new $strClass($strClass::getAttributesFromDca($arrFieldDef, $arrFieldDef['name'], $arrFieldDef['value']));
				
				$objDC = new \DC_Table($arrFieldDef['source']);
				$objDC->field = $objAttribute->get('alias');
				$arrOptions = $objAttribute->getOptions($objDC);
				
				if(count($arrOptions) > 0)
				{	
					$tmp = array();
					foreach($arrOptions as $k => $v)
					{
						$tmp[] = array('value'=>$k,'label'=>$v);
					}
					$arrOptions = $tmp;
				}
				$objWidget->__set('options',$arrOptions);
				
				break;
			// HOOK: allow other extensions to insert widgets
			default:
				if (isset($GLOBALS['TL_HOOKS']['CUSTOMELEMENTNOTELIST']['loadFormField']) && count($GLOBALS['TL_HOOKS']['CUSTOMELEMENTNOTELIST']['loadFormField']) > 0)
				{
					foreach($GLOBALS['TL_HOOKS']['CUSTOMELEMENTNOTELIST']['loadFormField'] as $callback)
					{
						return \System::importStatic($callback[0])->{$callback[1]}($arrFieldDef,$objAttribute);
					}
				}
				return null;
				break;
		}
		
		$objWidget->__set('id',($arrFieldDef['id'] ? $arrFieldDef['id'] : $objAttribute->get('id')) );
		$objWidget->__set('name',($arrFieldDef['name'] ? $arrFieldDef['name'] : $objAttribute->get('alias')) );
		$objWidget->__set('label',$arrFieldDef['label'][0]);
		
		return $objWidget;
	}
	
	
	/**
	 * Reformat options array for widgets
	 * @param array
	 * @return array
	 */
	protected function getOptions($arrFieldDef)
	{
		if(count($arrFieldDef['options']) < 1 && !$arrFieldDef['eval']['includeBlankOption'])
		{
			return array();
		}
		
		$arrReturn = array();
		if($arrFieldDef['inputType'] == 'select' && $arrFieldDef['eval']['includeBlankOption'])
		{
			$arrReturn[0] = array('value'=>$GLOBALS['TL_LANG']['customelements_notelist']['blankOption']);
		}
		
		foreach($arrFieldDef['options'] as $value => $label)
		{
			$arrReturn[] = array('value'=>$value,'label'=>$label);
		}
		
		return $arrReturn;		
	}
	
}