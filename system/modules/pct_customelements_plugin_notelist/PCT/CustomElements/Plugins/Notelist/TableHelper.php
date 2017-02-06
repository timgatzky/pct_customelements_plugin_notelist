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
namespace PCT\CustomElements\Plugins\Notelist;


/**
 * Class file
 * TableHelper
 */
class TableHelper extends \Contao\Backend
{
	/**
	 * 
	 */
	public function getSources(\DataContainer $objDC)
	{
		$arrReturn = array();
		$objDatabase = \Database::getInstance();
		
		$objCEs = $objDatabase->prepare("SELECT * FROM tl_pct_customelement WHERE alias!=''")->execute();
		if($objCEs->numRows > 0)
		{
			while($objCEs->next())
			{
				if($objCEs->isCTE)
				{
					$arrReturn['ce::tl_content::'.$objCEs->id] = 'CustomElement: '.$objCEs->title . ' ['.$objCEs->id.'] (content)';
				}
				
				if($objCEs->isFMD)
				{
					$arrReturn['ce::tl_module::'.$objCEs->id] = 'CustomElement: '.$objCEs->title . ' ['.$objCEs->id.'] (module)';
				}
				
				// fetch customcatalogs
				$objCCs = $objDatabase->prepare("SELECT * FROM tl_pct_customcatalog WHERE active=1 AND pid=?")->execute($objCEs->id);
				if($objCCs->numRows < 1)
				{
					continue;
				}
				
				while($objCCs->next())
				{
					$strTable =  ($objCCs->mode == 'new' ? $objCCs->tableName : $objCCs->existingTable);
					$arrReturn['cc::'.$strTable.'::'.$objCCs->id] = 'CustomCatalog: '.$objCCs->title . ' ['.$objCCs->id.']';
				}
			}
		};
		
		return $arrReturn;
	}
	
	
	/**
	 * Return the attributes from the custom element selected
	 * @param object
	 * @return array
	 */
	public function getAttributesBySelection(\DataContainer $objDC)
	{
		if(strlen($objDC->activeRecord->customelements_notelist_source) < 1)
		{
			return array();
		}
		
		$objAttributes = null;
		
		$arrSource = explode('::', $objDC->activeRecord->customelements_notelist_source);
		switch($arrSource[0])
		{
			case 'ce':
				$objAttributes = \PCT\CustomElements\Core\AttributeFactory::fetchMultipleByCustomElement($arrSource[2]);
				break;
			case 'cc':
				$objAttributes = \PCT\CustomElements\Plugins\CustomCatalog\Core\AttributeFactory::fetchAllByCustomCatalog($arrSource[2]);
				break;
			default:
				return array();
				break;
		}
		
		if($objAttributes->numRows < 1)
		{
			return array();
		}
		
		while($objAttributes->next())
		{
			$arrReturn[$objAttributes->id] = $objAttributes->title . ' [id::'.$objAttributes->id.']';
		}
		
		return $arrReturn;
	}
	
	
	/**
	 * Return all attributes as array
	 * @param object
	 * @return array
	 */
	public function getAttributes(\DataContainer $objDC)
	{
		$objCC = \Database::getInstance()->prepare("SELECT * FROM tl_pct_customelement WHERE id=(SELECT pid FROM tl_pct_customelement_group WHERE id=?)")->limit(1)->execute($objDC->activeRecord->pid);
		
		$objAttributes = \PCT\CustomElements\Core\AttributeFactory::fetchMultipleByCustomElement($objCC->id);
		if($objAttributes === null)
		{
			return array();
		}
		
		$values = array();
		if(is_array($GLOBALS['TL_DCA'][$objDC->table]['fields'][$objDC->field]['options_values']))
		{
			$values = $GLOBALS['TL_DCA'][$objDC->table]['fields'][$objDC->field]['options_values'];
		}
		
		$distinct = $GLOBALS['TL_DCA'][$objDC->table]['fields'][$objDC->field]['eval']['distinctField'];
		
		$arrReturn = array();
		$arrDistinct = array();
		while($objAttributes->next())
		{
			if(strlen($distinct) > 0)
			{
				if(in_array($objAttributes->{$distinct},$arrDistinct))
				{
					continue;
				}
				$arrDistinct[] = $objAttributes->{$distinct};
			}
			
			if(count($values) > 0)
			{
				if(in_array($objAttributes->type, $values))
				{
					$arrReturn[$objAttributes->id] = $objAttributes->title . ' ['.$objAttributes->alias.']';
					continue;
				}
			}
			else
			{
				$arrReturn[$objAttributes->id] = $objAttributes->title . ' ['.$objAttributes->alias.']';
			}
		}
		
		return $arrReturn;
	}
	
	
	/**
	 * Return all form templates as array
	 * @return array
	 */
	public function getFormTemplates()
	{
		return $this->getTemplateGroup('form_notelist');
	}
	
	
	/**
	 * Return all mail templates as array
	 * @return array
	 */
	public function getMailTemplates()
	{
		return $this->getTemplateGroup('mail_notelist');
	}
}