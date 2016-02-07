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
JHtml::_('behavior.modal');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_citybranding/assets/css/citybranding.css');
?>
<script type="text/javascript">

    //fixes the "map inside tab" problem
	js = jQuery.noConflict();
    js(document).ready(function() {
	    js('a[data-toggle="tab"]').on('shown', function (e) {
		    if(e.target.hash == '#gmap') {
			    google.maps.event.trigger(map, 'resize');
			    map.setCenter(marker.getPosition());
		    }
	    });
    });



    Joomla.submitbutton = function(task)
    {
        if (task == 'brand.cancel') {
            Joomla.submitform(task, document.getElementById('brand-form'));
        }
        else {
            
            if (task != 'brand.cancel' && document.formvalidator.isValid(document.id('brand-form'))) {
                
                Joomla.submitform(task, document.getElementById('brand-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_citybranding&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="brand-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <div class="form-vertical">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CITYBRANDING_TITLE_BRAND', true)); ?>
        <div class="row-fluid">
            <div class="span6">
                <fieldset class="adminform">
	                <div class="control-group">
		                <div id="class1" style="color: green;"></div>
		                <div class="control-label"><?php echo $this->form->getLabel('tags'); ?></div>
		                <div class="controls"><?php echo $this->form->getInput('tags'); ?></div>
	                </div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('is_global'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('is_global'); ?></div>
					</div>
	                <?php /*
	                <div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('stepid'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('stepid'); ?></div>
					</div>


						foreach((array)$this->item->stepid as $value): 
							if(!is_array($value)):
								echo '<input type="hidden" class="stepid" name="jform[stepidhidden]['.$value.']" value="'.$value.'" />';
							endif;
						endforeach;
					*/?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('areaid'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('areaid'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
					</div>

                </fieldset>
            </div>
            <div class="span6">
                <fieldset class="adminform">
	                <div class="control-group">
		                <div class="control-label"><?php echo $this->form->getLabel('panorama'); ?></div>
		                <div class="controls"><?php echo $this->form->getInput('panorama'); ?></div>
	                </div>
	                <div class="control-group">
		                <div class="control-label"><?php echo $this->form->getLabel('photo'); ?></div>
		                <div class="controls"><?php echo $this->form->getInput('photo'); ?></div>
	                </div>

                </fieldset>
			</div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'gmap', JText::_('COM_CITYBRANDING_SETTINGS_GOOGLE_MAP_LABEL', true)); ?>
        	<div class="span12">
		        <div class="control-group">
			        <div class="control-label"><?php echo $this->form->getLabel('address'); ?></div>
			        <div class="controls"><?php echo $this->form->getInput('address'); ?></div>
			        <?php echo $this->form->getInput('latitude'); ?>
			        <?php echo $this->form->getInput('longitude'); ?>
		        </div>
        	</div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
            <div class="span6">
                <fieldset class="adminform">
	                <div class="control-group">
		                <div class="control-label"><?php echo $this->form->getLabel('moderation'); ?></div>
		                <div class="controls"><?php echo $this->form->getInput('moderation'); ?></div>
	                </div>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('language'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('hits'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('hits'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('votes'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('votes'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('modality'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('modality'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('note'); ?></div>
					</div>
                </fieldset>	
			</div>		
            <div class="span6">
                <fieldset class="adminform">
	                <div class="control-group">
		                <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
		                <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
	                </div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
						<div class="controls"><strong><?php echo $this->form->getInput('created'); ?></strong></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
						<div class="controls"><strong><?php echo $this->form->getInput('created_by'); ?></strong></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('updated'); ?></div>
						<div class="controls"><strong><?php echo $this->form->getInput('updated'); ?></strong></div>
					</div>

					<?php if(!empty($this->item->notification_emails)) : ?>
						<div class="alert alert-info">
							<p><strong><?php echo JText::_('COM_CITYBRANDING_ADMIN_NOTIFICATIONS_RECEIVED_BY');?>:</strong></p>
							<?php 
								foreach ($this->item->notification_emails as $email) {
									echo $email.'<br />';
								}
							?>
						</div>
					<?php endif; ?>
                </fieldset>	
			</div>	
		<?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if (JFactory::getUser()->authorise('core.admin','citybranding')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
<?php echo $this->loadTemplate('print'); ?>
