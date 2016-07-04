<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2016
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_plugin_notelist
 * @filter		History
 * @link		http://contao.org
 * @license     LGPL
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Filters;

/**
 * Class file
 * History
 */
class History extends \PCT\CustomElements\Filter
{
	/**
	 * Prepare the sql query array for this filter and return it as array
	 * @return array
	 * 
	 * called from getQueryOption() in \PCT\CustomElements\Filter
	 */	
	public function getQueryOptionCallback()
	{
		$arrSession = \Session::getInstance()->get('customelementnotelist_history');
		
		$objModule = $this->getCustomCatalog()->getOrigin();
		$strTable = $this->getTable();
				
		$arrIds = array();
		
		// filter is empty
		if(count($arrSession['tables'][$strTable]) < 1 || !is_array($arrSession['tables'][$strTable]))
		{
			if($objModule->customcatalog_filter_showAll)
			{
				return array();
			}
			else
			{
				$arrIds = array(-1);
			}
		}
		else
		{
			$values = array_reverse( array_unique($arrSession['tables'][$strTable]) );
			
			$i = 0;
			
			// reduce to limit
			if($objModule->customcatalog_limit > 0 && count($values) > 0)
			{
				foreach($values as $id)
				{
					if($i < $objModule->customcatalog_limit)
					{
						$arrIds[] = $id;
					}
					$i++;
				}
			}
			else
			{
				$arrIds = $values;
			}
		}
		
		$options = array
		(
			'column'	=> 'id',
			'operation'	=> 'IN',
			'value'		=> $arrIds,
		);
		
		return $options;
	}
	
}