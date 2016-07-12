<?php

/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Citybranding records.
 */
class CitybrandingModelPois extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'stepid', 'a.stepid',
                'catid', 'a.catid',
                'description', 'a.description',
                'address', 'a.address',
                'latitude', 'a.latitude',
                'longitude', 'a.longitude',
                'photo', 'a.photo',
                'access', 'a.access', 'access_level',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created', 'a.created',
                'updated', 'a.updated',
                'created_by', 'a.created_by',
                'language', 'a.language',
                'hits', 'a.hits',
                'note', 'a.note',
                'votes', 'a.votes',
                'modality', 'a.modality',

            );
        }
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) {

        //set default ordering
        if($ordering == null || empty($ordering)) {
            $ordering = 'a.updated';
        }

        //set default ordering
        if($direction == null || empty($direction)) {
            $direction = 'DESC';
        }

        // Initialise variables.
        $app = JFactory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        //$this->setState('list.limit', $limit);

        $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        //$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $published = 1; //always fetch published pois only
        $this->setState('filter.state', $published);

        $access = $app->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $category = $app->getUserStateFromRequest($this->context . '.filter.category', 'cat', array());
        $this->setState('filter.category', $category);
        //Filtering catid
        //$this->setState('filter.catid', $app->getUserStateFromRequest($this->context.'.filter.catid', 'filter_catid', '', 'string'));

        $classification = $app->getUserStateFromRequest($this->context . '.filter.classification', 'cla', array());
        $this->setState('filter.classification', $classification);

        $steps = $app->getUserStateFromRequest($this->context . '.filter.steps', 'steps', array());
        $this->setState('filter.steps', $steps);
        //Filtering stepid
        //$this->setState('filter.stepid', $app->getUserStateFromRequest($this->context.'.filter.stepid', 'filter_stepid', '', 'string'));

        //Filtering owned
        $this->setState('filter.owned', $app->getUserStateFromRequest($this->context.'.filter.owned', 'filter_owned', 'no', 'string'));

        $this->setState('filter.language', JLanguageMultilang::isEnabled());

        // List state information.
        parent::populateState($ordering, $direction);
        $this->setState('list.limit', 0); //show all
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        //check API request
        $userid = $this->state->get('filter.citybrandingapi.userid', 0);
        $user = ($userid > 0 ? JFactory::getUser($userid) : JFactory::getUser());

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'DISTINCT a.*'
                )
        );

        $query->from('`#__citybranding_pois` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
		// Join over the category 'catid'
		$query->select('catid.params AS catid_params, catid.title AS catid_title');
		$query->join('LEFT', '#__categories AS catid ON catid.id = a.catid');
		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
        // Join over the citybranding steps.
        $query->select('st.title AS stepid_title, st.stepcolor AS stepid_color')
            ->join('LEFT', '#__citybranding_steps AS st ON st.id = a.stepid');
        
        // Filter by published state
        $published = $this->getState('filter.state');
         if (is_numeric($published)) {
             $query->where('a.state = ' . (int) $published);
         } else if ($published === '') {
             $query->where('(a.state IN (0, 1))');
         }
        //$query->where('a.state = 1');        

        // Filter by moderation
        $query->where('
            (
            (a.created_by > 0 AND a.created_by  ='.$user->id.' AND a.moderation IN (0,1)) OR
            (a.created_by > 0 AND a.created_by !='.$user->id.' AND a.moderation = 0)
            )
        ');

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.title LIKE '.$search.' )');
            }
        }


        // Filter by access level.
        if ($access = $this->getState('filter.access'))
        {
            $query->where('a.access = ' . (int) $access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin'))
        {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }        

        //Filtering stepid
        // if ($stepid = $this->getState('filter.stepid'))
        // {
        //     $query->where('a.stepid = ' . (int) $stepid);
        // }

        $filter_steps = $this->state->get('filter.steps');
        if(!empty($filter_steps)){
            if(!in_array(0, $filter_steps)){
                $filter_steps = implode(',', $filter_steps);
                $query->where('a.stepid IN ('.$filter_steps.')');
            }
        }        

        // Filter by category
        $filter_category = $this->state->get('filter.category');
        if(!empty($filter_category)){
            if(!in_array(0, $filter_category)){
                $filter_category = implode(',', $filter_category);
                $query->where('a.catid IN ('.$filter_category.')');
            }
        }

        // Filter by classification
        $filter_classification = $this->state->get('filter.classification');
        if(!empty($filter_classification)){
            if(!in_array(0, $filter_classification)){
                $filter_classification = implode(',', $filter_classification);
                $query->where('a.classifications IN ('.$filter_classification.')');
            }
        }

        //Filtering owned
	    $filter_owned = $this->state->get("filter.owned");
	    if ($filter_owned == 'yes' && $user->id > 0) {
		    $query->where("a.created_by = '".$user->id."'");
	    }

	    // Filter by language
	    if ($this->getState('filter.language'))
	    {
		    $query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
	    }

	    // Filter by geo-boundaries (Currently only used by API requests
	    $minLat = $this->state->get('filter.citybrandingapi.minLat');
	    $maxLat = $this->state->get('filter.citybrandingapi.maxLat');
	    $minLng = $this->state->get('filter.citybrandingapi.minLng');
	    $maxLng = $this->state->get('filter.citybrandingapi.maxLng');

	    if(!is_null($minLat) && !is_null($maxLat) && !is_null($minLng) && !is_null($maxLng))
	    {
		    $query->where('a.latitude BETWEEN ' . $minLat . ' AND ' . $maxLat );
		    $query->where('a.longitude BETWEEN ' . $minLng . ' AND ' . $maxLng );
	    }

	    // Add the list ordering clause.
	    $orderCol = $this->state->get('list.ordering');
	    $orderDirn = $this->state->get('list.direction');

	    if(!$orderCol && !$orderDirn)
	    {
		    $orderCol = $this->state->get('filter.citybrandingapi.ordering', '');
		    $orderDirn = $this->state->get('filter.citybrandingapi.direction', '');
	    }

	    if ($orderCol == 'access_level')
        {
            $orderCol = 'ag.title';
        }

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }
        else {
            //set default ordering
            $query->order($db->escape('a.updated' . ' ' . 'DESC'));       
        }

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();

        if (JFactory::getApplication()->isSite()) {

            $user = JFactory::getUser();
            $canChange = $user->authorise('core.edit.state', 'com_citybranding');
            $groups = $user->getAuthorisedViewLevels();
            $categories = JCategories::getInstance('citybranding');

    
            for ($x = 0, $count = count($items); $x < $count; $x++) {
	            $items[$x]->created_by_name = JFactory::getUser($items[$x]->created_by)->name;

                // Set category image (for marker icon)...avoid using JCategories, just get category params in the main query
                $prms = json_decode($items[$x]->catid_params);
                unset($items[$x]->catid_params);
                // if(isset($prms->citybranding_category_emails))
                //     $items[$x]->notification_emails = explode("\n", $prms->citybranding_category_emails);
                // else
                //     $items[$x]->notification_emails = array();

                if(isset($prms->image))
                    $items[$x]->category_image = $prms->image;
                else
                    $items[$x]->category_image = '';


                // Check the access level. Remove pois the user shouldn't see
                if (!in_array($items[$x]->access, $groups)) {
                    unset($items[$x]);
                    continue;
                }

                //Check the state. Remove pois that are not not published but keep own (unless is privileged)
                if ( ($items[$x]->created_by != $user->id) && ($items[$x]->state != 1) && (!$canChange) ) {
                    unset($items[$x]);
                    continue;
                }

                //Remove anonymous unpublished
                if ( ($items[$x]->created_by == 0) && ($items[$x]->state != 1) ) {
                    unset($items[$x]);
                    continue;
                }

                //Remove according to category access level
                //if not privileged user Jcategory->get returns nothing... actually the whole object is not even set
                if (!isset($categories->get($items[$x]->catid)->access)) {
                    unset($items[$x]);
                    continue;
                }
                
            }
        }
        return $items;
    }

}
