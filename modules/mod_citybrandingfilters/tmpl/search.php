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

$app = JFactory::getApplication();
$search = $app->getUserStateFromRequest('com_citybranding.issues.filter.search', 'filter_search');
$owned = $app->getUserStateFromRequest('com_citybranding.issues.filter.owned', 'filter_owned');
?>

<div class="citybranding_filters_search">
	<form class="form-search form-inline" action="<?php echo JRoute::_('index.php?option=com_citybranding&view=issues'); ?>" method="post" name="citybranding_filter_form" id="citybranding_filter_form">
	    <input type="text" class="input-medium search-query" name="filter_search" value="<?php echo $search; ?>">
		<?php if (JFactory::getUser()->id > 0) : ?>
			<p>
			<input type="hidden" id="filter_owned_hidden" name="filter_owned" value="no" />
		    <label class="checkbox inline">
				<input type="checkbox" id="filter_owned" name="filter_owned" value="yes" <?php echo ($owned == 'yes' ? 'checked="checked"' : ''); ?> > Show only my issues
			</label>
			</p>
		<?php endif; ?>
		<p></p>
	    <p><button type="submit" class="btn"><?php echo JText::_('MOD_CITYBRANDINGFILTERS_SEARCH'); ?> / <?php echo JText::_('MOD_CITYBRANDINGFILTERS_APPLY'); ?></button></p>
	</form>
</div>