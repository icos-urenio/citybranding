<?php
/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Pois list controller class.
 */
class CitybrandingControllerPois extends CitybrandingController
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Pois', $prefix = 'CitybrandingModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => false));
		return $model;
	}

	public function markers()
	{
		try {
			$items = $this->getModel()->getItems();
			//$items contains too much overhead, set only necessary data
			$markers = array();
			foreach ($items as $item) {
				$marker = new StdClass();
				$marker->id = $item->id;
				$marker->state = $item->state;
				$marker->moderation = $item->moderation;
				$marker->title = $item->title;
				$marker->latitude = $item->latitude;
				$marker->longitude = $item->longitude;
				$marker->category_image = ($item->category_image == '' ? '' : JURI::base() . $item->category_image);
				$marker->stepid_title = $item->stepid_title;
				$marker->stepid_color = $item->stepid_color;
				$marker->poitype = 'poi';

				$markers[] = $marker;
			}

			//get brands
			JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
			$brandsModel = JModelLegacy::getInstance( 'Brands', 'CitybrandingModel', array('ignore_request' => true) );
			$items = $brandsModel->getItems();
			foreach ($items as $item) {
				//global brands should not be displayed on map
				if($item->is_global == 0)
				{
                    $marker = new StdClass();
                    $marker->id = $item->id;
                    $marker->state = $item->state;
                    $marker->moderation = $item->moderation;
                    $marker->title = $item->title;
                    $marker->latitude = $item->latitude;
                    $marker->longitude = $item->longitude;
                    $marker->poitype = 'brand';
                    $markers[] = $marker;
				}
			}

			echo new JResponseJson($markers);
		}
		catch(Exception $e)	{
			echo new JResponseJson($e);
		}
	}	
}

