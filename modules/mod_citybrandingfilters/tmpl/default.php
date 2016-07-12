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

//get state of Pois model
$poisModel = JModelLegacy::getInstance( 'Pois', 'CitybrandingModel', array('ignore_request' => false) );
$state = $poisModel->getState();

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$app = JFactory::getApplication();
$search = $app->getUserStateFromRequest('com_citybranding.pois.filter.search', 'filter_search');
//echo '<h1>term:'.$search.'</h1>';

$owned = $app->getUserStateFromRequest('com_citybranding.pois.filter.owned', 'filter_owned');
$cat = $app->getUserStateFromRequest('com_citybranding.pois.filter.category', 'cat', array());
$classifications = $app->getUserStateFromRequest('com_citybranding.pois.filter.classifications', 'classifications', array());

$jinput = $app->input;
$option = $jinput->get('option', null);
$view = $jinput->get('view', null);
$id = $jinput->get('id', null);
?>
<script src="https://cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.10/jquery.popupoverlay.js"></script>

<!--<script src="--><?php //echo JURI::base() . '/modules/mod_citybrandingfilters/assets/js/script.js'; ?><!--"></script>-->
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function() {
		js('#cb-modal').popup(
			{
				color: '#1D232A',
				opacity: 1,
				transition: '0.3s',
				scrolllock: true,
				backgroundactive:false

			}
		);
		js("#searchclear").click(function(){
			js("#filter_search").val('');
		});

		js('#selectAllCategories').click(function(event) {
			js(':checkbox[name="cat[]"]').prop('checked', this.checked);
		});

		js('#selectAllClassifications').click(function(event) {
			js(':checkbox[name="cla[]"]').prop('checked', this.checked);
		});
	});

	//show markers according to filtering
	function show(category) {
		// == check the checkbox ==
		document.getElementById('cat-'+category).checked = true;
	}

	function hide(category) {
		// == clear the checkbox ==
		document.getElementById('cat-'+category).checked = false;
	}

	//--- non recursive since IE cannot handle it (doh!!)
	//TODO: replace with jQuery
	function citybranding_filterbox_click(box, category) {
		if (box.checked) {
			show(category);
		} else {
			hide(category);
		}
		var com = box.getAttribute('path');
		var arr = new Array();
		arr = document.getElementsByName('cat[]');
		for(var i = 0; i < arr.length; i++)
		{
			var obj = document.getElementsByName('cat[]').item(i);
			var c = obj.id.substr(4, obj.id.length);

			var path = obj.getAttribute('path');
			if(com == path.substring(0,com.length)){
				if (box.checked) {
					obj.checked = true;
					show(c);
				} else {
					obj.checked = false;
					hide(c);
				}
			}
		}
		return false;
	}
</script>

<div class="citybranding_filters_buttons">
	<?php if ($option == 'com_citybranding' && $view != 'pois') : ?>
<!--		<span class="citybranding_btn_left">
			<a href="<?php /*echo JRoute::_('index.php?option=com_citybranding', false, 2); */?>" class="button"><i class="fa fa-arrow-circle-left"></i> <?php /*echo JText::_('MOD_CITYBRANDINGFILTERS_RETURN_TO_POIS'); */?></a>
		</span>
-->
		<span class="citybranding_btn_left">
			<a href="javascript:window.history.back();" class="button"><i class="fa fa-arrow-circle-left"></i> <?php echo JText::_('MOD_CITYBRANDINGFILTERS_BACK'); ?></a>
		</span>

	<?php else : ?>
		<div class="citybranding_btn_left">
			<a id="search_btn" href="#cb-modal" role="button" class="button special cb-modal_open" ><i class="fa fa-search"></i> <?php echo JText::_('MOD_CITYBRANDINGFILTERS_SEARCH'); ?></a>
			<?php /*
			<div class="btn-group">
			  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				<?php echo JText::_('MOD_CITYBRANDINGFILTERS_ORDERING'); ?> <span class="caret"></span>
			  </button>
			  <ul class="dropdown-menu" role="menu">
				<li><?php echo JHtml::_('grid.sort',  'COM_CITYBRANDING_POIS_TITLE', 'a.title', $listDirn, $listOrder); ?></li>
				<li><?php echo JHtml::_('grid.sort',  'COM_CITYBRANDING_POIS_STEPID', 'a.stepid', $listDirn, $listOrder); ?></li>
				<li><?php echo JHtml::_('grid.sort',  'JDATE', 'a.updated', $listDirn, $listOrder); ?></li>
			  </ul>
			</div>

			<div class="btn-group">
			  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				<?php echo JText::_('MOD_CITYBRANDINGFILTERS_DISPLAY'); ?> <span class="caret"></span>
			  </button>
			  <ul class="dropdown-menu" role="menu">
				<?php echo ModCitybrandingfiltersHelper::createLimitBox($state->get('list.limit')); ?>
			  </ul>
			</div>
			<?php if($params->get('show_help') == 1) : ?>
				<a id="help_btn" href="<?php echo $params->get('help_link'); ?>" role="button" class="btn btn-default"><i class="icon-help"></i> <?php echo JText::_('MOD_CITYBRANDINGFILTERS_HELP'); ?></a>
			<?php endif; ?>
			*/ ?>
		</div>
		<?php if(true) : //only logged? ?>
		<div class="citybranding_btn_right">
			<a id="addnew_btn" href="new-brand" role="button" class="button" ><i class="fa fa-plus-circle"></i> <?php echo JText::_('MOD_CITYBRANDINGFILTERS_ADD_ITEM'); ?></a>
		</div>
		<?php endif; ?>

	<?php endif; ?>

</div>

<!-- Modal -->
<div id="cb-modal" style="width:70%;" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
	<form action="<?php echo JRoute::_('index.php?option=com_citybranding&view=pois'); ?>" method="get" name="citybranding_filter_form" id="adminForm">
		<div class="cb-modal-content">
			<h3 id="searchModalLabel"><?php echo JText::_('MOD_CITYBRANDINGFILTERS_SEARCH'); ?></h3>
			<div class="cb-modal-body">

				<div class="row uniform">
					<div class="10u">
						<input id="filter_search" placeholder="<?php echo JText::_('MOD_CITYBRANDINGFILTERS_SEARCH_PLACEHOLDER');?>" name="filter_search" type="text" value="<?php echo $search; ?>" />
					</div>
					<div class="2u$">
						<a href="javascript:void(0);" id="searchclear" class="button"><i class="fa fa-times-circle"></i> <?php echo JText::_('MOD_CITYBRANDINGFILTERS_CLEAR');?></a>
					</div>
				</div>

<!--				<?php /*if (JFactory::getUser()->id > 0) : */?>
					<br />
					<input type="hidden" id="filter_owned_hidden" name="filter_owned" value="no" />
					<input type="checkbox" id="filter_owned" name="filter_owned" value="yes" <?php /*echo ($owned == 'yes' ? 'checked="checked"' : ''); */?> />
					<label for="filter_owned"><?php /*echo JText::_('MOD_CITYBRANDINGFILTERS_SHOW_MINE');*/?></label>
				--><?php /*endif; */?>
				<hr />
				<h4>
					<input type="checkbox" checked="checked" id="selectAllCategories">
					<label for="selectAllCategories"><?php echo JText::_('MOD_CITYBRANDINGFILTERS_CATEGORIES');?></label>
				</h4>
				<?php $category_filters = ModCitybrandingfiltersHelper::getCategoryFilters(); ?>

				<div class="row">

					<?php foreach ($category_filters as $filter) : ?>
						<div class="4u">
							<?php echo JText::_($filter); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<hr />

				<h4>
					<input type="checkbox" checked="checked" id="selectAllClassifications">
					<label for="selectAllClassifications"><?php echo JText::_('MOD_CITYBRANDINGFILTERS_CLASSIFICATIONS');?></label>
				</h4>
				<?php $classification_filters = ModCitybrandingfiltersHelper::getClassificationFilters(); ?>

				<div class="row">
					<?php foreach ($classification_filters as $filter) : ?>
						<div class="4u">
							<?php echo $filter; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<hr />

			</div>
			<div class="modal-footer">
				<button id="cancel_filters" class="cb-modal_close button" aria-hidden="true"><?php echo JText::_('JCANCEL');?></button>
				<button type="submit" id="apply_filters" class="fade_open button special"><?php echo JText::_('MOD_CITYBRANDINGFILTERS_APPLY');?></button>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="limit" value="<?php echo $state->get('list.limit');?>" />
		<input type="hidden" name="limitstart" value="<?php echo $state->get('list.start');?>" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
