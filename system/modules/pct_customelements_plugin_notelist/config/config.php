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
 * Constants
 */ 
define(PCT_CUSTOMELEMENTS_NOTELIST_PATH,'system/modules/pct_customelements_plugin_notelist');
define(PCT_CUSTOMELEMENTS_NOTELIST_VERSION,'1.0.1');

/**
 * Register plugin
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['PLUGINS']['notelist'] = array
(
	'tables' 	=> array('tl_pct_customelement','tl_pct_customelement_group','tl_pct_customelement_attribute'),
	'requires'	=> array('pct_customelements'=>'1.2.6'),
);


/**
 * Stop here if CE Version is to low or notelist is not active
 */
if(strlen(strpos(\Environment::getInstance()->scriptName, '/contao/install.php')) < 1)
{
	$objPluginFactory = new \PCT\CustomElements\Core\PluginFactory();
	if(!in_array('notelist',$objPluginFactory::getActivePlugins()) && \Input::get('do') != 'repository_manager' )
	{
		return;
	}
}

/**
 * Globals
 */
$GLOBALS['customelements_notelist']['default_amount']	= 1;
$GLOBALS['customelements_notelist']['autoReloadPage']	= true; // reload the page when amount is being updated or an item is placed on the notelist
$GLOBALS['customelements_notelist']['formfieldLogic'] 	= 'customelement_notelist_%s_%s_%s'; // (SOURCE)_(ENTRY-ID)_(ATTRIBUTE-ID) e.g. customelement_notelist_tl_content_1_2

/**
 * Register attribute
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['ATTRIBUTES']['notelist'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['PCT_CUSTOMELEMENTS']['ATTRIBUTES']['notelist'],
	'path' 		=> PCT_CUSTOMELEMENTS_NOTELIST_PATH,
	'class'		=> 'PCT\CustomElements\Attributes\Notelist',
	'icon'		=> 'fa fa-check-square-o'
);

/**
 * Register filter
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['FILTERS']['notelist'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['PCT_CUSTOMELEMENTS']['FILTERS']['notelist'],
	'path' 		=> PCT_CUSTOMELEMENTS_TAGS_PATH,
	'class'		=> 'PCT\CustomElements\Filters\Notelist',
	'icon'		=> 'fa fa-check-square'
);

/**
 * Form fields
 */
array_insert($GLOBALS['TL_FFL'],count($GLOBALS['TL_FFL']),array
(
	'customelements_notelist'	=> 'PCT\CustomElements\Plugins\Notelist\Formfield'
));


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] 		= array('\PCT\CustomElements\Plugins\Notelist\Notelist', 'replaceTags'); 


