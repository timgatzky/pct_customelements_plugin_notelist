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
 * Hooks
 */
class Hooks
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
			self::$objInstance = new self();
		}
		return self::$objInstance;
	}
	

	/**
	 * SetItem Hook
	 * Called when an Item is deleted from the notelist
	 * @param array		new Session array after removing
	 * @param integer	id of metamodel
	 * @param integer	id of metamodel item
	 * @param integer	amount
	 * @param array		item variants
	 * @return array	session array to be saved
	 */
	public function callSetItemHook($arrSession,$strSource,$intItem,$intAmount,$arrVariants)
	{
		if (isset($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['addItem']) && count($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['addItem']) > 0)
		{
			foreach($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['addItem'] as $callback)
			{
				$arrSession = \System::importStatic($callback[0])->{$callback[1]}($arrSession,$strSource,$intItem,$intAmount,$arrVariants);
			}
		}
		
		return $arrSession;
	}
	
	/**
	 * RemoveItem Hook
	 * Called when an Item is deleted from the notelist
	 * @param array		new Session array after removing
	 * @param integer	id of metamodel
	 * @param integer	id of metamodel item
	 */
	public function callRemoveItemHook($arrSession,$strSource,$intItem)
	{
		if (isset($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['removeItem']) && count($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['removeItem']) > 0)
		{
			foreach($GLOBALS['CUSTOMELEMENTNOTELIST_HOOKS']['removeItem'] as $callback)
			{
				\System::importStatic($callback[0])->{$callback[1]}($arrSession,$strSource,$intItem);
			}
		}
	}
	
}