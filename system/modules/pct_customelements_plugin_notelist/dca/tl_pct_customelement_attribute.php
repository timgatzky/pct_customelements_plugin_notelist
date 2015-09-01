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
 * Table tl_pct_customelement_attribute
 */
$objDcaHelper = PCT\CustomElements\Helper\DcaHelper::getInstance()->setTable('tl_pct_customelement_attribute');
$strType = 'notelist';

/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['settings_legend'][] = 'defaultValue';
$arrPalettes['settings_legend'][] = 'allowNotelistVariants';
$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['palettes'][$strType] = $objDcaHelper->generatePalettes($arrPalettes);

/**
 * Subpalettes
 */
$objDcaHelper->addSubpalette('allowNotelistVariants',array('notelistVariants'));

if($objDcaHelper->getActiveRecord()->type == $strType)
{
	if(\Input::get('act') == 'edit' && \Input::get('table') == $objDcaHelper->getTable())
	{
		// Show template info
		\Message::addInfo(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['templateInfo_attribute'], 'customcatalog_attr_notelist'));
	}
	
	$GLOBALS['TL_DCA'][$objDcaHelper->getTable()]['fields']['defaultValue']['inputType'] = 'select';
	$GLOBALS['TL_DCA'][$objDcaHelper->getTable()]['fields']['defaultValue']['options'] = array(0,1);
	$GLOBALS['TL_DCA'][$objDcaHelper->getTable()]['fields']['defaultValue']['eval'] = array();
	
}

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['fields']['allowNotelistVariants'] = array
(
    'label'  		=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['allowNotelistVariants'],
    'exclude'		=> true,
    'inputType'		=> 'checkbox',
    'eval'			=> array('tl_class'=>'','submitOnChange'=>true),
    'sql'			=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['fields']['notelistVariants'] = array
(
    'label'  			=> &$GLOBALS['TL_LANG']['tl_pct_customelement_attribute']['notelistVariants'],
    'exclude'			=> true,
    'inputType'			=> 'select',
    'options_callback'	=> array('PCT\CustomElements\Plugins\Notelist\TableHelper','getAttributes'),
    'options_values'	=>array('select','selectdb','tags','checkboxMenu'),
    'eval'				=> array('tl_class'=>'','chosen'=>true,'multiple'=>true),
    'sql'				=> "blob NULL"
);
