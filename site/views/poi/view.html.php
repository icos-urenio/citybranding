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
JHtml::_('jquery.framework');
/**
 * View to edit
 */
class CitybrandingViewPoi extends JViewLegacy {

    protected $state;
    protected $item;
    ///protected $form;
    protected $params;

    /**
     * Display the view
     */
    public function display($tpl = null) {

        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        $this->state = $this->get('State');
        $this->item = $this->get('Data');
        $this->params = $app->getParams('com_citybranding');

        if (!empty($this->item)) {
            ///$this->form = $this->get('Form');

            // JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
            // $poiModel = JModelLegacy::getInstance( 'Poi', 'CitybrandingModel' );
            // $results = $poiModel->getItem($id);
            // print_r($results);
            $this->logs = $this->getModel('Logs')->getItemsByPoi($this->item->id);

        }


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        

        if ($this->_layout == 'edit') {

            $authorised = $user->authorise('core.create', 'com_citybranding');

            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument() {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_CITYBRANDING_DEFAULT_PAGE_TITLE'));
        }
        $title = $this->params->get('page_title', '');
        if (empty($title)) {
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        $this->document->addStyleSheet(JURI::root(true).'/components/com_citybranding/assets/css/style.css');
        $this->document->addStyleSheet(JURI::root(true).'/components/com_citybranding/assets/css/photobox.css');
        // TODO: add this <!--[if lt IE 9]><link rel="stylesheet" href="photobox/photobox.ie.css"><![endif]-->
        $this->document->addScript(JURI::root(true).'/components/com_citybranding/assets/js/jquery.photobox.js');
        $this->document->addScript(JURI::root(true).'/components/com_citybranding/assets/js/citybranding.js');
    }

}
