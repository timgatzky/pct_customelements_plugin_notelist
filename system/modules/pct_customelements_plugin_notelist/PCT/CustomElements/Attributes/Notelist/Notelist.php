<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements_plugin_notelist
 * @attribute	Notelist
 * @link		http://contao.org
 * @license     LGPL
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Attributes;

/**
 * Imports
 */
use PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;

/**
 * Class file
 * Notelist
 */
class Notelist extends \PCT\CustomElements\Core\Attribute
{
	/**
	 * Return the field definition
	 * @return array
	 */
	public function getFieldDefinition()
	{
		$arrEval = $this->getEval();

		$arrReturn = array
		(
			'label'			=> array( $this->get('title'),$this->get('description') ),
			'exclude'		=> true,
			'inputType'		=> 'checkbox',
			'default'		=> $this->get('defaultValue'),
			'eval'			=> $arrEval,
			'sql'			=> "char(1) NOT NULL default ''",
		);
		
		return $arrReturn;
	}
	

	/**
	 * Generate the attribute in the frontend
	 * @param string
	 * @param mixed
	 * @param array
	 * @param string
	 * @param object
	 * @param object
	 * @return string
	 * called renderCallback method
	 */
	public function renderCallback($strField,$varValue,$objTemplate,$objAttribute)
	{
		if(empty($varValue) || count($varValue) < 1)
		{
			return '';
		}
		
		$objNotelist = new \PCT\CustomElements\Plugins\Notelist\Notelist();
		
		$objConfig = new \StdClass();
		$objConfig->attribute = $objAttribute;
		$objConfig->template = $objAttribute->get('template');
		$objConfig->source = $objAttribute->getOrigin()->getTable() ?: 'tl_content';
		
		if(!$objAttribute->get('objActiveRecord'))
		{
			$objAttribute->set('objActiveRecord',$objAttribute->getOrigin()->get('activeRecord'));
		}
		
		return $objNotelist->addNotelistToTemplate($objTemplate,$objConfig);
	}

}
