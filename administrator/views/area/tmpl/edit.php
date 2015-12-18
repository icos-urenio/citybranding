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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_citybranding/assets/css/citybranding.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
        
    });

    Joomla.submitbutton = function(task)
    {
        if (task == 'area.cancel') {
            Joomla.submitform(task, document.getElementById('area-form'));
        }
        else {
            
            if (task != 'area.cancel' && document.formvalidator.isValid(document.id('area-form'))) {
                
                Joomla.submitform(task, document.getElementById('area-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_citybranding&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="area-form" class="form-validate">

    <div class="form-vertical">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CITYBRANDING_TITLE_AREA', true)); ?>
        <div class="row-fluid">
            <div class="span4 form-vertical">
                <fieldset class="adminform">

		            <div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('parent_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('parent_id'); ?></div>
					</div>					
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('photo'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('photo'); ?></div>
					</div>
						
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
					</div>
					<div style="clear:both;"></div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('areacolor'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('areacolor'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
					</div>




                </fieldset>
            </div>
			<div class="span8 form-vertical">
                <fieldset class="adminform">
					<div class="control-group">
						
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

                </fieldset>	
			</div>            
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
	        <div class="control-group">
	        	<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
	        	<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
	        </div>
	        <div class="control-group">
	        	<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
	        	<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
	        </div>
	        <div class="control-group">
	        	<div class="control-label"><?php echo $this->form->getLabel('updated'); ?></div>
	        	<div class="controls"><?php echo $this->form->getInput('updated'); ?></div>
	        </div>
	        	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	        	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	        <div class="control-group">
	        	<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
	        	<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
	        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php if (JFactory::getUser()->authorise('core.admin','citybranding')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_CITYBRANDING_FIELDSET_RULES', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>