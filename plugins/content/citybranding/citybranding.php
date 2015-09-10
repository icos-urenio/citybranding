<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');

class plgContentcitybranding extends JPlugin
{
	function plgContentCitybranding(& $subject, $config)
	{
		parent::__construct( $subject, $config);
		if($this->params===false)
		{
			$this->_plugin = JPluginHelper::getPlugin( 'content', 'content_citybranding' );
			$this->params = new JParameter( $jPlugin->params);
		}

	}

	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		$name = $form->getName();
		//if (!in_array($name, array('com_content.article','com_categories.categorycom_content'))) {
		if (!in_array($name, array('com_categories.categorycom_citybranding'))) {
			return true;
		}
		$s = "
		   	jQuery(document).ready(function() {
		   		jQuery('#jform_params_citybranding_category_emails-lbl') // Replace this selector with one suitable for you
		   		  .append('<br /><button class=\"btn btn-success btn-small\"><i class=\"icon-refresh\"></i> emails</button>') // Create the element

		   		  .click(function(e){ extractMailFromGroup('".JSession::getFormToken()."'); e.preventDefault(); }); // Add a click handler
		   	});
		";
		JFactory::getDocument()->addScript(JURI::root().'plugins/content/citybranding/assets/js/citybranding_content_script.js');
		JFactory::getDocument()->addScriptDeclaration($s);

		JForm::addFormPath(dirname(__FILE__).'/category_fields');
		$form->loadFile('citybranding', false);

		return true;
	}

}
