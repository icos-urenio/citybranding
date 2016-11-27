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
$canEdit = $user->authorise('core.edit', 'com_citybranding.brand.' . $this->item->id);
$canChange = $user->authorise('core.edit.state', 'com_citybranding.brand.' . $this->item->id);
$canEditOwn = $user->authorise('core.edit.own', 'com_citybranding.brand.' . $this->item->id);

if (!$canEdit && $user->authorise('core.edit.own', 'com_citybranding.brand.' . $this->item->id)) {
	$canEdit = $user->id == $this->item->created_by;
}

//Edit Own only if brand status is the initial one
//$firstStep = CitybrandingFrontendHelper::getStepByStepId($this->item->stepid);
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.min.js"></script>
<script src="<?php echo  JURI::root(true) . '/components/com_citybranding/assets/js/imagesloaded.pkgd.min.js'; ?>"></script>

<script src="<?php echo JURI::root(true).'/components/com_citybranding/assets/js/jquery.collagePlus.min.js'; ?>"></script>
<script src="<?php echo JURI::root(true).'/components/com_citybranding/assets/js/jquery.removeWhitespace.min.js'; ?>"></script>

<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
		js('#gallery').photobox('a', { thumbs:true, loop:false }, callback);
		// using setTimeout to make sure all images were in the DOM, before the history.load() function is looking them up to match the url hash
		setTimeout(window._photobox.history.load, 2000);
		function callback(){
			//console.log('callback for loaded content:', this);
		};

	    js(window).load(function () {
		    collage();
	    });

		var grid2 = js('.grid2').masonry({
			// set itemSelector so .grid-sizer is not used in layout
			itemSelector: '.grid-item',
			// use element for option
			columnWidth: '.grid-sizer',
			gutter: '.gutter-sizer',
			percentPosition: true
		});
		//grid2.masonry('layout');
		grid2.imagesLoaded().progress( function() {
			grid2.masonry('layout');
		});
    });

    function collage() {
	    js('#gallery').removeWhitespace().collagePlus(
		    {
			    'fadeSpeed' : 2000,
			    'targetHeight': 200,
			    'direction': 'horizontal',
			    'allowPartialLastRow': false,
			    'effect': 'effect-1'
		    }
	    );
    };

    var resizeTimer = null;
    js(window).bind('resize', function() {
	    // hide all the images until we resize them
	    // set the element you are scaling i.e. the first child nodes of ```.Collage``` to opacity 0
	    js('#gallery .Image_Wrapper').css("opacity", 0);
	    // set a timer to re-apply the plugin
	    if (resizeTimer) clearTimeout(resizeTimer);
	    resizeTimer = setTimeout(collage, 200);
    });
</script>

<?php if (!$this->item || ($this->item->state != 1 && !$canEditOwn ) ) : ?>
	<div class="alert alert-danger">
		<?php echo JText::_('COM_CITYBRANDING_ITEM_NOT_LOADED'); ?>
	</div>
<?php return; endif; ?>

<?php if($canEdit /*&& $this->item->checked_out == 0*/ && $canEditOnStatus): ?>
	<a class="button special" href="<?php echo JRoute::_('index.php?option=com_citybranding&task=brand.edit&id='.$this->item->id); ?>"><i class="fa fa-pencil"></i> <?php echo JText::_("COM_CITYBRANDING_EDIT_ITEM"); ?> your brand</a>
<?php endif; ?>

<header class="citybranding-poi-title">
	<h4><?php echo $this->item->title; ?></h4>
	<p><span class="icon cb-location"></span> <?php echo $this->item->address;?><br />
		<!--<span class="icon cb-pushpin"></span> --><?php /*echo $this->item->catid_title; */?>
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

		<?php if(!empty($photos->files) && file_exists($photos->imagedir .'/'. $photos->id . '/thumbnail/' . (@$photos->files[0]->name))) : ?>

		<div id='gallery'>
			<?php
			$index = 1;
			$count = count($photos->files);
			?>

			<?php foreach ($photos->files as $photo) : ?>
				<div class="Image_Wrapper">
				<a href="<?php echo $photos->imagedir .'/'. $photos->id . '/' . ($photo->name) ;?>" class="image fit">
					<img src="<?php echo $photos->imagedir .'/'. $photos->id . '/medium/' . ($photo->name) ;?>" alt="<?php echo JText::_('COM_CITYBRANDING_POIS_PHOTO') . ' '. $index;?>" />
				</a>
				<?php $index++;?>

				</div>

			<?php endforeach; ?>
		</div>
		<?php endif; ?>

</div>

<p></p>
<p><?php echo $this->item->description; ?></p>

<?php
	$relativePois = $this->relativePois;
?>


<?php if(!empty($relativePois)) : ?>
<h4>POIs close to the <?php echo $this->item->title;?> (up to <?php echo $params->get('radiusMeters'); ?> meters)</h4>
<?php endif; ?>
<div class="grid2">
	<!-- width of .grid-sizer used for columnWidth -->
	<div class="grid-sizer"></div>
	<div class="gutter-sizer"></div>

	<?php if(!empty($relativePois)) : ?>



		<?php foreach ($relativePois as $rPoi) : ?>
			<?php $attachments = json_decode($rPoi['photo']); ?>
			<div class="grid-item">
				<div id="citybranding-panel-poi-<?php echo $rPoi['id'];?>" class="citybranding-panel">


					<?php //get photo if any
					$img = null;
					$i = 0;
					if(isset($attachments->files)){
						foreach ($attachments->files as $file) {
							if (isset($file->thumbnailUrl)){
								$img['src']  = $attachments->imagedir .'/'. $attachments->id . '/medium/' . ($attachments->files[$i]->name);
								$img['link'] = JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $rPoi['id']);
								break; //on first photo break
							}
							$i++;
						}
					}
					?>
					<?php if (!is_null($img)) : ?>
                        <div class="panel-thumbnail">
							<a href="<?php echo $img['link'];?>">
								<img class="citybranding-card-img" src="<?php echo $img['src'];?>" alt="POI photo" />
							</a>
						</div>
					<?php endif; ?>

					<div class="cb-category-icon">
						<?php if($rPoi['category_image'] != '') : ?>
							<img src="<?php echo $rPoi['category_image']; ?>" alt="category symbol" />
						<?php endif; ?>
					</div>
					<div class="cb-classification-icon">
						<?php $rPoi['classifications'] = explode(',',$rPoi['classifications']);?>
						<?php foreach ($rPoi['classifications'] as $classification) : ?>
							<i class="icon <?php echo CitybrandingFrontendHelper::getClassificationById($classification); ?>"></i>
						<?php endforeach; ?>
					</div>


					<div class="<?php echo ($rPoi['moderation'] == 1 ? 'poi-unmoderated ' : ''); ?>citybranding-panel-body">
					<span class="lead">

						<?php if ($canEdit && $canEditOnStatus) : ?>
							<a class="citybranding-grid-title" href="<?php echo JRoute::_('index.php?option=com_citybranding&task=poi.edit&id='.(int) $rPoi['id']); ?>">
								<i class="icon-edit"></i> <?php echo $this->escape($rPoi['title']); ?></a>
						<?php else : ?>
							<h5><a class="citybranding-grid-title" href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $rPoi['id']); ?>">
									<?php echo $this->escape($rPoi['title']); ?>
								</a></h5>
						<?php endif; ?>

						<a href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $rPoi['id']);?>">
							(<i class="fa fa-tachometer"></i> <?php echo round($rPoi['distance']*1609.344) ;?> meters)
						</a>

					</span>
					</div>


				</div>
			</div>
		<?php endforeach; ?>

	<?php endif; ?>
</div> <!-- grid2 -->

<div style="height: 10em;"></div>