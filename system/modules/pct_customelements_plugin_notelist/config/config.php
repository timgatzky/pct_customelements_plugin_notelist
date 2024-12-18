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

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\System;

/**
 * Constants
 */ 
if( \defined('PCT_CUSTOMELEMENTS_NOTELIST_VERSION') === false )
{
	define('PCT_CUSTOMELEMENTS_NOTELIST_VERSION','2.0.1');
	define('PCT_CUSTOMELEMENTS_NOTELIST_PATH','system/modules/pct_customelements_plugin_notelist');	
}

if( version_compare(ContaoCoreBundle::getVersion(),'5.0','>=') )
{
	$rootDir = System::getContainer()->getParameter('kernel.project_dir');
	include( $rootDir.'/system/modules/pct_customelements_plugin_notelist/config/autoload.php' );
}

/**
 * Globals
 */
$GLOBALS['customelements_notelist']['default_amount']	= 1;
$GLOBALS['customelements_notelist']['autoReloadPage']	= true; // reload the page when amount is being updated or an item is placed on the notelist
$GLOBALS['customelements_notelist']['formfieldLogic'] 	= 'customelement_notelist_%s_%s_%s'; // (SOURCE)_(ENTRY-ID)_(ATTRIBUTE-ID) e.g. customelement_notelist_tl_content_1_2
$GLOBALS['CUSTOMELEMENTS_NOTELIST']['sessionName'] 		= 'customelementnotelist';
$GLOBALS['CUSTOMELEMENTS_NOTELIST']['clearSessionAfterSubmit'] = false; // set to true if you want to clear the submitted notelist after submitting


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
	'path' 		=> PCT_CUSTOMELEMENTS_NOTELIST_PATH,
	'class'		=> 'PCT\CustomElements\Filters\Notelist',
	'icon'		=> 'fa fa-check-square'
);

$GLOBALS['PCT_CUSTOMELEMENTS']['FILTERS']['history'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['PCT_CUSTOMELEMENTS']['FILTERS']['history'],
	'path' 		=> PCT_CUSTOMELEMENTS_NOTELIST_PATH,
	'class'		=> 'PCT\CustomElements\Filters\History',
	'icon'		=> 'fa fa-history'
);

/**
 * Form fields
 */
$GLOBALS['TL_FFL']['customelements_notelist'] = 'PCT\CustomElements\Plugins\Notelist\Formfield';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] 		= array('\PCT\CustomElements\Plugins\Notelist\Notelist', 'replaceTags'); 
$GLOBALS['TL_HOOKS']['getFrontendModule'][] 		= array('\PCT\CustomElements\Plugins\Notelist\Notelist', 'createHistory');
$GLOBALS['TL_HOOKS']['getContentElement'][] 		= array('\PCT\CustomElements\Plugins\Notelist\Notelist', 'createHistory');


