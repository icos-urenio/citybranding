<?php

/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
defined('_JEXEC') or die;

class CitybrandingFrontendHelper
{

	private static $_items = array(); //used by getCategories
	private static $_parent; //used by getCategories

    public static function convert2UTC($date)
    {
        //get timezone from settings
        $offset = JFactory::getConfig()->get('offset');

        $utc = new DateTime($date, new DateTimeZone($offset));
        $utc->setTimezone(new DateTimeZone('UTC'));
        return $utc->format('Y-m-d H:i:s');
    }

	public static function convertFromUTC($date)
	{
		//get timezone from settings
		$offset = JFactory::getConfig()->get('offset');

		$tzDate = new DateTime($date, new DateTimeZone('UTC'));
		$tzDate->setTimezone(new DateTimeZone($offset));
		return $tzDate->format('Y-m-d H:i:s');
	}

	public static function checkNullArguments($args)
	{
		$nullArguments = array();

		if(!is_array($args))
		{
			throw new Exception('Checking arguments bad input');
		}

		foreach($args as $name => $value)
		{
			if (is_null($value))
			{
				array_push($nullArguments, $name);
			}
		}

		if(!empty($nullArguments))
		{
			$errMsg = 'The following arguments are missing or bad input: ' . implode(', ',$nullArguments);
			throw new Exception($errMsg);
		}
	}

	public static function sanitizePois($data, $userid)
	{
		if(!is_array($data)){
			throw new Exception('Pois sanitization bad input');
		}

		$pois = array();

		foreach ($data as $poi)
		{
			$poi = self::sanitizePoi($poi, $userid);
			array_push($pois, $poi);
		}
		return $pois;
	}

	public static function sanitizePoi($data, $userid)
	{
		if(!is_object($data)){
            throw new Exception('Poi sanitization bad input');
        }
        //unset overhead
        unset($data->asset_id);
        unset($data->ordering);
        unset($data->checked_out);
        unset($data->checked_out_time);
        unset($data->access);
        unset($data->language);
        unset($data->note);
        unset($data->modality);
        unset($data->updated_by);
		unset($data->access_level);
		unset($data->editor);

		if($data->category_image != '')
		{
			$data->category_image = JUri::base() . $data->category_image;
		}

        //separate photos and file attachments
        $obj = json_decode($data->photo);
		unset($data->photo);

		$data->photos = array();
		$data->attachments = array();

		if(is_object($obj)) {
			foreach ($obj->files as $file) {
				unset($file->deleteType);
				unset($file->deleteUrl);

				if (isset($file->thumbnailUrl))
				{
					$file->url = substr($file->url, 0, strlen($obj->imagedir)) === $obj->imagedir ? JUri::base() . $file->url : dirname(JUri::base()) . $file->url;
					$file->mediumUrl = substr($file->mediumUrl, 0, strlen($obj->imagedir)) === $obj->imagedir ? JUri::base() . $file->mediumUrl : dirname(JUri::base()) . $file->mediumUrl;
					$file->thumbnailUrl = substr($file->thumbnailUrl, 0, strlen($obj->imagedir)) === $obj->imagedir ? JUri::base() . $file->thumbnailUrl : dirname(JUri::base()) . $file->thumbnailUrl;
					array_push($data->photos, $file);
				}
				else
				{
					$file->url = substr($file->url, 0, strlen($obj->imagedir)) === $obj->imagedir ? JUri::base() . $file->url : dirname(JUri::base()) . $file->url;
					array_push($data->attachments, $file);
				}
			}
			unset($obj->id);
			unset($obj->imagedir);
		}

        //set dates to server timezone
        $data->created_TZ = $data->created == '0000-00-00 00:00:00' ? $data->created : self::convertFromUTC($data->created);
        $data->updated_TZ = $data->updated == '0000-00-00 00:00:00' ? $data->updated : self::convertFromUTC($data->updated);
        $data->regdate_TZ = $data->regdate == '0000-00-00 00:00:00' ? $data->regdate : self::convertFromUTC($data->regdate);

		$data->myPoi = ($data->created_by == $userid);

		//do the casting
		$data->moderation = (boolean)$data->moderation;
		$data->id = (int)$data->id;
		$data->stepid = (int)$data->stepid;
		$data->catid = (int)$data->catid;
		$data->state = (int)$data->state;
		$data->created_by = (int)$data->created_by;
		$data->hits = (int)$data->hits;
		$data->votes = (int)$data->votes;
		$data->subgroup = (int)$data->subgroup;

        return $data;
	}

	public static function sanitizeSteps($data)
	{
		if(!is_array($data)){
			throw new Exception('Steps sanitization bad input');
		}

		$steps = array();

		foreach ($data as $step)
		{
			$step = self::sanitizeStep($step);
			array_push($steps, $step);
		}
		return $steps;
	}

	public static function sanitizeStep($data)
	{
		if(!is_object($data)){
			throw new Exception('Step sanitization bad input');
		}
		//unset overhead
		unset($data->asset_id);
		unset($data->state);
		unset($data->created);
		unset($data->updated);
		unset($data->checked_out);
		unset($data->checked_out_time);
		unset($data->created_by);
		unset($data->updated_by);
		unset($data->language);
		unset($data->editor);

		return $data;
	}

	public static function getCategories($recursive = false)
	{
		$categories = JCategories::getInstance('citybranding');
		self::$_parent = $categories->get();
		if (is_object(self::$_parent)) {
			self::$_items = self::$_parent->getChildren($recursive);
		}
		else {
			self::$_items = false;
		}

		return self::loadCats(self::$_items);
	}

	protected static function loadCats($cats = array())
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
				$params = json_decode($JCatNode->params);

				$return[$i]->image = $params->image;
				if($return[$i]->image)
				{
					$return[$i]->image = JUri::base() . $return[$i]->image;
				}

				if($JCatNode->hasChildren())
					$return[$i]->children = self::loadCats($JCatNode->getChildren());
				else
					$return[$i]->children = null;

				$i++;
			}
			return $return;
		}
		return false;
	}

	public static function isValidTimeStamp($timestamp)
	{
		return ((string) (int) $timestamp === $timestamp)
		&& ($timestamp <= PHP_INT_MAX)
		&& ($timestamp >= ~PHP_INT_MAX);
	}

	/**
	* Get category name using category ID
	* @param integer $category_id Category ID
	* @return mixed category name if category is found, null otherwise
	*/
	public static function getCategoryNameByCategoryId($category_id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('title')
			->from('#__categories')
			->where('extension = ' . $db->quote('com_citybranding'))
			->where('id = ' . intval($category_id));

		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	* Get step name using step ID
	* @param integer $stepid Step ID
	* @return step name and color if step is found, null otherwise
	*/
	public static function getStepByStepId($stepid) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('a.title AS stepid_title, a.stepcolor AS stepid_color, a.ordering')
			->from('#__citybranding_steps AS a')
			->where('a.id = ' . intval($stepid));

		$db->setQuery($query);
		return $db->loadAssoc();
	}

	public static function getPrimaryStepId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('a.id')
			->from('#__citybranding_steps AS a')
			->order('a.ordering');

		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getGroupNameById($group_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('title')
			->from('#__usergroups')
			->where('id = ' . intval($group_id));

		$db->setQuery($query);
		return $db->loadResult();	
	}

	public static function emailExists($email)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('email')." = ".$db->quote($email));

		$db->setQuery($query);
		$count = $db->loadResult();

		return (bool) $count;
	}

	public static function setLanguage($language, $extensions = array('com_citybranding'))
	{
		$lang = JFactory::getLanguage();
		$joomlaLang = null;

		//try to align input with available language
		$availLanguages = $lang->getKnownLanguages();
		foreach ($availLanguages as $key => $value) {
			if($language == substr($key, 0, 2))
			{
				$joomlaLang = $key;
			}
		}

		if(is_null($joomlaLang))
		{
			throw new Exception('Language is not available');
		}

		$base_dir = JPATH_SITE;
		$language_tag = $joomlaLang;
		$reload = true;
		//for each extension load the appropriate language
		foreach ($extensions as $extension) {
			$lang->load($extension, $base_dir, $language_tag, $reload);
		}
	}

	public static function getRelativeTime($time)
	{
		if(strtotime($time) <= 0)
			return '';

		$time = CitybrandingFrontendHelper::convertFromUTC($time);

		// Load the parameters.
		$app = JFactory::getApplication();
/*		$params	= $app->getParams();
		$showrelativedates = $params->get('showrelativedates');		
		$dateformat = $params->get('dateformat');		
		
		if(!$showrelativedates){
			//$item->reported_rel = date("d/m/Y",strtotime($item->reported));
			return date($dateformat,strtotime($time));
		}
*/		
		$SECOND = 1;
		$MINUTE = 60 * $SECOND;
		$HOUR = 60 * $MINUTE;
		$DAY = 24 * $HOUR;
		$MONTH = 30 * $DAY;
 
		$delta = time() - strtotime($time);
		
		if ($delta < 1 * $MINUTE)
		{
			return $delta == 1 ? JText::_('ONE_SECOND_AGO') : sprintf(JText::_('SECONDS_AGO'), $delta);
		}
		if ($delta < 2 * $MINUTE)
		{
		  return JText::_('A_MINUTE_AGO');
		}
		if ($delta < 45 * $MINUTE)
		{
			return sprintf(JText::_('MINUTES_AGO'), floor($delta / $MINUTE));
		}
		if ($delta < 90 * $MINUTE)
		{
		  return JText::_('AN_HOUR_AGO');
		}
		if ($delta < 24 * $HOUR)
		{
		  return sprintf(JText::_('HOURS_AGO'), floor($delta / $HOUR));
		}
		if ($delta < 48 * $HOUR)
		{
		  return JText::_('YESTERDAY');
		}
		if ($delta < 30 * $DAY)
		{
			return sprintf(JText::_('DAYS_AGO'), floor($delta / $DAY));
		}
		if ($delta < 12 * $MONTH)
		{
		  $months = floor($delta / $DAY / 30);
		  return $months <= 1 ? JText::_('ONE_MONTH_AGO') : sprintf(JText::_('MONTHS_AGO'), $months);
		}
		else
		{
			$years = floor($delta / $DAY / 365);
			if($years < 100)	//TODO: needed for versions older than PHP5.3
				return $years <= 1 ? JText::_('ONE_YEAR_AGO') : sprintf(JText::_('YEARS_AGO'), $years);
			else
				return '';
		}

	}

	public function cutString($title, $max)
	{
	    if($title=='')
	        return '';

	    if(is_array($title)) list($string, $match_to) = $title;
	    else { $string = $title; $match_to = $title{0}; }
	 
	    $match_start = stristr($string, $match_to);
	    $match_compute = strlen($string) - strlen($match_start);
	 
	    if (strlen($string) > $max)
	    {
	        if ($match_compute < ($max - strlen($match_to)))
	        {
	            $pre_string = substr($string, 0, $max);
	            $pos_end = strrpos($pre_string, " ");
	            if($pos_end === false) $string = $pre_string."...";
	            else $string = substr($pre_string, 0, $pos_end)."...";
	        }
	        else if ($match_compute > (strlen($string) - ($max - strlen($match_to))))
	        {
	            $pre_string = substr($string, (strlen($string) - ($max - strlen($match_to))));
	            $pos_start = strpos($pre_string, " ");
	            $string = "...".substr($pre_string, $pos_start);
	            if($pos_start === false) $string = "...".$pre_string;
	            else $string = "...".substr($pre_string, $pos_start);
	        }
	        else
	        {
	            $pre_string = substr($string, ($match_compute - round(($max / 3))), $max);
	            $pos_start = strpos($pre_string, " "); $pos_end = strrpos($pre_string, " ");
	            $string = "...".substr($pre_string, $pos_start, $pos_end)."...";
	            if($pos_start === false && $pos_end === false) $string = "...".$pre_string."...";
	            else $string = "...".substr($pre_string, $pos_start, $pos_end)."...";
	        }
	 
	        $match_start = stristr($string, $match_to);
	        $match_compute = strlen($string) - strlen($match_start);
	    }
	 
	    return $string;

	}

	public static function getClassificationById($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
				->select('a.icon')
				->from('#__citybranding_classifications AS a')
				->where('a.id = ' . intval($id));

		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getClassificationTitleById($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('a.title')
			->from('#__citybranding_classifications AS a')
			->where('a.id = ' . intval($id));

		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getAreaById($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('a.title')
			->from('#__citybranding_areas AS a')
			->where('a.id = ' . intval($id));

		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getRelativeBrands($lat, $lng, $radius = 2.0)
	{
		$db = JFactory::getDbo();
		$origLat = $lat;
		$origLon = $lng;
		$dist = $radius; // This is the maximum distance (in miles) away from $origLat, $origLon in which to search
		$query = "
			SELECT id,address,latitude,longitude,moderation,photo,title,areaid,is_global, 3956 * 2 *
          ASIN(SQRT( POWER(SIN(($origLat - abs(latitude))*pi()/180/2),2)
          +COS($origLat*pi()/180 )*COS(abs(latitude)*pi()/180)
          *POWER(SIN(($origLon-longitude)*pi()/180/2),2)))
          as distance FROM #__citybranding_brands WHERE
          longitude between ($origLon-$dist/abs(cos(radians($origLat))*69))
          and ($origLon+$dist/abs(cos(radians($origLat))*69))
          and latitude between ($origLat-($dist/69))
          and ($origLat+($dist/69))
          and state = 1
          AND is_global = 0
          HAVING distance < $dist ORDER BY distance;";

		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as &$oneItem) {
			$tags = new JHelperTags;
			$tagIds = $tags->getTagIds($oneItem['id'], 'com_citybranding.brand');
			$tagNames = $tags->getTagNames(explode(',',$tagIds));
			//$oneItem['tags'] = implode(', ',$tagNames);
			$oneItem['tags'] = $tagNames;
		}
		return $items;
	}

	public static function getGlobalBrands()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('id,address,latitude,longitude,moderation,photo,title,areaid,is_global')
			->from('#__citybranding_brands')
			->where('is_global = 1');
		$db->setQuery($query);
		$items = $db->loadAssocList();
		foreach ($items as &$oneItem) {
			$tags = new JHelperTags;
			$tagIds = $tags->getTagIds($oneItem['id'], 'com_citybranding.brand');
			$tagNames = $tags->getTagNames(explode(',',$tagIds));
			//$oneItem['tags'] = implode(', ',$tagNames);
			$oneItem['tags'] = $tagNames;
		}
		return $items;
	}

	public static function getRelativePois($lat, $lng, $radius = 2.0)
	{
		$db = JFactory::getDbo();
		$origLat = $lat;
		$origLon = $lng;
		$dist = $radius; // This is the maximum distance (in miles) away from $origLat, $origLon in which to search
		$query = "
			SELECT id,catid,address,latitude,longitude,moderation,photo,classifications,title, 3956 * 2 *
          ASIN(SQRT( POWER(SIN(($origLat - abs(latitude))*pi()/180/2),2)
          +COS($origLat*pi()/180 )*COS(abs(latitude)*pi()/180)
          *POWER(SIN(($origLon-longitude)*pi()/180/2),2)))
          as distance FROM #__citybranding_pois WHERE
          longitude between ($origLon-$dist/abs(cos(radians($origLat))*69))
          and ($origLon+$dist/abs(cos(radians($origLat))*69))
          and latitude between ($origLat-($dist/69))
          and ($origLat+($dist/69))
          and state = 1
          having distance < $dist ORDER BY distance;";

		$db->setQuery($query);
		$pois = $db->loadAssocList();
		foreach ($pois as &$poi)
		{
			$poi['category_image'] = self::getCategoryImage($poi['catid']);
			$poi['category_image'] = ($poi['category_image'] == '' ? '' : JURI::base() . $poi['category_image']);

		}
		return $pois;
	}

	public function getCategoryImage($catid)
	{
		$category_image = '';
		$category = JCategories::getInstance('Citybranding')->get($catid);
		$params = json_decode($category->params);
		if(isset($params->image))
		{
			$category_image = $params->image;
		}

		return $category_image;
	}
}
