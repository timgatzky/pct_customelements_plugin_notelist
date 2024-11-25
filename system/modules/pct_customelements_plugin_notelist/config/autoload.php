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

// path relative from composer directory
$path = \Contao\System::getContainer()->getParameter('kernel.project_dir').'/vendor/composer/../../system/modules/pct_customelements_plugin_notelist';

/**
 * Register the classes
 */
$classMap = array
(
	'PCT\CustomElements\Attributes\Notelist'			=> $path.'/PCT/CustomElements/Attributes/Notelist/Notelist.php',
	'PCT\CustomElements\Filters\Notelist'				=> $path.'/PCT/CustomElements/Filters/Notelist/Notelist.php',
	'PCT\CustomElements\Filters\History'				=> $path.'/PCT/CustomElements/Filters/History/History.php',
	'PCT\CustomElements\Plugins\Notelist\Hooks'			=> $path.'/PCT/CustomElements/Plugins/Notelist/Hooks.php',
	'PCT\CustomElements\Plugins\Notelist\Notelist'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Notelist.php',
	'PCT\CustomElements\Plugins\Notelist\Variants'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Variants.php',
	'PCT\CustomElements\Plugins\Notelist\Formfield'		=> $path.'/PCT/CustomElements/Plugins/Notelist/Formfield.php',
	'PCT\CustomElements\Plugins\Notelist\TableHelper'	=> $path.'/PCT/CustomElements/Plugins/Notelist/TableHelper.php',
);

$loader = new \Composer\Autoload\ClassLoader();
$loader->addClassMap($classMap);
$loader->register();


/**
 * Register the templates
 */
\Contao\TemplateLoader::addFiles(array
(
	'form_notelist_customelements'				=> 'system/modules/pct_customelements_plugin_notelist/templates',
	'mail_notelist_customelements'				=> 'system/modules/pct_customelements_plugin_notelist/templates',
	'customelement_attr_notelist'				=> 'system/modules/pct_customelements_plugin_notelist/templates',
));
