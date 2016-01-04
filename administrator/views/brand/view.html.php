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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class CitybrandingViewBrand extends JViewLegacy {

    protected $state;
    protected $item;
    protected $form;
    //protected $logs;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        //$this->logs = $this->get('Logs');
//        if($this->item->id > 0)
//            $this->logs = $this->getModel('Logs')->getItemsByBrand($this->item->id);
//        else
//            $this->logs = array();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar() {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
        $canDo = CitybrandingHelper::getActions();

        JToolBarHelper::title(JText::_('COM_CITYBRANDING_TITLE_BRAND'), 'drawer');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {

            JToolBarHelper::apply('brand.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('brand.save', 'JTOOLBAR_SAVE');
        }
        // if (!$checkedOut && ($canDo->get('core.create'))) {
        //     JToolBarHelper::custom('brand.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        // }
        // If an existing item, can save to a copy.
        //if (!$isNew && $canDo->get('core.create')) {
        //    JToolBarHelper::custom('brand.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
        //}

        if(!empty($this->item->id)) {

            $bar = JToolBar::getInstance('toolbar');
            $layout = new JLayoutFile('joomla.toolbar.popup');
            $dhtml = $layout->render(
                array(
                    'doTask' => 'print', //$url,
                    'class' => 'icon-print',
                    'text' => JText::_('COM_CITYBRANDING_PRINT'),
                    'name' => 'citybranding-print'
            ));
            $bar->appendButton('Custom', $dhtml);
            
        }

        if (empty($this->item->id)) {
            JToolBarHelper::cancel('brand.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('brand.cancel', 'JTOOLBAR_CLOSE');
        }
    }

}
