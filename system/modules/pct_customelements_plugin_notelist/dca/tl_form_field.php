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
 * Table tl_pct_customelement_filter
 */
$objDcaHelper = \PCT\CustomElements\Helper\DcaHelper::getInstance()->setTable('tl_form_field');
$strType = 'customelements_notelist';


/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['type_legend'][] = 'name';
$arrPalettes['type_legend'][] = 'label';
$arrPalettes['settings_legend'] = array('customelements_notelist_source','customelements_notelist_visibles');
$arrPalettes['template_legend'] = array('customelements_notelist_formTpl','customelements_notelist_mailTpl');
$GLOBALS['TL_DCA']['tl_form_field']['palettes'][$strType] = $objDcaHelper->generatePalettes($arrPalettes);


/**
 * Subpalettes
 */
$objDcaHelper->addSubpalette('customelements_notelist_source',array('customelements_notelist_visibles'));


/**
 * Fields
 */
$objDcaHelper->addFields(array(
	'customelements_notelist_source' => array
	(
		'label'					  => &$GLOBALS['TL_LANG']['tl_form_field']['customelements_notelist_source'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('\PCT\CustomElements\Plugins\Notelist\TableHelper', 'getSources'),
		'eval'                    => array('includeBlankOption'=>true,'mandatory'=>true,'submitOnChange'=>true,'chosen'=>true),
		'sql'					  => "varchar(64) NOT NULL default ''",
	),
	'customelements_notelist_visibles' => array
	(
		'label'					  => &$GLOBALS['TL_LANG']['tl_form_field']['customelements_notelist_visibles'],
		'exclude'                 => true,
		'inputType'               => 'checkboxWizard',
		'options_callback'        => array('\PCT\CustomElements\Plugins\Notelist\TableHelper', 'getAttributesBySelection'),
		'eval'                    => array('multiple'=>true),
		'sql'					  => "blob NULL",
	),
	'customelements_notelist_formTpl' => array
	(
		'label'					  => &$GLOBALS['TL_LANG']['tl_form_field']['customelements_notelist_formTpl'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('\PCT\CustomElements\Plugins\Notelist\TableHelper', 'getFormTemplates'),
		'eval'                    => array('tl_class'=>'w50'),
		'sql'					  => "varchar(64) NOT NULL default ''",
	),
	'customelements_notelist_mailTpl' => array
	(
		'label'					  => &$GLOBALS['TL_LANG']['tl_form_field']['customelements_notelist_mailTpl'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('\PCT\CustomElements\Plugins\Notelist\TableHelper', 'getMailTemplates'),
		'eval'                    => array('tl_class'=>'w50'),
		'sql'					  => "varchar(64) NOT NULL default ''",
	),
));
