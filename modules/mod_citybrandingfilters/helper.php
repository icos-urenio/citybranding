<?php
/**
 * @version     1.0.0
 * @package     com_citybranding
 * @subpackage  mod_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
defined('_JEXEC') or die;

class ModCitybrandingfiltersHelper {
    
    private static $filters;

    public function getVotes($id) 
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT votes FROM #__citybranding_pois WHERE id='.$id);
        $votes = $db->loadResult();
        return $votes;
    }

    public static function createStatuses() 
    {
        $app = JFactory::getApplication();
        $filter_steps = $app->getUserStateFromRequest('com_citybranding.pois.filter.steps', 'steps', array());

        //get poi statuses
        JFormHelper::addFieldPath(JPATH_ROOT . '/components/com_citybranding/models/fields');
        $step = JFormHelper::loadFieldType('Step', false);
        $statuses = $step->getOptions();
        if(empty($filter_steps)){
            $str = '<ul class="citybranding_ulist citybranding_ulist_inline">';
            foreach ($statuses as $status) {
                $str .= '<li>';
                $str .= '<input type="checkbox" name="steps[]" value="'.$status->value.'" '. 'checked="checked"' . '>';
                $str .= '<span class="root">'.' '.$status->text.'</span>';
                $str .= '</li>';
            }
            $str .= '</ul>';
        }
        else {
            $str = '<ul class="citybranding_ulist citybranding_ulist_inline">';
            foreach ($statuses as $status) {
                $str .= '<li>';
                $str .= '<input type="checkbox" name="steps[]" value="'.$status->value.'" '. (in_array($status->value, $filter_steps) ? 'checked="checked"' : '') . '>';
                $str .= '<span class="root">'.' '.$status->text.'</span>';
                $str .= '</li>';
            }
            $str .= '</ul>';
        }
        return $str;        
    }

    //TODO: getCategories + loadCats to be replaced with getOptions like subgrouplist.php does
    public static function getCategories($recursive = false)
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
        return ModCitybrandingfiltersHelper::loadCats($_items);
    }
        
    private static function loadCats($cats = array())
    {
        if(is_array($cats))
        {
            $i = 0;
            $return = array();
            foreach($cats as $JCatNode)
            {
                $return[$i] = new stdClass();
                $return[$i]->title = $JCatNode->title;
                $return[$i]->parentid = $JCatNode->parent_id;
                $return[$i]->path = $JCatNode->get('path');
                $return[$i]->id = $JCatNode->id;
                //$params = new JRegistry();
                //$params->loadJSON($JCatNode->params);
                //$return[$i]->image = $params->get('image');

                if($JCatNode->hasChildren())
                    $return[$i]->children = ModCitybrandingfiltersHelper::loadCats($JCatNode->getChildren());
                else
                    $return[$i]->children = false;

                $i++;
            }
            return $return;
        }
        return false;
    }

    private static function createFilters($cats = array())
    {
        $app = JFactory::getApplication();
        $filter_category = $app->getUserStateFromRequest('com_citybranding.pois.filter.category', 'cat', array());

        //self::$filters .= '<ul class="citybranding_ulist">';
        foreach($cats as $JCatNode){
            //id is the category id
            if(empty($filter_category)){
                if($JCatNode->parentid == 'root')
                {
                    self::$filters .= '<input path="' . $JCatNode->path . '" parent="box' . $JCatNode->parentid . '" name="cat[]" value="' . $JCatNode->id . '" type="checkbox" checked="checked" id="cat-' . $JCatNode->id . '" onclick="citybranding_filterbox_click(this,' . $JCatNode->id . ')" />' . "\n";
                    self::$filters .= '<label for="cat-' . $JCatNode->id .'"><span class="root">'.' '.JText::_($JCatNode->title).'</span></label>';
                }
                else {
                    self::$filters .= '<input path="' . $JCatNode->path . '" parent="box' . $JCatNode->parentid . '" name="cat[]" value="' . $JCatNode->id . '" type="checkbox" checked="checked" id="cat-' . $JCatNode->id . '" onclick="citybranding_filterbox_click(this,' . $JCatNode->id . ')" />' . "\n";
                    self::$filters .= '<label for="cat-' . $JCatNode->id .'">'.JText::_($JCatNode->title).'</label>';
                }
            }
            else{
                if($JCatNode->parentid == 'root')
                {
                    self::$filters .='<input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" '; if(in_array($JCatNode->id, $filter_category)) self::$filters .= 'checked="checked"'; self::$filters .= ' id="cat-'.$JCatNode->id.'" onclick="citybranding_filterbox_click(this,'.$JCatNode->id.')" />' . "\n";
                    self::$filters .= '<label for="cat-' . $JCatNode->id .'"><span class="root">'.' '.JText::_($JCatNode->title).'</span></label>';
                }
                else
                {
                    self::$filters .='<input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" '; if(in_array($JCatNode->id, $filter_category)) self::$filters .= 'checked="checked"'; self::$filters .= ' id="cat-'.$JCatNode->id.'" onclick="citybranding_filterbox_click(this,'.$JCatNode->id.')" />'. "\n";
                    self::$filters .= '<label for="cat-' . $JCatNode->id .'">'.JText::_($JCatNode->title).'</label>';
                }
            }
            
            if(!empty($JCatNode->children)){
                ModCitybrandingfiltersHelper::createFilters($JCatNode->children);
            }
        
        }
        //self::$filters .= '</ul>';

        return self::$filters;
    }

    private static function createFiltersAsArray($cats)
    {
        $ar[] = null;
        foreach($cats as $cat){
            self::$filters = '';
            $ar[] = ModCitybrandingfiltersHelper::createFilters(array($cat));
        }
        self::$filters = '';
        return $ar;
    }

    public function createLimitBox($lim)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        //$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $lim);
        $selected = $limit;

        $html = '';
        $values = array (1, 5, 10, 20, 100, 0);
        foreach($values as $i){
            $a = $i;
            if($a == 0)
                $a = JText::_('JALL');
            if($selected == $i){
                $html .= '<li><a href="#" onclick="jQuery(\'input[name=limit]\').val('.$i.');jQuery(\'#adminForm\').submit();">'.$a.' <i class="icon-ok"></i></a></li>';
            }
            else {
                $html .= '<li><a href="#" onclick="jQuery(\'input[name=limit]\').val('.$i.');jQuery(\'#adminForm\').submit();">'.$a.'</a></li>';
            }
        }
        return $html;
    }

    public static function getCategoryFilters($cat_id = 0) {
        $categories = ModCitybrandingfiltersHelper::getCategories();
        $filters = ModCitybrandingfiltersHelper::createFiltersAsArray($categories);
        return array_filter($filters);
    }

    public static function getClassificationFilters() {
        $classifications = ModCitybrandingfiltersHelper::getCategories();
        $filters = ModCitybrandingfiltersHelper::createFiltersAsArray($classifications);
        return array_filter($filters);
    }


}
