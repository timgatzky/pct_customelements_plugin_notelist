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


$path = 'system/modules/pct_customelements_plugin_notelist';

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'PCT\CustomElements',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'PCT\CustomElements\Attributes\Notelist'			=> $path.'/PCT/CustomElements/Attributes/Notelist/Notelist.php',
	'PCT\CustomElements\Filters\Notelist'				=> $path.'/PCT/CustomElements/Filters/Notelist/Notelist.php',
	'PCT\CustomElements\Plugins\Notelist\Hooks'			=> $path.'/PCT/CustomElements/Plugins/Notelist/Hooks.php',
	'PCT\CustomElements\Plugins\Notelist\Notelist'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Notelist.php',
	'PCT\CustomElements\Plugins\Notelist\Variants'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Variants.php',
	'PCT\CustomElements\Plugins\Notelist\Formfield'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Formfield.php',
	'PCT\CustomElements\Plugins\Notelist\TableHelper'	=> $path.'/PCT/CustomElements/Plugins/Notelist/TableHelper.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'form_notelist_customelements'				=> $path.'/templates',
	'mail_notelist_customelements'				=> $path.'/templates',
	'customelement_attr_notelist'				=> $path.'/templates',
));
