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
use Contao\System;
use \PCT\CustomElements\Plugins\Notelist\Hooks as Hooks;


/**
 * Class file
 * Notelist
 */
class Notelist extends \Contao\Controller
{
	/**
	 * Session node
	 * @var string
	 */
	protected $strSession	= 'customelementnotelist';
	
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
			self::$objInstance = new static();
		}
		return self::$objInstance;
	}
	
	
	/**
	 * Init
	 */
	public function __construct()
	{
		if(strlen($GLOBALS['CUSTOMELEMENTS_NOTELIST']['sessionName']) > 0 && $GLOBALS['CUSTOMELEMENTS_NOTELIST']['sessionName'] != $this->strSession)
		{
			$this->strSession = $GLOBALS['CUSTOMELEMENTS_NOTELIST']['sessionName'];
		}
	}
		
	
	/**
	 * Insert/update an item in the notelist
	 * @param integer
	 * @param integer
	 * @param integer
	 * @param array
	 * @param boolean
	 */
	public function setItem($varSource,$intItem,$intAmount=0,$arrVariants=array(),$blnReload=true,$arrEntry=array())
	{
		// get Session
		$objSession = System::getContainer()->get('session');
		$arrSession = $objSession->get($this->strSession);
		
		$time = time();
		
		$arrSession[$varSource][$intItem] = array
		(
			'tstamp'	=> $time,
			'source'	=> $varSource,
			'item_id'	=> $intItem,
			'attr_id'	=> $arrEntry['attr_id'],
			'amount'	=> ($intAmount < 0 || !$intAmount ? 0 : $intAmount),
			'variants'	=> $arrVariants,
		);
		
		// HOOK allow other extensions to manipulate the session
		$arrSession =  Hooks::getInstance()->callSetItemHook($arrSession,$varSource,$intItem,$intAmount,$arrVariants);
		
		// set Session
		$objSession->set($this->strSession,$arrSession);
		
		if($blnReload)
		{
			// reload the page to see changes	
			$this->reload();
		}
	}
	
	
	/**
	 * Get an item from the notelist and return as array
	 * @param integer
	 * @param integer
	 * @return array
	 */
	public function getItem($varSource,$intItem)
	{
		// get Session
		$arrSession = System::getContainer()->get('session')->get($this->strSession);
		return $arrSession[$varSource][$intItem];
	}
	
	
	/**
	 * Remove an item from notelist
	 * @param integer
	 * @param integer
	 * @param boolean
	 */
	public function removeItem($varSource,$intItem,$blnReload=true)
	{
		// get Session
		$objSession = System::getContainer()->get('session');
		$arrSession = $objSession->get($this->strSession);
		
		unset($arrSession[$varSource][$intItem]);
		
		// HOOK tell other extensions an item has been removed
		Hooks::getInstance()->callRemoveItemHook($arrSession,$varSource,$intItem);
		
		// set Session
		$objSession->set($this->strSession,$arrSession);
		
		if($blnReload)
		{
			// reload the page to see changes	
			$this->reload();
		}
	}
	
		
	/**
	 * Get all items from current notelist and return as array
	 * @param integer
	 * @return array
	 */
	public function getNotelist($varSource=null)
	{
		// Session
		$objSession = System::getContainer()->get('session');
		$arrSession = $objSession->get($this->strSession);
		
		if( !isset($arrSession[$varSource]) || !is_array($arrSession[$varSource]) || empty($arrSession[$varSource]))
		{
			return array();
		}
		
		return $arrSession[$varSource];
	}
	
	
	/**
	 * Return the whole notelist session
	 * @return array
	 */
	public function getNotelists()
	{
		// Session
		$objSession = System::getContainer()->get('session');
		$arrSession = $objSession->get($this->strSession);
		if(!is_array($arrSession))
		{
			return array();
		}
		return $arrSession;
	}

	
	/**
	 * Returns true if an element is already in the notelist
	 * @param integer
	 * @param integer
	 * @return boolean
	 */
	public function isInNotelist($varSource, $intItem)
	{
		$arrNotelist = $this->getNotelist($varSource);
		
		if(isset($arrNotelist[$intItem]) && !empty($arrNotelist[$intItem]))
		{
			return true;
		}
				
		return false;
	}
	
	
	/**
	 * Replace Inserttags
	 * @param string
	 * @return string or boolean
	 */
	public function replaceTags($strTag)
	{
		$strValue = '';
		$element = explode('::', $strTag);

		switch($element[0])
		{
			case 'form':
				if(isset($_POST[$element[1]]))
				{
					return false;
				}
				
				$strValue = \Contao\Input::post($element[1]);
				
				if(is_array($strValue))
				{
					$strValue = serialize($strValue);
				}
				
				return $strValue;
			break;
			case 'customcatalognotelist':
				$objNotelist = new \PCT\CustomElements\Plugins\Notelist\Notelist();
				switch($element[1])
				{
					case 'total':
					case 'count':
						return count($objNotelist->getNotelist($element[2]));
						break;
					case 'values':
						$arrNotelist = $objNotelist->getNotelist($element[2]);
						
						if( empty($arrNotelist) === true )
						{
							return '';
						}

						$objDatabase = \Contao\Database::getInstance();

						$strField = $element[3];
						if( $objDatabase->fieldExists($strField,$element[2]) === false )
						{
							\Contao\System::log('Field '.$strField.' does not exist in table '.$element[2],__METHOD__,\TL_ERROR);
							return '';
						}
						
						// collect items
						$arrIds = array();
						foreach($arrNotelist as $data)
						{
							$arrIds[] = $data['item_id'];
						}

						$arrIds = \array_filter( \array_unique($arrIds) );
						if( empty($arrIds) === true )
						{
							return '';
						}
						
						// fetch entries
						$objResult = $objDatabase->prepare("SELECT * FROM ".$element[2]." WHERE id IN(".\implode(',',$arrIds).")")->execute();
						if($objResult->numRows < 1)
						{
							return '';
						}
						
						return \implode(',', \array_unique( \array_filter($objResult->fetchEach( $strField )) ));
					
						break;
				}
				break;
			
			default: return false; break;
		}

		return false;
	}

	
	/**
	 * Add the notelist form to a template
	 *
	 */
	public function addNotelistToTemplate($objTemplate, $objConfig)
	{
		$blnReload = $GLOBALS['customelements_notelist']['autoReloadPage'];
		
		$objSession = System::getContainer()->get('session');;
		
		$strSource = $objConfig->source;
		
		// attribute object
		$objAttr = $objConfig->attribute;
		
		// record
		$arrRow = $objAttr->get('objActiveRecord')->row();
		
		#$objNotelistTemplate = new \Contao\FrontendTemplate($objConfig->template);
		$objTemplate->includeNotelist = true;
		
		$strFormID = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$strSource,$arrRow['id'],$objAttr->get('id'));
		
		$objTemplate->action = $this->replaceInsertTags('{{env::request}}');
		$objTemplate->formID = $strFormID;
		$objTemplate->itemID = $arrRow['id'];
		$objTemplate->source = $strSource;
		$objTemplate->variants = array();
		
		//-- submits
		$objTemplate->submit = $GLOBALS['TL_LANG']['customelements_notelist']['submitLabel'];
		$objTemplate->submitName = $strFormID.'_add'; #'ADD_NOTELIST_ITEM';
		$objTemplate->update = $GLOBALS['TL_LANG']['customelements_notelist']['updateLabel'];
		$objTemplate->updateName = $strFormID.'_update'; #'UPDATE_NOTELIST_ITEM';
		$objTemplate->remove = $GLOBALS['TL_LANG']['customelements_notelist']['removeLabel'];
		$objTemplate->removeName = $strFormID.'_remove'; #'REMOVE_NOTELIST_ITEM';
		// get item from notelist and set amount value
		$arrItem = $this->getItem($strSource,$arrRow['id']);
		$amount = ($arrItem['amount'] ? $arrItem['amount'] : $GLOBALS['customelements_notelist']['default_amount']);
		// create amount widget
		$arrData=array('eval'=>array('rgxp' => 'digit', 'mandatory'=>true));
		$objWidgetAmount = new \Contao\FormTextField($this->prepareForWidget($arrData, $strFormID.'_amount', $amount, $strFormID.'_amount'));	
		
		$objTemplate->amountInput = $objWidgetAmount->generate();
		$objTemplate->amountLabel = sprintf('<label for="ctrl_%s">%s</label>',$objWidgetAmount->id,$GLOBALS['TL_LANG']['customelements_notelist']['amountLabel']);
		
		//-- variants
		$arrVariants = array();
		if($objAttr->get('allowNotelistVariants') > 0)
		{
			$arrTemplateVariants = array();
			
			$arrNotelistVariants = StringUtil::deserialize($objAttr->get('notelistVariants')) ?: array();
			if(!is_array($arrNotelistVariants))
			{
				$arrNotelistVariants = array($arrNotelistVariants);
			}

			foreach($arrNotelistVariants as $intVariantAttrId)
			{
				$objVariantAttr = \PCT\CustomElements\Core\AttributeFactory::findById($intVariantAttrId);
				if(!$objVariantAttr)
				{
					continue;
				}
				$objVariantAttr->generate();
				$objVariantAttr->set('objActiveRecord',$objAttr->get('objActiveRecord'));
				$objVariantAttr->set('objOrigin',$objAttr->get('objOrigin'));
				
				$strName = sprintf($GLOBALS['customelements_notelist']['formfieldLogic'],$strSource,$arrRow['id'],$objVariantAttr->get('alias'));				
				
				// generate widget
				$arrFieldDef = array
				(
					'id'		=> $strSource.'_'.$arrRow['id'].'_'.$objVariantAttr->get('id'),
					'attr_id'	=> $objVariantAttr->get('id'),
					'item_id'	=> $arrRow['id'],
					'name'		=> $strName,
					'value'		=> $arrItem['variants'][$strName]['value'],
					'source'	=> $strSource,
				);
				
				$arrFieldDef = array_merge($objVariantAttr->getFieldDefinition(),$arrFieldDef);
				
				$objWidget = \PCT\CustomElements\Plugins\Notelist\Variants::getInstance()->loadFormField($arrFieldDef,$objVariantAttr);
				
				if(!$objWidget)
				{
					continue;
				}
				
				// collect variants
				$arrTemplateVariants[$strName] = array
				(
					'id'	=> $objVariantAttr->get('id'),
					'html'	=> $objWidget->generate(),
					'raw'	=> $objWidget,
					'attribute' => $objVariantAttr,
				);
				
				// check if a variant field is submitted and store in array
				if( Input::post('FORM_SUBMIT') == $strFormID && Input::post($objWidget->name) )
				{
					$arrVariants[$strName] = array
					(
						'id'		=> $objVariantAttr->get('id'),
						'name' 		=> $objVariantAttr->get('alias'),
						'value'		=> Input::post($objWidget->name),
					);
				}
			}
			
			// add variants fields to template
			$objTemplate->variants = $arrTemplateVariants;
		}
		
		//-- form submits
		if( Input::post('FORM_SUBMIT') == $strFormID )
		{
			$intAmount = Input::post($strFormID.'_amount');
			$intItem = Input::post('ITEM_ID');
			$strSource = Input::post('SOURCE');
			
			// insert or update an item
			if( strlen(Input::post($objTemplate->submitName)) > 0 || strlen(Input::post($objTemplate->updateName)) > 0 )
			{
				// validate amount
				$objWidgetAmount->validate();
				if($objWidgetAmount->hasErrors())
				{
					$objTemplate->statusMessage = $objWidgetAmount->getErrorAsString(0);
				}
				else
				{
					// toggle status message
					if(strlen(Input::post($objTemplate->updateName)) > 0)	
					{
						$objTemplate->statusMessage = $GLOBALS['TL_LANG']['customelements_notelist']['itemUpdated'];
						
						// reload if variants where updated
						#if(count($arrVariants) > 0)
						#{
						#	$blnReload = true;
						#}
					}
					else 
					{
						$objTemplate->statusMessage = $GLOBALS['TL_LANG']['customelements_notelist']['itemAdded'];
					}
					
					// remember item
					$objSession->set('customcatalognotelist_added',$strFormID);
					
					// set the notelist
					$this->setItem($strSource,$intItem,$intAmount,$arrVariants,$blnReload,array('attr_id'=>$objAttr->get('id')));
				}
			}
			// remove an item and reload the page immediately
			else if(strlen($objTemplate->removeName) > 0)
			{
				// remember item
				$objSession->set('customcatalognotelist_removed',$strFormID);
				
				$this->removeItem($strSource,$intItem);
			}
			else {}
		}
				
		// mark item as being added
		if($arrItem['amount'])
		{
			$objTemplate->added = true;
		}
		
		// set focus flag when added
		if($objSession->get('customcatalognotelist_added') == $strFormID)
		{
			$objTemplate->focus = true;
			$objTemplate->focusAdded = true;
			// remove flag
			$objSession->remove('customcatalognotelist_added');
		}
		
		if($objSession->get('customcatalognotelist_removed') == $strFormID)
		{
			$objTemplate->focus = true;
			$objTemplate->focusRemove = true;
			// remove flag
			$objSession->remove('customcatalognotelist_removed');
		}
		
		return $objTemplate->parse();
	}
	
	
	/**
	 * Clear a notelist
	 * @param string
	 */
	public function remove($strSource)
	{
		// Session
		$objSession = System::getContainer()->get('session');;
		$arrSession = $objSession->get($this->strSession);
		if(!is_array($arrSession[$strSource]))
		{
			return true;
		}
		
		unset($arrSession[$strSource]);
		
		$objSession->set($this->strSession,$arrSession);
		
		return true;
	}
	
	
	/**
	 * Create a history of visited entries
	 * @param object
	 * @param string
	 */
	public function createHistory($objRow, $strBuffer)
	{
		$varEntry = \Contao\Input::get( (\Contao\Config::get('useAutoItem') === true ? 'auto_item' : $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']),false,true );
		if(strlen($varEntry) < 1)
		{
			return $strBuffer;
		}
		
		$objModel = clone($objRow);
		$strTable = '';
		
		// is frontend module in pagelayout
		if( in_array($objModel->type, array('customcatalogreader','customcataloglist')) && strlen($objModel->customcatalog) > 0)
		{
			$strTable = $objModel->customcatalog;
		}
		else if($objModel->type == 'module' && $objModel->module > 0)
		{
			$objModuleModel = \Contao\ModuleModel::findByPk($objModel->module);
			if($objModuleModel !== null)
			{
				return $this->createHistory($objModuleModel, $strBuffer);
			}
		}
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if($objCC === null || strlen($strTable) < 1)
		{
			return $strBuffer;
		}
		
		$strLanguage = '';
		if( $objCC->get('multilanguage') && ($objModel->customcatalog_filter_actLang || $objCC->get('aliasField') > 0) )
		{
			$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
			$strLanguage = $objMultilanguage->getActiveFrontendLanguage();
		}
		
		// render the regular details page of a customcatalog entry
		$objEntry = $objCC->findPublishedItemByIdOrAlias(\Contao\Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']),$strLanguage);
		
		$time = time();
		$objSession = System::getContainer()->get('session');;
		
		// get the current session
		$arrSession = $objSession->get('customelementnotelist_history');
		if(!is_array($arrSession))
		{
			$arrSession = array();
			$arrSession['createTime'] = $time;
			$arrSession['tables'] = array();
		}
		
		// check if user visited a new entry or remains on the last one visited
		if($arrSession['lastTableVisited'] != $strTable || $arrSession['lastItemVisited'] != $objEntry->id)
		{
			// add new entry
			$arrSession['tables'][$strTable][] = $objEntry->id;
			$arrSession['tstamp'] = $time;
		}
		
		// store information
		$arrSession['lastUrl'] = \Contao\Environment::get('request');
		$arrSession['lastTableVisited'] = $strTable;
		$arrSession['lastItemVisited'] = $objEntry->id;
		
		// update session
		$objSession->set('customelementnotelist_history',$arrSession);
		
		return $strBuffer;
	}
	
	
	
}