<?php
/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
error_reporting(E_ALL | E_STRICT);
// No direct access
defined('_JEXEC') or die;

JLoader::register('UploadHandler', JPATH_COMPONENT . '/models/fields/multiphoto/server/UploadHandler.php');

jimport('joomla.application.component.controllerform');

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Issue controller class.
 */
class CitybrandingControllerUpload extends CitybrandingController
{
	
	public function handler()
	{
        // Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));        

		//TODO: http://docs.joomla.org/JSON_Responses_with_JResponseJson
    	JFactory::getDocument()->setMimeEncoding( 'application/json' );
	    JResponse::setHeader('Content-Disposition','attachment;filename="test.json"');

		$options = array(
		            'script_url' => JRoute::_( JURI::root(true).'/index.php?option=com_citybranding&task=upload.handler&format=json&id='.JRequest::getVar('id').'&imagedir='.JRequest::getVar('imagedir').'&'.JSession::getFormToken() .'=1' ),
		            'upload_dir' => JPATH_ROOT . '/'.JRequest::getVar('imagedir') . '/' . JRequest::getVar('id').'/',
		            'upload_url' => JRequest::getVar('imagedir') . '/'.JRequest::getVar('id').'/'
		        );
		$upload_handler = new UploadHandler($options);
		JFactory::getApplication()->close();
	}

}
