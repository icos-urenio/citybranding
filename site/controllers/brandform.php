<?php

/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
// No direct access
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_COMPONENT . '/helpers/citybranding.php';
JPluginHelper::importPlugin('citybranding');

/**
 * Poi controller class.
 */
class CitybrandingControllerBrandForm extends CitybrandingController {

    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @since	1.6
     */
    public function edit() {
        $app = JFactory::getApplication();

        // Get the previous edit id (if any) and the current edit id.
        $previousId = (int) $app->getUserState('com_citybranding.edit.brand.id');
        $editId = JFactory::getApplication()->input->getInt('id', null, 'array');

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_citybranding.edit.brand.id', $editId);

        // Get the model.
        $model = $this->getModel('BrandForm', 'CitybrandingModel');

        // Check out the item
        if ($editId) {
            $model->checkout($editId);
        }

        // Check in the previous user.
        if ($previousId) {
            $model->checkin($previousId);
        }

        // Redirect to the edit screen.
        $this->setRedirect(JRoute::_('index.php?option=com_citybranding&id=&view=brand&layout=edit', false));
    }

    /**
     * Method to save a user's profile data.
     *
     * @return	void
     * @since	1.6
     */
    public function save() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel('BrandForm', 'CitybrandingModel');

        // Get the user data.
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());
            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            $input = $app->input;
            $jform = $input->get('jform', array(), 'ARRAY');

            // Save the data in the session.
            $app->setUserState('com_citybranding.edit.brand.data', $jform, array());

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_citybranding.edit.brand.id');
            $this->setRedirect(JRoute::_('index.php?option=com_citybranding&view=brandform&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_citybranding.edit.brand.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_citybranding.edit.brand.id');
            $this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_citybranding&view=brandform&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_citybranding.edit.brand.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_CITYBRANDING_BRAND_SAVED_SUCCESSFULLY'));
        //$menu = JFactory::getApplication()->getMenu();
        //$item = $menu->getActive();
        //$url = (empty($item->link) ? 'index.php?option=com_citybranding&view=pois' : $item->link);
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getItems(array('link', 'language'), array('index.php?option=com_citybranding&view=pois', JFactory::getLanguage()->getTag()) , true);

        $url = 'index.php?option=com_citybranding&view=pois&Itemid='.$item->id;
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_citybranding.edit.brand.data', null);

        //emulate postSaveHook like extending from JControllerForm
        $this->postSaveHook($model, $data);
    }

    function cancel() {
        
        $app = JFactory::getApplication();

        // Get the current edit id.
        $editId = (int) $app->getUserState('com_citybranding.edit.brand.id');

        // Get the model.
        $model = $this->getModel('BrandForm', 'CitybrandingModel');

        // Check in the item
        if ($editId) {
            $model->checkin($editId);
        }
        
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_citybranding&view=pois' : $item->link);
        $this->setRedirect(JRoute::_($url, false));
    }

    public function remove() {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $model = $this->getModel('BrandForm', 'CitybrandingModel');

        // Get the user data.
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        // Validate the posted data.
        $form = $model->getForm();
        if (!$form) {
            JError::raiseError(500, $model->getError());
            return false;
        }

        // Validate the posted data.
        $data = $model->validate($form, $data);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the data in the session.
            $app->setUserState('com_citybranding.edit.brand.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_citybranding.edit.brand.id');
            $this->setRedirect(JRoute::_('index.php?option=com_citybranding&view=brand&layout=edit&id=' . $id, false));
            return false;
        }

        // Attempt to save the data.
        $return = $model->delete($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_citybranding.edit.brand.data', $data);

            // Redirect back to the edit screen.
            $id = (int) $app->getUserState('com_citybranding.edit.brand.id');
            $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
            $this->setRedirect(JRoute::_('index.php?option=com_citybranding&view=brand&layout=edit&id=' . $id, false));
            return false;
        }


        // Check in the profile.
        if ($return) {
            $model->checkin($return);
        }

        // Clear the profile id from the session.
        $app->setUserState('com_citybranding.edit.brand.id', null);

        // Redirect to the list screen.
        $this->setMessage(JText::_('COM_CITYBRANDING_ITEM_DELETED_SUCCESSFULLY'));
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_citybranding&view=pois' : $item->link);
        $this->setRedirect(JRoute::_($url, false));

        // Flush the data from the session.
        $app->setUserState('com_citybranding.edit.brand.data', null);
    }

    //simulate postSaveHook to move any images to the correct directory
    public function postSaveHook (JModelLegacy $model, $validData = array())
    {
        
        $insertid = JFactory::getApplication()->getUserState('com_citybranding.edit.brand.insertid');

        //B: move any images only if record is new
        if($validData['id'] == 0){
            //check if any files uploaded
            $obj = json_decode( $validData['photo'] );
            if(empty($obj->files))
                return;

            $srcDir = JPATH_ROOT . '/' . $obj->imagedir . '/' . $obj->id;
            $dstDir = JPATH_ROOT . '/' . $obj->imagedir . '/' . $insertid;
            $success = rename ( $srcDir , $dstDir );

            if($success){
                //update photo json isnew, id
                unset($obj->isnew);
                $obj->id = $insertid;
                $photo = json_encode($obj);

                // Create an object for the record we are going to update.
                $object = new stdClass();
                $object->id = $insertid;
                $object->photo = $photo;
                // Update photo
                $result = JFactory::getDbo()->updateObject('#__citybranding_brands', $object, 'id');

            }
            else {
                JFactory::getApplication()->enqueueMessage('Cannot move '.$srcDir.' to '.$dstDir.'. Check folder rights', 'error'); 
            }

        }


    }
}
