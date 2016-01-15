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

$app = JFactory::getApplication();
$params	= $app->getParams();

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_citybranding', JPATH_ADMINISTRATOR);
$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_citybranding.poi.' . $this->item->id);
$canChange = $user->authorise('core.edit.state', 'com_citybranding.poi.' . $this->item->id);
$canEditOwn = $user->authorise('core.edit.own', 'com_citybranding.poi.' . $this->item->id);

if (!$canEdit && $user->authorise('core.edit.own', 'com_citybranding.poi.' . $this->item->id)) {
	$canEdit = $user->id == $this->item->created_by;
}

//Edit Own only if poi status is the initial one
$firstStep = CitybrandingFrontendHelper::getStepByStepId($this->item->stepid);
$canEditOnStatus = true;
//if ($firstStep['ordering'] != 1){
//    $canEditOnStatus = false;
//}

$photos = json_decode($this->item->photo);
$i=0;
foreach ($photos->files as $photo) {
	if(!isset($photo->thumbnailUrl))
		unset($photos->files[$i]);
	$i++;
}
$attachments = json_decode($this->item->photo);
$i=0;
foreach ($attachments->files as $attachment) {
	if(isset($attachment->thumbnailUrl))
		unset($attachments->files[$i]);
	$i++;
}
?>

<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
		js('#gallery').photobox('a', { thumbs:true, loop:false }, callback);
		// using setTimeout to make sure all images were in the DOM, before the history.load() function is looking them up to match the url hash
		setTimeout(window._photobox.history.load, 2000);
		function callback(){
			//console.log('callback for loaded content:', this);
		};

		var grid = js('.grid').masonry({
			// set itemSelector so .grid-sizer is not used in layout
			itemSelector: '.grid-item',
			// use element for option
			columnWidth: '.grid-sizer',
			/*gutter: '.gutter-sizer',*/
			percentPosition: true
		});
		//grid.masonry('layout');
		grid.imagesLoaded().progress( function() {
			grid.masonry('layout');
		});
    });
</script>

<?php if (!$this->item || ($this->item->state != 1 && !$canEditOwn ) ) : ?>
	<div class="alert alert-danger">
		<?php echo JText::_('COM_CITYBRANDING_ITEM_NOT_LOADED'); ?>
	</div>
<?php return; endif; ?>

<?php if($canEdit /*&& $this->item->checked_out == 0*/ && $canEditOnStatus && $this->item->poitype == 1): ?>
	<a class="button special" href="<?php echo JRoute::_('index.php?option=com_citybranding&task=poi.edit&id='.$this->item->id); ?>"><i class="fa fa-pencil"></i> <?php echo JText::_("COM_CITYBRANDING_EDIT_ITEM"); ?> your brand</a>
<?php endif; ?>

<header class="citybranding-poi-title">
	<h4><?php echo $this->item->title; ?></h4>
	<p><span class="icon cb-location"></span> <?php echo $this->item->address;?><br />
		<span class="icon cb-pushpin"></span> <?php echo $this->item->catid_title; ?>
	</p>

</header>

<?php /*if(!empty($attachments->files)) : ?>
	<div id="attachments">
		<div class="citybranding-poi-subtitle"><?php echo JText::_('COM_CITYBRANDING_POI_ATTACHMENTS'); ?></div>
		<?php foreach ($attachments->files as $attachment) : ?>
			<ul>
			<li><a href="<?php echo $attachment->url; ?>"><?php echo $attachment->name; ?></a></li>
			</ul>
		<?php endforeach ?>
	</div>
<?php endif; */ ?>


<?php
$dom = JURI::root(true) . '/components/com_citybranding/assets/pannellum-2.1.1/src/pannellum.htm';
$pan = '?panorama=' . JURI::root(true) . '/images/panorama/examplepano.jpg';
$arg = '&amp;title='.htmlspecialchars($this->item->title);
$preview = '&amp;preview=' . JURI::root(true) . '/images/panorama/examplepano-preview.jpg';

$src = $dom.$pan.$arg.$preview;
?>

<div id="poi-wrapper">
	<div class="grid">

		<!-- width of .grid-sizer used for columnWidth -->
		<div class="grid-sizer"></div>
		<div class="gutter-sizer"></div>

		<?php if($this->item->id == 4 && false) : //testing ?>
		<div class="grid-item grid-item--width100">
			<iframe title="pannellum panorama viewer 1"
					width="100%"
					height="300px"
					webkitAllowFullScreen
					mozallowfullscreen
					allowFullScreen
					style="border-style:none;margin:0;"
					src="<?php echo $src;?>">
			</iframe>
		</div>
		<?php endif; ?>

		<?php if(!empty($photos->files) && file_exists($photos->imagedir .'/'. $photos->id . '/thumbnail/' . (@$photos->files[0]->name))) : ?>

		<div id='gallery'>
			<?php
			$index = 1;
			$count = count($photos->files);
			?>

			<?php foreach ($photos->files as $photo) : ?>

			<div class="grid-item<?php echo $index == 1 ? ' grid-item--width2': ''; ?><?php echo $count == 1 ? ' grid-item--width100': ''; ?>">
				<a href="<?php echo $photos->imagedir .'/'. $photos->id . '/' . ($photo->name) ;?>" class="image fit">
					<?php if($index == 1) : ?>
						<img src="<?php echo $photos->imagedir .'/'. $photos->id . ($index == 1 ? '/' : '/medium/') . ($photo->name) ;?>" alt="<?php echo JText::_('COM_CITYBRANDING_POIS_PHOTO') . ' '. $index;?>" />
					<?php else :?>
						<img src="<?php echo $photos->imagedir .'/'. $photos->id . '/medium/' . ($photo->name) ;?>" alt="<?php echo JText::_('COM_CITYBRANDING_POIS_PHOTO') . ' '. $index;?>" />
					<?php endif; ?>
				</a>
				<?php $index++;?>
			</div>

			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
</div>

<p></p>
<p><?php echo $this->item->description; ?></p>

<?php if( $this->item->poitype == 0) : ?>
	<h4>Brands relative or close to the <?php echo $this->item->title;?> (up to <?php echo $params->get('radiusMeters'); ?> meters)</h4>
	<?php
		$relativeBrands = CitybrandingFrontendHelper::getRelativePois($this->item->latitude, $this->item->longitude, $params->get('radiusMeters') * 0.000621371192);
	?>

	<?php if(!empty($relativeBrands)) : ?>
		<?php foreach ($relativeBrands as $rBrand) : ?>
			<h5>
				<a href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $rBrand['id']);?>">
					<?php echo $rBrand['title'];?>
					(<i class="fa fa-tachometer"></i> <?php echo round($rBrand['distance']*1609.344) ;?> meters)
				</a>
			</h5>
		<?php endforeach; ?>

	<?php else : ?>
		<div class="alert alert-info"><h5 style="text-align: center;">None yet. Help populate the catalog by adding your brand!!</h5></div>
	<?php endif; ?>


<?php endif ?>

<div style="height: 10em;"></div>