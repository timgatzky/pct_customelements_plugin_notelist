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
 * Imports
 */

use Contao\Input;
use Contao\StringUtil;
use \PCT\CustomElements\Plugins\Notelist\Hooks as Hooks;
use \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory as CustomCatalogFactory;
use \PCT\CustomElements\Core\CustomElementFactory as CustomElementFactory;


/**
 * Class file
 * Formfield
 */
class Formfield extends \Contao\Widget
{
	/**
	 * @var string
	 */
	protected $strStatusMessage = '';
	
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;
	
	/**
	 * The CSS class prefix
	 * @var string
	 */
	protected $strPrefix = 'notelist';
	
	
	/**
	 * @inherit doc
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'value':
			case 'varValue':
				return $this->render(true);
				break;
			default:
				return parent::__get($strKey);
				break;
		}
	}
	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		if(TL_MODE == 'BE')
		{
			$objTemplate = new \Contao\BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### CUSTOMELEMENTS NOTELIST ###';
			$objTemplate->id = $this->id;
			$objTemplate->title = $this->headline;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=form&amp;table=tl_form_field&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}
		
		return $this->render();
	}
	
	
	/**
	 * Parse the widget
	 */
	public function parse($blnForMail=false)
	{
		if( TL_MODE == 'BE' )
		{
			$objTemplate = new \Contao\BackendTemplate('be_wildcard');
			
			$arrSource = \explode('::', $this->customelements_notelist_source);
			$strSource = $arrSource[1];
			
			$objTemplate->wildcard = $strSource;
			#$objTemplate->id = $this->id;
			#$objTemplate->link = $this->name;
			return $objTemplate->parse();
		}	
		return $this->render($blnForMail);
	}
	
	
	/**
	 * Generate the field and return html string
	 * @param boolean
	 * @return string
	 */
	protected function render($bolFormMail=false)
	{
		if(strlen($this->customelements_notelist_source) < 1 )
		{
			return '';
		}
		
		$arrSource = explode('::', $this->customelements_notelist_source);
		$strSource = $arrSource[1];
		
		// MetaModelNotelist object, provides various helper functions
		$objNotelist = \PCT\CustomElements\Plugins\Notelist\Notelist::getInstance();
	
		//-- toggle template
		$strTemplate = ($bolFormMail == true ? $this->customelements_notelist_mailTpl : $this->customelements_notelist_formTpl);
		
		//-- create template object and add template vars
		$objTemplate = new \Contao\FrontendTemplate($strTemplate);
		$objTemplate->empty = $GLOBALS['TL_LANG']['customelements_notelist']['emptyInfo'];
		$objTemplate->entries = array();

		$arrNotelist = $objNotelist->getNotelist($strSource);
		if(empty($arrNotelist))
		{
			return $objTemplate->parse();
		}
		
		// visible fields
		$arrVisibles = StringUtil::deserialize($this->customelements_notelist_visibles);
		
		// prepare template for regular FE output
		$arrTmp = array();
		if(!$bolFormMail)
		{
			//-- submits
			$objTemplate->submit = $GLOBALS['TL_LANG']['metamodels_notelist']['submitLabel'];
			$objTemplate->remove = $GLOBALS['TL_LANG']['metamodels_notelist']['removeLabel'];
			$objTemplate->update = $GLOBALS['TL_LANG']['metamodels_notelist']['updateLabel'];
			
			$i = 0;
			foreach($arrNotelist as $item_id => $entry)
			{
				if($entry['item_id'] < 1)
				{
					continue;
				}
				
				// add classes
				$arrClass = array('item_'.$entry['id']);
				($i == 0 ? $arrClass[] = 'first' : '');
				($i >= count($arrNotelist)-1 ? $arrClass[] = 'last' : '');
				($i%2 == 0 ? $arrClass[] = 'even' : $arrClass[] = 'odd');
				
				$entry['class'] = implode(' ', $arrClass);
				
				// id base for inputs
				$strId = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$entry['source'],$entry['item_id'],$entry['attr_id']);
				
				//-- generate amount input and label and add to entry
				$arrData=array('eval'=>array('rgxp' => 'digit', 'mandatory'=>true));
				$objWidgetAmount = new \Contao\FormTextField($this->prepareForWidget($arrData, $strId.'_amount', $entry['amount'], $strId.'_amount'));	
				$entry['label_amount'] = sprintf('<label for="ctrl_%s">%s</label>',$strId.'_amount',$GLOBALS['TL_LANG']['metamodels_notelist']['amountLabel']);
				$entry['input_amount'] = $objWidgetAmount->generate();
				
				//-- generate update submit
				$objFormSubmitUpdate = new \Contao\FormSubmit();
				$objFormSubmitUpdate->id = $strId.'_update';
				$objFormSubmitUpdate->name = $strId.'_update';
				$objFormSubmitUpdate->slabel = $GLOBALS['TL_LANG']['customelements_notelist']['updateLabel'];
				$entry['input_update'] = $objFormSubmitUpdate->generate();
				
				//-- generate remove submit
				$objFormSubmitRemove = new \Contao\FormSubmit();
				$objFormSubmitRemove->id = $strId.'_remove';
				$objFormSubmitRemove->name = $strId.'_remove';
				$objFormSubmitRemove->slabel = $GLOBALS['TL_LANG']['customelements_notelist']['removeLabel'];
				$entry['input_remove'] = $objFormSubmitRemove->generate();
				
				//-- data
				$entry['fields'] = $this->prepareDataForWidget($entry,$arrVisibles);
				
				
				//-- status message
				$entry['statusMessage'] = $this->strStatusMessage;
				
				//-- variants
				if(!empty($entry['variants']) && is_array($entry['variants']))
				{
					$arrTemplateVariants = array();
		
					$objVariants = \PCT\CustomElements\Plugins\Notelist\Variants::getInstance();
					// generate variants
					foreach($entry['variants'] as $strName => $arrAttribute)
					{
						$objVariantAttr = \PCT\CustomElements\Core\AttributeFactory::findById($arrAttribute['id']);
						if(!$objVariantAttr)
						{
							continue;
						}
						
						$arrAttribute['value'] = StringUtil::deserialize($arrAttribute['value']);
						
						$strId = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$entry['source'],$entry['item_id'],$arrAttribute['id']);
						
						// generate widget
						$arrFieldDef = array
						(
							'id'	=> $strId,
							'name'	=> $strName,
							'value'	=> $arrAttribute['value'],
							'source'	=> $entry['source'],
						);
						$arrFieldDef = array_merge($objVariantAttr->getFieldDefinition(),$arrFieldDef);
						
						// set value from session
						$objVariantAttr->value = $arrAttribute['value'];
						
						$objWidget = $objVariants->loadFormField($arrFieldDef,$objVariantAttr);
						if(!$objWidget)
						{
							continue;
						}
						
						// collect variants
						$arrTemplateVariants[$strName] = array
						(
							'id'		=> $objVariantAttr->get('id'),
							'widget'	=> $objWidget,
							'value'		=> $arrAttribute['value'],
							'attribute' => $objVariantAttr,
						);
					}
					$entry['variants'] = $arrTemplateVariants;
				}
			
				// set
				$arrTmp[] = $entry;
				
				++$i;
			}
		}
		// prepare for email
		else
		{
			foreach($arrNotelist as $item_id => $entry)
			{
				if($entry['item_id'] < 1)
				{
					continue;
				}
				
				$entry['fields'] = $this->prepareDataForWidget($entry,$arrVisibles);
				
				//-- variants
				if(!empty($entry['variants']) && is_array($entry['variants']))
				{
					$arrTemplateVariants = array();
			
					$objVariants = \PCT\CustomElements\Plugins\Notelist\Variants::getInstance();
			
					// generate variants
					foreach($entry['variants'] as $strName => $arrAttribute)
					{
						$objVariantAttr = \PCT\CustomElements\Core\AttributeFactory::findById($arrAttribute['id']);
						if(!$objVariantAttr)
						{
							continue;
						}

						$arrAttribute['value'] =  StringUtil::deserialize($arrAttribute['value']);
						
						$strId = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$entry['source'],$entry['item_id'],$arrAttribute['id']);
						
						// generate widget
						$arrFieldDef = array
						(
							'id'	=> $strId,
							'name'	=> $strName,
							'value'	=> $arrAttribute['value'],
							'source'	=> $entry['source'],
						);
						$arrFieldDef = array_merge($objVariantAttr->getFieldDefinition(),$arrFieldDef);
						
						// set value from session
						$objVariantAttr->value = $arrAttribute['value'];

						$objWidget = $objVariants->loadFormField($arrFieldDef,$objVariantAttr);
						if(!$objWidget)
						{
							continue;
						}
						
						// collect variants
						$arrTemplateVariants[$strName] = array
						(
							'id'		=> $objVariantAttr->get('id'),
							'widget'	=> $objWidget,
							'value'		=> $arrAttribute['value'],
							'attribute' => $objVariantAttr,
						);

					}
					
					$entry['variants'] = $arrTemplateVariants;
				}
				
				// set
				$arrTmp[] = $entry;
			}
		}
		$arrNotelist = $arrTmp;
		unset($arrTmp);

		if( empty($arrNotelist) || !\is_array($arrNotelist) )
		{
			$arrNotelist = array();
		}
		
		$objTemplate->entries = $arrNotelist;
		$objTemplate->total = count($arrNotelist);
		
		$strBuffer = $objTemplate->parse();
		$strBuffer = $this->replaceInsertTags($strBuffer);
		
		if($bolFormMail)
		{
			$strBuffer = str_replace("\t", " ", $strBuffer);;
			#$strBuffer = $objString->decodeEntities(trim($strBuffer)); 
			$strBuffer = trim(preg_replace('/\.$/m', ' ', $strBuffer));
			#$strBuffer = trim(preg_replace('/\s\s+/', ' ', $strBuffer));
			#$strBuffer = trim(preg_replace('{(.)\1+}', '$1', $strBuffer));
		}
		
		if($GLOBALS['CUSTOMELEMENTS_NOTELIST']['clearSessionAfterSubmit'] == true && $bolFormMail)
		{
			$objNotelist->remove($strSource);
		}
		
		return $strBuffer;
	}
	
	/**
	 * Update or remove items in here
	 */
	public function validate()
	{
		$arrSource = explode('::', $this->customelements_notelist_source);
		$strSource = $arrSource[1];
		
		$objNotelist = \PCT\CustomElements\Plugins\Notelist\Notelist::getInstance();
	
		$arrNotelist = $objNotelist->getNotelist($strSource);
		if(count($arrNotelist) < 1)
		{
			return;
		}
		
		$blnReload = $GLOBALS['customelements_notelist']['autoReloadPage'];
		
		foreach($arrNotelist as $item_id => $entry)
		{
			if($entry['item_id'] < 1)
			{
				continue;
			}
			
			$strId = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$entry['source'],$entry['item_id'],$entry['attr_id']);
			//-- check for post action
			// update item
			if( isset($_POST[$strId.'_update']) )
			{
				$blnUpdate = false;
				
				$amount = Input::post($strId.'_amount');
				if($entry['amount'] != $amount)
				{
					$blnUpdate = true;
				}
				
				if(count($entry['variants']) > 0)
				{
					foreach($entry['variants'] as $strName => $arrAttribute)
					{
						if(Input::post($strName) && $arrAttribute['value'] != Input::post($strName) )
						{
							$entry['variants'][$strName]['value'] = Input::post($strName);
							$blnUpdate = true;
						}
					}
				}
				
				// create a psydo amount input field to valide input
				$arrData=array('eval'=>array('rgxp' => 'digit', 'mandatory'=>true));
				$objAmountWidget = new \Contao\FormTextField($this->prepareForWidget($arrData, $strId.'_amount', $amount, $strId.'_amount'));
				$objAmountWidget->validate();
				if($objAmountWidget->hasErrors())
				{
					$this->class = 'error';
					$this->addError($GLOBALS['TL_LANG']['ERR']['digit']);
				}
				else
				{	
					if($blnUpdate)
					{
						// toggle status message
						if(strlen($_POST[$strId.'_update']) > 0)	
							{$this->strStatusMessage = $GLOBALS['TL_LANG']['customelements_notelist']['itemUpdated'];}
						else 
							{$this->strStatusMessage = $GLOBALS['TL_LANG']['customelements_notelist']['itemAdded'];}
						
						// set the notelist
						$objNotelist->setItem($entry['source'],$entry['item_id'],$amount,$entry['variants'],$blnReload,$entry);
					}
				}
				
				// avoid sending the form when page is not being reloaded after an update
				$this->addError('');
				
			}
			// remove item
			else if( isset($_POST[$strId.'_remove']) )
			{
				// remove item and reload
				$objNotelist->removeItem($entry['source'],$entry['item_id']);
			}
			else{}
		}
		
		return $this->render(true);
	}

	
	/**
	 * Fetch the data from a metamodel and return a prepared array
	 * @param integer
	 * @param integer
	 * @param array		/ visible fields
	 * @return array
	 */
	public function prepareDataForWidget($arrEntry,$arrVisibles=array())
	{
		if(count($arrVisibles) < 1)
		{
			return array();
		}
		
		$arrSource = explode('::', $this->customelements_notelist_source);
		
		$objVisibles = \PCT\CustomElements\Core\AttributeFactory::fetchMultipleById($arrVisibles,array('order'=>'FIELD(id,'.implode(',', $arrVisibles).')'));
		if($objVisibles === null)
		{
			return array();
		}
		
		$objRow = \Contao\Database::getInstance()->prepare("SELECT * FROM ".$arrEntry['source']." WHERE id=?")->limit(1)->execute($arrEntry['item_id']);
		if($objRow->numRows < 1)
		{
			return array();
		}
		
		$objOrigin = new \PCT\CustomElements\Core\Origin();
		$objOrigin->set('strTable',$arrEntry['source']);
		$objOrigin->set('intPid',$arrEntry['item_id']);
		
		$bolIsCustomElement = $arrSource[0] == 'ce' ? true : false;
		
		$arrReturn = array();
		while($objVisibles->next())
		{
			$objAttribute = \PCT\CustomElements\Core\AttributeFactory::findById($objVisibles->id);
			// skip deprected selected attributes
			if(!$objAttribute)
			{
				continue;
			}
			$objAttribute = $objAttribute->generate();
			$objAttribute->setOrigin($objOrigin);
			$objAttribute->set('objActiveRecord',$objRow);
			
			$strName = $bolIsCustomElement ? $objAttribute->get('uuid') : $objAttribute->get('alias');
			
			if(!$bolIsCustomElement)
			{
				$varValue = $objRow->{$strName};
				$objAttribute->setValue($varValue);
			}
			
			$objTplAttribute = new \PCT\CustomElements\Core\TemplateAttribute($objAttribute);
			$objTplAttribute->label = $objAttribute->get('title') ?: $objAttribute->get('alias') ?: $objAttribute->get('id');
			$objTplAttribute->name = $strName;
			
			$arrReturn[$strName] = $objTplAttribute;
		}
		
		return $arrReturn;
	}

	
}
