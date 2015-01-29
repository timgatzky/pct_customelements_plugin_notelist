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
$objDcaHelper = \PCT\CustomElements\Helper\DcaHelper::getInstance()->setTable('tl_pct_customelement_filter');
$strType = 'notelist';

/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$objDcaHelper->removeField('urlparam');
$objDcaHelper->removePalette('template_legend');
$GLOBALS['TL_DCA']['tl_pct_customelement_filter']['palettes'][$strType] = $objDcaHelper->generatePalettes();