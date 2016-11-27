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
require_once JPATH_COMPONENT_SITE . '/helpers/citybranding.php';

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
//JHtml::_('formbehavior.chosen', 'select');
//include popup overlay
JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_citybranding/assets/js/jquery.popupoverlay.min.js');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_citybranding', JPATH_ADMINISTRATOR);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/components/com_citybranding/assets/js/form.js');
$doc->addStyleSheet(JURI::base() . '/components/com_citybranding/assets/css/form.css');

if($this->item->state == 1){
	$state_string = JText::_('JPUBLISHED');
	$state_value = 1;
} else {
	$state_string = JText::_('JUNPUBLISHED');
	$state_value = 0;
}
if($this->item->id) {
	$canState = JFactory::getUser()->authorise('core.edit.state','com_citybranding.brand');
} else {
	$canState = JFactory::getUser()->authorise('core.edit.state','com_citybranding.brand.'.$this->item->id);
}

JFormHelper::addFieldPath(JPATH_ROOT . '/components/com_citybranding/models/fields');
$steps = JFormHelper::loadFieldType('Step', false);
$options = $steps->getOptions();
$default_stepId = $options[0]->value;

//TODO: Add business logic here
if(!$canState) {
	$this->form->setFieldAttribute( 'access', 'disabled', 'disabled' );
}
?>

<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.citybrandingid').css('display','none');
            jQuery('.citybrandingaccess').css('display','none');
            jQuery('.citybrandinglanguage').css('display','none');

            jQuery('#form-brand').submit(function(event) {
                jQuery('input[name="task"]').val('brandform.save');
            });

        });
</script>

<div class="container">
<div class="row">
<div class="brand-edit front-end-edit">
    <form id="form-brand" action="<?php echo JRoute::_('index.php?option=com_citybranding&task=brand.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

    <?php if (!empty($this->item->id)): ?>
        <h1><i class="icon-pencil"></i> <?php echo JText::_('COM_CITYBRANDING_BRAND_EDIT'); ?> #<?php echo $this->item->id; ?></h1>
    <?php else: ?>
        <h1><i class="icon-plus-sign"></i> <?php echo JText::_('COM_CITYBRANDING_BRAND_NEW'); ?></h1>
    <?php endif; ?>
    <hr class="citybranding-form-hr" />

    <div class="citybranding-dates">
		<i class="fa fa-user"></i> <?php echo (strlen($this->form->getInput('created_by'))>1 ? $this->form->getInput('created_by') : 'Guest'); ?>
		<i class="fa fa-calendar"></i> <?php echo $this->form->getInput('created'); ?>

	</div>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('areaid'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('areaid'); ?></div>
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('is_global'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('is_global'); ?></div>
	</div>

	<div class="control-group citybrandingid">
		<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
	</div>	

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('address'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('address'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('latitude'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('latitude'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('longitude'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('longitude'); ?></div>
	</div>






<!--	    <div class="control-group">
		    <div class="control-label"><?php /*echo $this->form->getLabel('related'); */?></div>
		    <div class="controls"><?php /*echo $this->form->getInput('related'); */?></div>
	    </div>-->

<!--	    <div class="control-group">
		    <div class="control-label"><?php /*echo $this->form->getLabel('classifications'); */?></div>
		    <div class="controls"><?php /*echo $this->form->getInput('classifications'); */?></div>
	    </div>
-->
	<div class="cb-files control-group">
		<div class="control-label"><?php echo $this->form->getLabel('photo'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('photo'); ?></div>
	</div>

	<div class="control-group citybrandingaccess">
		<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
	</div>

	<div class="control-group citybrandinglanguage">
		<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
	</div>


	<div class="fltlft" <?php if (!JFactory::getUser()->authorise('core.admin','citybranding')): ?> style="display:none;" <?php endif; ?> >
        <?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
        <?php echo JHtml::_('sliders.panel', JText::_('ACL Configuration'), 'access-rules'); ?>
        <fieldset class="panelform">
            <?php echo $this->form->getLabel('rules'); ?>
            <?php echo $this->form->getInput('rules'); ?>
        </fieldset>
        <?php echo JHtml::_('sliders.end'); ?>
    </div>
	<?php if (!JFactory::getUser()->authorise('core.admin','citybranding')): ?>
	    <script type="text/javascript">
	        jQuery.noConflict();
	        jQuery('.tab-pane select').each(function(){
	           var option_selected = jQuery(this).find(':selected');
	           var input = document.createElement("input");
	           input.setAttribute("type", "hidden");
	           input.setAttribute("name", jQuery(this).attr('name'));
	           input.setAttribute("value", option_selected.val());
	           document.getElementById("form-brand").appendChild(input);
	        });
	    </script>
	<?php endif; ?>

	<div style="float:right;">
		<a class="button2" href="guide" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
		<button type="submit" class="validate button special"><i class="fa fa-check-circle"></i> <?php echo JText::_('JSUBMIT'); ?></button>
	</div>

    <input type="hidden" name="option" value="com_citybranding" />
    <input type="hidden" name="task" value="brandform.save" />
    <?php echo JHtml::_('form.token'); ?>

    </form>
</div>
</div>
</div> <!-- /container -->