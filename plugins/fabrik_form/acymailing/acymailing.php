<?php
/**
 * Add a user to an Acymailing mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.acymailing
 * @copyright   Copyright (C) 2005-2016  Better Web - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Date\Date;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Add a user to an Acymailing mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.acymailing
 * @since       3.8
 */

class PlgFabrik_FormAcymailing extends PlgFabrik_Form
{
	protected $html = null;

	/**
	 * Set up the html to be injected into the bottom of the form
	 *
	 * @return  void
	 */

	public function getBottomContent()
	{
		$params = $this->getParams();
		$formModel = $this->getModel();

		if ($params->get('acymailing_userconfirm', true))
		{
			$layout = $this->getLayout('form');
			$layoutData = new stdClass;

			$errors = $formModel->getErrors();

			if (array_key_exists('consent_required', $errors))
			{
				$layoutData->errClass = '';
			}
			else
			{
				$layoutData->errClass = 'fabrikHide';
			}
			
			$layoutData->errText 	   = FText::_('PLG_FORM_ACYMAILING_PLEASE_CONFIRM_CONSENT');
			$layoutData->showConsent   = $params->get('acymailing_userconfirm', '0') === '1';
			$layoutData->useFieldset   = $params->get('acymailing_fieldset', '0') === '1';
			$layoutData->fieldsetClass = $params->get('acymailing_fieldset_class', '');
			$layoutData->legendClass   = $params->get('acymailing_legend_class', '');
			$layoutData->legendText    = FText::_($params->get('acymailing_legend', ''));
			$layoutData->consentText   = FText::_($params->get('acymailing_intro_terms'));
			$layoutData->mailingText   = FText::_($params->get('acymailing_signuplabel', ''));
			$this->html 			   = $layout->render($layoutData);
		}
		else
		{
			$this->html = '';
		}
			
		$opts 			   = new \StdClass();
		$opts->renderOrder = $this->renderOrder;
		$opts->formid  	   = $formModel->getId();
		$opts 			   = json_encode($opts);

		$this->formJavascriptClass($params, $formModel);
		$formModel->formPluginJS['Acymailing' . $this->renderOrder] = 'var acymailing = new Acymailing(' . $opts . ');';
	}

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  Plugin counter
	 *
	 * @return  string  html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}
	
	/**
	 * Run right before the form processing
	 * keeps the data to be processed or sent if consent is not given
	 *
	 * @return	bool
	 */
	
	//public function onBeforeProcess()
	//{
	//	$formModel = $this->getModel();
	//	
	//	if(!array_key_exists('fabrik_contact_consent', $formModel->formData))
	//	{
	//		$formModel->errors['consent_required'] = array(FText::_('PLG_FORM_CONSENT_PLEASE_CONFIRM_CONSENT'));
	//		$formModel->formErrorMsg = FText::_('PLG_FORM_CONSENT_PLEASE_CONFIRM_CONSENT');
	//		return false;
	//	}
	// }

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return	bool
	 */

	public function onAfterProcess()
	{
		$params    = $this->getParams();
		$formModel = $this->getModel();
		$emailData = $this->getProcessData();
		$filter    = JFilterInput::getInstance();
		$post      = $filter->clean($_POST, 'array');
		$subscribe = array_key_exists('fabrik_acymailing_consent', $post);
		$confirm   = $params->get('acymailing_userconfirm', '0') === '1';
		
		if ($formModel->isNewRecord() && $confirm && !$subscribe)
		{
			return;
		}
		
		if($this->checkAcymailing())
		{
			$myUser = new stdClass();
	
			$emailField	= $params->get('acymailing_email');			    
			if(!$emailField)
			{
				throw new RuntimeException(FText::_('PLG_FORM_ACYMAILING_NO_EMAIL_ERROR_MSG'));
				return false;
			}
			else
			{
				$emailKey 	   = $formModel->getElement($emailField, true)->getFullName();
				$myUser->email = $formModel->formDataWithTableName[$emailKey];
			}
			
			$nameField = $params->get('acymailing_name');
			if($nameField)
			{
				$nameKey 	  = $formModel->getElement($nameField, true)->getFullName();
				$myUser->name = $formModel->formDataWithTableName[$nameKey];
			}
			else
			{
				$myUser->name ='';
			}
			
			$lists = explode(',', $params->get('acymailing_listid'));
			
			$acymailingUserId  = $this->acymailingSubscribe($myUser, $lists, $formModel);
			$this->savePrivacy($data, $acymailingUserId);
		}
	}
	
	/**
	 * Insert record in privacy table
	 *
	 * @param	array	$data submitted data
	 * @param	int		$status status of consent : 0 = new, 1 = update, 2 = remove
	 *
	 * @return	bool
	 */
	protected function savePrivacy($data, $subid)
	{
		$db 	   = FabrikWorker::getDbo();
		$params    = $this->getParams();
		$formModel = $this->getModel();
		
		$now 	   = new JDate('now');
		$listId	   = $data['listid'];
		$formId	   = $data['formid'];
		$rowId	   = $data['rowid'];
		
		$consentMessage = $params->get('acymailing_signuplabel');
		
		// Optional record of the IP address
		$ip = '';
		if($params->get('acymailing_ip_record', '0') === '1')
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		   
		}
		
		$query 	 = $db->getQuery( true );
		$columns = array('id', 'date_time', 'list_id', 'form_id', 'row_id', 'user_id', 'consent_message', 'update_record','ip', 'newsletter_engine', 'sublist_id', 'subid');
		$values  = array('NULL',
						 $db->quote($now->format('Y-m-d H:i:s')),
						 $db->quote($listId),
						 $db->quote($formId),
						 $db->quote($rowId),
						 'NULL',
						 $db->quote($consentMessage),
						 0,
						 $db->quote($ip),
						 'Acymailing',
						 $db->quote(json_encode($params->get('acymailing_listid'))),
						 $subid
						 );
		$query->insert($db->quoteName('#__fabrik_privacy'))
			  ->columns($db->quoteName($columns))
			  ->values(implode(',', $values));
		$db->setQuery($query);
		$db->execute();
		
		return;
	}
	
	/**
	 * Check whether Acymailing component is installed
	 * returns false and error message if not installed
	 *
	 * @return	bool
	 */
	
	protected function checkAcymailing()
	{
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'))
		{
			throw new RuntimeException(FText::_('PLG_FORM_ACYMAILING_ERROR_MSG'));
			return false;
		}
		
		return true;
	}
	
	/**
	 * Add user as an Acymailing subscriber
	 *
	 * @return	bool
	 */
	
	protected function acymailingSubscribe($user, $lists, $formModel)
	{
		if($this->checkAcymailing())
		{
			$params    = $this->getParams();
			$confirm   = $params->get('acymailing_userconfirm', '0') === '1';
			
			$user->confirmed = 0;
			if($params->get('acymailing_double_optin', '1') === '0')
			{
				$user->confirmed = 1;
			}
	 
			$subscriberClass = acymailing_get('class.subscriber');
	 
			$subid = $subscriberClass->save($user);
	 
			$newSubscription = array();
			if(!empty($lists))
			{
				foreach($lists as $listId)
				{
					$newList = array();
					$newList['status'] = 1;
					
					$newSubscription[$listId] = $newList;
				}
			}
			
			if(empty($newSubscription)) return $subid;
			
			if(empty($subid)) return false;
			
			$subscriberClass->saveSubscription($subid,$newSubscription);
			
			return $subid;
		}
		else
		{
			return false;
		}
	}
}
