<?php
/**
 * @version     1.0.0
 * @package     com_citybranding
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.6
 */
class CitybrandingCategories extends JCategories
{
	public function __construct($options = array())
	{
		//echo 'EXTENDS JCATEGORIES';
		$options['table'] = '#__citybranding_issues';
		$options['extension'] = 'com_citybranding';
		parent::__construct($options);
	}
}
