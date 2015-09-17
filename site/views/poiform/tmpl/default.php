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
//TODO: Set this on settings
$firstStep = CitybrandingFrontendHelper::getStepByStepId($this->item->stepid);
?>
<?php if ($firstStep['ordering'] != 1 && isset($this->item->id) && $this->item->id > 0) :?>
	<div class="alert alert-danger"><?php echo JText::_('COM_CITYBRANDING_POI_CANNOT_EDIT_ANYMORE'); ?></div>
	<?php return; ?>	
<?php endif; ?>

<?php 
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

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
	$canState = JFactory::getUser()->authorise('core.edit.state','com_citybranding.poi');
} else {
	$canState = JFactory::getUser()->authorise('core.edit.state','com_citybranding.poi.'.$this->item->id);
}

JFormHelper::addFieldPath(JPATH_ROOT . '/components/com_citybranding/models/fields');
$steps = JFormHelper::loadFieldType('Step', false);
$options = $steps->getOptions();
$default_stepId = $options[0]->value;

//TODO: Add business logic here
if(!$canState) {
	$this->form->setFieldAttribute( 'stepid', 'readonly', 'true' );
	$this->form->setFieldAttribute( 'access', 'disabled', 'disabled' );
}
if(!$canState && $this->item->id > 0) {
	$this->form->setFieldAttribute( 'catid', 'readonly', 'true' );
}
?>

<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.citybrandingid').css('display','none');
            jQuery('.citybrandingaccess').css('display','none');
            jQuery('.citybrandinglanguage').css('display','none');

            jQuery('#form-poi').submit(function(event) {
                jQuery('input[name="task"]').val('poiform.save');
            });

			jQuery('input:hidden.stepid').each(function(){
				var name = jQuery(this).attr('name');
				if(name.indexOf('stepidhidden')){
					jQuery('#jform_stepid option[value="' + jQuery(this).val() + '"]').attr('selected', 'selected');
				}
			});

			jQuery("#jform_stepid").trigger("liszt:updated");
        });
    
    
</script>

<div class="container">
<div class="row">
<div class="poi-edit front-end-edit">
    <form id="form-poi" action="<?php echo JRoute::_('index.php?option=com_citybranding&task=poi.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <div style="float:right;">
        <button type="submit" class="validate btn btn-primary"><i class="icon-ok"></i> <?php echo JText::_('JSUBMIT'); ?></button>
        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_citybranding&task=poiform.cancel'); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
    </div>

    <?php if (!empty($this->item->id)): ?>
        <h1><i class="icon-pencil"></i> <?php echo JText::_('COM_CITYBRANDING_POI_EDIT'); ?> #<?php echo $this->item->id; ?></h1>
    <?php else: ?>
        <h1><i class="icon-plus-sign"></i> <?php echo JText::_('COM_CITYBRANDING_POI_NEW'); ?></h1>
    <?php endif; ?>
    <hr class="citybranding-form-hr" />

    <div class="citybranding-dates">
		<i class="icon-user"></i> <?php echo (strlen($this->form->getInput('created_by'))>1 ? $this->form->getInput('created_by') : 'Guest'); ?>
		<i class="icon-calendar"></i> <?php echo $this->form->getInput('created'); ?>
		<?php echo (strlen($this->form->getInput('updated')) > 4 ? '<i class="icon-pencil"></i> '.$this->form->getInput('updated') : ''); ?>
	
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
	</div>
	
	<div class="control-group citybrandingid">
		<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
	</div>	

	<?php if (!empty($this->item->id)): /*existing*/?> 
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('regnum'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('regnum'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('regdate'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('regdate'); ?></div>
	</div>	
	
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('stepid'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('stepid'); ?></div>
		<?php foreach((array)$this->item->stepid as $value): ?>
			<?php if(!is_array($value)): ?>
				<input type="hidden" class="stepid" name="jform[stepidhidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php else : /*new*/?>
		<input type="hidden" name="jform[stepid]" value="<?php echo $default_stepId; ?>" />
		<input type="hidden" class="stepid" name="jform[stepidhidden][<?php echo $default_stepId; ?>]" value="<?php echo $default_stepId; ?>" />
	<?php endif; ?>

	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
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
	<div class="control-group">
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
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('hits'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('hits'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('note'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('note'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('votes'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('votes'); ?></div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('modality'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('modality'); ?></div>
	</div>				

	<?php /* TODO: check this on settings */ ?>
	<?php /*
	<div class="control-group">
		<?php if(!$canState): ?>
			<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
			<div class="controls"><?php echo $state_string; ?></div>
			<input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
		<?php else: ?>
			<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
		<?php endif; ?>
	</div>
	*/ ?>


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
	           document.getElementById("form-poi").appendChild(input);
	        });
	    </script>
	<?php endif; ?>


    
    <input type="hidden" name="option" value="com_citybranding" />
    <input type="hidden" name="task" value="poiform.save" />
    <?php echo JHtml::_('form.token'); ?>

    </form>
</div>
</div>
</div> <!-- /container -->