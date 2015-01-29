<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_plugin_notelist
 * @filter		Notelist
 * @link		http://contao.org
 * @license     LGPL
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Filters;

/**
 * Class file
 * Notelist
 */
class Notelist extends \PCT\CustomElements\Filter
{
	/**
	 * Prepare the sql query array for this filter and return it as array
	 * @return array
	 * 
	 * called from getQueryOption() in \PCT\CustomElements\Filter
	 */	
	public function getQueryOptionCallback()
	{
		$objNotelist = new \PCT\CustomElements\Plugins\Notelist\Notelist();
		
		$arrNotelist = $objNotelist->getNotelist($this->getTable());
		if(count($arrNotelist) < 1)
		{
			return array();
		}
		
		$arrIds = array_keys($arrNotelist);
		
		$options = array
		(
			'column'	=> 'id',
			'operation'	=> 'IN',
			'value'		=> $arrIds,
		);
		
		return $options;
	}
	
}