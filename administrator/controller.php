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

class CitybrandingController extends JControllerLegacy {

    /**
     * Method to display a view.
     *
     * @param	boolean			$cachable	If true, the view output will be cached
     * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return	JController		This object to support chaining.
     * @since	1.5
     */
    public function display($cachable = false, $urlparams = false) {
        require_once JPATH_COMPONENT . '/helpers/citybranding.php';

        $view = JFactory::getApplication()->input->getCmd('view', 'issues');
        JFactory::getApplication()->input->set('view', $view);
        
        if($view == 'issue'){
            $v = $this->getView($view, 'html');
            $v->setModel($this->getModel($view), true); //the default model (true)
            
            $logsModel = $this->getModel('Logs', 'CitybrandingModel');
            $v->setModel($logsModel, false);
            $v->setLayout('edit');
        }
        
        parent::display($cachable, $urlparams);
        return $this;
    }

}
