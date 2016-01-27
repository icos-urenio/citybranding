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

/**
 * Citybranding helper.
 */
class CitybrandingHelper {

	private static $catIds = array();
    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '') {
        JHtmlSidebar::addEntry(
			JText::_('COM_CITYBRANDING_TITLE_POIS'),
			'index.php?option=com_citybranding&view=pois',
			$vName == 'pois'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CITYBRANDING_TITLE_CATEGORIES'),
			"index.php?option=com_categories&extension=com_citybranding",
			$vName == 'categories'
		);
		if ($vName=='categories') {
			JToolBarHelper::title('City Branding: Categories (POIs)');
		}
	    JHtmlSidebar::addEntry(
		    JText::_('COM_CITYBRANDING_TITLE_CLASSIFICATIONS'),
		    'index.php?option=com_citybranding&view=classifications',
		    $vName == 'classifications'
	    );
	    JHtmlSidebar::addEntry(
		    JText::_('COM_CITYBRANDING_TITLE_BRANDS'),
		    'index.php?option=com_citybranding&view=brands',
		    $vName == 'brands'
	    );
	    JHtmlSidebar::addEntry(
		    JText::_('COM_CITYBRANDING_TITLE_AREAS'),
		    'index.php?option=com_citybranding&view=areas',
		    $vName == 'areas'
	    );

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return	JObject
     * @since	1.6
     */
    public static function getActions() {
        $user = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_citybranding';

        $actions = array(
            'core.admin', 
            'core.manage', 
            'core.create', 
            'core.edit', 
            'core.edit.own', 
            'core.edit.state', 
            'core.delete',
            'citybranding.manage.keys',
            'citybranding.manage.steps',
            'citybranding.manage.logs',
            'citybranding.showall.pois'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function getCategoriesByUserGroups($user = null) {
    	if($user == null) {
    		$user = JFactory::getUser();
    	}

    	self::$catIds = array();
    	CitybrandingHelper::getCategoriesUserGroups(); //populates self::catIds
    	$categories = self::$catIds;

    	$usergroups = JAccess::getGroupsByUser($user->id);
    	$allowed_catIds = array();
    	foreach ($categories as $category) {
    		foreach ($category['usergroups'] as $groupid) {
    			if (in_array($groupid, $usergroups)){
    				array_push($allowed_catIds, $category['catid']);
    			}
    		}
    	}

    	return $allowed_catIds;
    }

    private static function getCategoriesUserGroups($recursive = false)
    {
        $_categories = JCategories::getInstance('Citybranding');
        $_parent = $_categories->get();
        if(is_object($_parent))
        {
            $_items = $_parent->getChildren($recursive);
        }
        else
        {
            $_items = false;
        }
        return CitybrandingHelper::loadCats($_items);
    }
        
    private static function loadCats($cats = array())
    {
        if(is_array($cats))
        {
            foreach($cats as $JCatNode)
            {
                $params = json_decode($JCatNode->params);
                if(isset($params->citybranding_category_usergroup))
                    $usergroups = $params->citybranding_category_usergroup;
                else
                    $usergroups = array();

                self::$catIds[] = array('catid'=>$JCatNode->id,'usergroups'=>$usergroups);

                if($JCatNode->hasChildren())
                    CitybrandingHelper::loadCats($JCatNode->getChildren());
            }
        }
        return false;
    }

}
