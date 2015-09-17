<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');
require_once JPATH_COMPONENT_SITE . '/helpers/citybranding.php';

class plgCitybrandingmail_notifier extends JPlugin
{
	public function onAfterNewPoiAdded($model, $validData, $id = null)
	{
		$details = $this->getDetails($id, $model);
		$app = JFactory::getApplication();

		$showMsgsFrontend = ($this->params->get('messagesfrontend') && !$app->isAdmin());
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		$poiLink =  rtrim(JURI::base(), '/') . JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $id);

		//Prepare email for admins
		if ($this->params->get('mailnewpoiadmins')){
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_NEW_POI_SUBJECT'), 
				$details->username, 
				$details->usermail
			);

			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_NEW_POI_BODY'),
				CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid']),
				$validData['title'],
				$validData['address']
			);
			$body .= '<a href="'.$poiLink.'">'.$poiLink.'</a>';
		
			if(empty($details->emails) || $details->emails[0] == ''){
				if($showMsgsBackend)
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_NOT_SET').CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid']), 'warning');
			}
			else {
				$recipients = implode(',', $details->emails);
				if ($this->sendMail($subject, $body, $details->emails) ) {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_CONFIRM').$recipients);
				}
				else {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
				}
			}
		}

		//Prepare email for user
		if ($this->params->get('mailnewpoiuser')) {		
			
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_NEW_POI_SUBJECT'), 
				$validData['title']
			);

			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_NEW_POI_BODY'),
				CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid'])
			);
			$body .= '<a href="'.$poiLink.'">'.$poiLink.'</a>';

			if ($this->sendMail($subject, $body, $details->usermail) ) {
				if($showMsgsBackend){
					//do we really want to sent confirmation mail if poi is submitted from backend?
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_NEW_POI_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}
				if($showMsgsFrontend){
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_NEW_POI_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}				
			}
			else {
				$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
			}

		}
	}	

	public function onAfterCategoryModified($model, $validData, $id = null)
	{
		$details = $this->getDetails($id, $model);
		$app = JFactory::getApplication();

		$showMsgsFrontend = ($this->params->get('messagesfrontend') && !$app->isAdmin());
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		$MENUALIAS = $this->params->get('menualias');
		$appSite = JApplication::getInstance('site');
		$router = $appSite->getRouter();
		$uri = $router->build('index.php?option=com_citybranding&view=poi&id='.(int) ($id == null ? $validData['id'] : $id));
		$parsed_url = $uri->toString();
		$parsed_url = str_replace('administrator/', '', $parsed_url);
		$parsed_url = str_replace('component/citybranding', $MENUALIAS, $parsed_url);
		$poiLink = $_SERVER['HTTP_HOST'] . $parsed_url;

		//Prepare email for admins
		if ($this->params->get('mailcategorychangeadmins')){
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_CATEGORY_MODIFIED_SUBJECT'), 
				($id == null ? $validData['id'] : $id)
			);

			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_CATEGORY_MODIFIED_BODY'),
				$validData['title'],
				CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid']),
				JFactory::getUser()->name
			);
		
			if(empty($details->emails) || $details->emails[0] == ''){
				if($showMsgsBackend)
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_NOT_SET').CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid']), 'warning');
			}
			else {
				$recipients = implode(',', $details->emails);
				if ($this->sendMail($subject, $body, $details->emails) ) {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_CONFIRM').$recipients);
				}
				else {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
				}
			}
		}

		//Prepare email for user
		if ($this->params->get('mailcategorychangeuser')) {		
			
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_CATEGORY_MODIFIED_SUBJECT'), 
				($id == null ? $validData['id'] : $id)
			);

			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_CATEGORY_MODIFIED_BODY'),
				$validData['title'],
				CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid'])
			);
			
			$body .= '<a href="http://'.$poiLink.'">'.$poiLink.'</a>';

			if ($this->sendMail($subject, $body, $details->usermail) ) {
				if($showMsgsBackend){
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_CATEGORY_MODIFIED_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}
				if($showMsgsFrontend){
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_CATEGORY_MODIFIED_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}				
			}
			else {
				$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
			}

		}
	}	

	public function onAfterStepModified($model, $validData, $id = null)
	{
		$details = $this->getDetails($id, $model);
		$app = JFactory::getApplication();

		$showMsgsFrontend = ($this->params->get('messagesfrontend') && !$app->isAdmin());
		$showMsgsBackend  = ($this->params->get('messagesbackend') && $app->isAdmin());

		$step = CitybrandingFrontendHelper::getStepByStepId($validData['stepid']);
		
		$MENUALIAS = $this->params->get('menualias');
		$appSite = JApplication::getInstance('site');
		$router = $appSite->getRouter();
		$uri = $router->build('index.php?option=com_citybranding&view=poi&id='.(int) ($id == null ? $validData['id'] : $id));
		$parsed_url = $uri->toString();
		$parsed_url = str_replace('administrator/', '', $parsed_url);
		$parsed_url = str_replace('component/citybranding', $MENUALIAS, $parsed_url);
		$poiLink = $_SERVER['HTTP_HOST'] . $parsed_url;

		//Prepare email for admins
		if ($this->params->get('mailstatuschangeadmins')){
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_STEP_MODIFIED_SUBJECT'), 
				($id == null ? $validData['id'] : $id)
			);


			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_STEP_MODIFIED_BODY'),
				$validData['title'],
				$step['stepid_title'],
				JFactory::getUser()->name
			);
		
			if(empty($details->emails) || $details->emails[0] == ''){
				if($showMsgsBackend)
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_NOT_SET').CitybrandingFrontendHelper::getCategoryNameByCategoryId($validData['catid']), 'warning');
			}
			else {
				$recipients = implode(',', $details->emails);
				if ($this->sendMail($subject, $body, $details->emails) ) {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_ADMINS_MAIL_CONFIRM').$recipients);
				}
				else {
					if($showMsgsBackend)
						$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
				}
			}
		}

		//Prepare email for user
		if ($this->params->get('mailstatuschangeuser')) {		
			
			$subject = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_STEP_MODIFIED_SUBJECT'), 
				($id == null ? $validData['id'] : $id)
			);

			$body = sprintf(
				JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_USER_STEP_MODIFIED_BODY'),
				$validData['title'],
				$step['stepid_title'],
				$poiLink
			);

			$body .= '<a href="http://'.$poiLink.'">'.$poiLink.'</a>';

			if ($this->sendMail($subject, $body, $details->usermail) ) {
				if($showMsgsBackend){
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_STEP_MODIFIED_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}
				if($showMsgsFrontend){
					$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_STEP_MODIFIED_CONFIRM').$details->usermail . ' (' . $details->username . ')');
				}				
			}
			else {
				$app->enqueueMessage(JText::_('PLG_CITYBRANDING_MAIL_NOTIFIER_MAIL_FAILED').$recipients, 'error');
			}

		}
	}	

	private function sendMail($subject, $body, $recipients) 
	{
		$app = JFactory::getApplication();
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');

		$mail = JFactory::getMailer();
		$mail->isHTML(true);
		$mail->Encoding = 'base64';
		if(is_array($recipients)){
			foreach($recipients as $recipient){
				if ($mail->ValidateAddress($recipient)){
					$mail->addRecipient($recipient);
				}
			}
		}
		else {
			$mail->addRecipient($recipients);
		}
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename.': '.$subject);
		$mail->setBody($body);
		if ($mail->Send()) {
		  return true;
		} else {
		  return false;
		}			
	}

	private function getDetails($id, $model) 
	{
		//check if poi added from frontend
		if($id == null){
			$poiid = $model->getItem()->get('id');
		} 
		else {
			$poiid = $id;
		}

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/poi.php';
		$poiModel = new CitybrandingModelPoi();
		//JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
		//$poiModel = JModelLegacy::getInstance( 'Poi', 'CitybrandingModel' );

		$emails = $poiModel->getItem($poiid)->get('notification_emails');

		$userid = $poiModel->getItem($poiid)->get('created_by');
		$username = JFactory::getUser($userid)->name;
		$usermail = JFactory::getUser($userid)->email;

		$details = new stdClass();
		$details->poiid = $poiid;
		$details->emails = $emails;
		$details->userid = $userid;
		$details->username = $username;
		$details->usermail = $usermail;

		return $details;
	}
}
