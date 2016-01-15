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

// Check for component
if (!JComponentHelper::getComponent('com_citybranding', true)->enabled)
{
	echo '<div class="alert alert-danger">City Branding component is not enabled</div>';
	return;
}

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';
//JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base() . '/modules/mod_citybrandingmap/assets/css/style.css');

//get parameters
$com_citybranding_params = JComponentHelper::getParams('com_citybranding');	
$api_key 	= $com_citybranding_params->get('api_key');
$lat        = $com_citybranding_params->get('latitude');
$lng        = $com_citybranding_params->get('longitude');
$zoom 	    = $com_citybranding_params->get('zoom');
$language   = $com_citybranding_params->get('maplanguage');
$clusterer 	= ($com_citybranding_params->get('clusterer') == 1 ? true : false);
$scrollwheel = ($com_citybranding_params->get('scrollwheel') == 1 ? true : false);

if($api_key == ''){
	echo '<span style="color: red; font-weight:bold;">Module CITYBRANDING Map :: Google Maps API KEY missing</span>';
	$doc->addScript('https://maps.googleapis.com/maps/api/js?language='.$language);
}
else{
	$doc->addScript('https://maps.googleapis.com/maps/api/js?key='.$api_key.'&language='.$language);
}

//clusterer
if($clusterer){
	$doc->addScript('http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js');
}

$jinput = JFactory::getApplication()->input;
$option = $jinput->get('option', null);
$view = $jinput->get('view', null);

if ($option == 'com_citybranding' && $view == 'poiform')
{
	return;
}


?>

<script type="text/javascript">
	var lat = <?php echo $lat;?> ;
	var lng = <?php echo $lng;?> ;
	var zoom = <?php echo $zoom;?> ;
	var clusterer = "<?php echo $clusterer;?>" ;
	var language = "<?php echo $language;?>" ;
	var linkToPoi = "<?php echo JRoute::_('index.php?option=com_citybranding&view=poi'); ?>";
	var linkToBrand = "<?php echo JRoute::_('index.php?option=com_citybranding&view=brand'); ?>";
</script>
<?php if ($option == 'com_citybranding' && $view == 'pois') : ?>
	<script src="<?php echo JURI::base();?>modules/mod_citybrandingmap/assets/js/script.js" type="text/javascript"></script>
<?php elseif ($option == 'com_citybranding' && $view == 'poi') : ?>
	<?php
	$id = $jinput->get('id', -1);
	$poiModel = JModelLegacy::getInstance( 'Poi', 'CitybrandingModel', array('ignore_request' => true) );
	$data = $poiModel->getData($id);

	$poiLat = $data->latitude;
	$poiLng = $data->longitude;
	$poiIcon = ($data->category_image == '' ? '' : JURI::base() . $data->category_image);
	$poiAddress = $data->address;
	$poiTitle = $data->title;
	?>
	<script type="text/javascript">
		var poiLat = "<?php echo $poiLat;?>" ;
		var poiLng = "<?php echo $poiLng;?>" ;
		var poiIcon = "<?php echo $poiIcon;?>" ;
		var poiAddress = "<?php echo $poiAddress;?>" ;
		var poiTitle = "<?php echo $poiTitle;?>" ;
	</script>
	<script src="<?php echo JURI::base();?>modules/mod_citybrandingmap/assets/js/single.js" type="text/javascript"></script>
<?php elseif ($option == 'com_citybranding' && $view == 'brand') : ?>
	<?php
	$id = $jinput->get('id', -1);
	$brandModel = JModelLegacy::getInstance( 'Brand', 'CitybrandingModel', array('ignore_request' => true) );
	$data = $brandModel->getData($id);

	$poiLat = $data->latitude;
	$poiLng = $data->longitude;
	$poiIcon = ''; //($data->category_image == '' ? '' : JURI::base() . $data->category_image);
	$poiAddress = $data->address;
	$poiTitle = $data->title;
	?>
	<script type="text/javascript">
		var poiLat = "<?php echo $poiLat;?>" ;
		var poiLng = "<?php echo $poiLng;?>" ;
		var poiIcon = "<?php echo $poiIcon;?>" ;
		var poiAddress = "<?php echo $poiAddress;?>" ;
		var poiTitle = "<?php echo $poiTitle;?>" ;
	</script>
	<script src="<?php echo JURI::base();?>modules/mod_citybrandingmap/assets/js/single.js" type="text/javascript"></script>
<?php endif; ?>

<?php
//initialize and load map
$script = array();
$script[] = "jQuery(document).ready(function () {";
$script[] = "  google.maps.event.addDomListener(window, 'load', citybranding_mod_map_initialize);";
$script[] = "});";
$doc->addScriptDeclaration(implode("\n", $script));

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_citybrandingmap', $params->get('layout', 'default'));
