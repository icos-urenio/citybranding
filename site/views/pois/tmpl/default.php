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

$user = JFactory::getUser();
$userId = $user->get('id');

// $canEdit = $user->authorise('core.edit', 'com_citybranding');
// $canDelete = $user->authorise('core.delete', 'com_citybranding');
?>

<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {

        var grid = js('.grid').masonry({
            // set itemSelector so .grid-sizer is not used in layout
            itemSelector: '.grid-item',
            // use element for option
            columnWidth: '.grid-sizer',
            gutter: '.gutter-sizer',
            percentPosition: true
        });
        //grid.masonry('layout');
        grid.imagesLoaded().progress( function() {
            grid.masonry('layout');
        });

    });
</script>


<div class="grid">
        <!-- width of .grid-sizer used for columnWidth -->
        <div class="grid-sizer"></div>
        <div class="gutter-sizer"></div>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
                $canCreate = $user->authorise('core.create', 'com_citybranding.poi.'.$item->id);
                $canEdit = $user->authorise('core.edit', 'com_citybranding.poi.'.$item->id);
                $canCheckin = $user->authorise('core.manage', 'com_citybranding.poi.'.$item->id);
                $canChange = $user->authorise('core.edit.state', 'com_citybranding.poi.'.$item->id);
                $canDelete = $user->authorise('core.delete', 'com_citybranding.poi.'.$item->id);
                //$canEditOwn = $user->authorise('core.edit.own', 'com_citybranding.poi.' . $item->id);
                $attachments = json_decode($item->photo);
                
                //Edit Own only if poi status is the initial one
                $firstStep = CitybrandingFrontendHelper::getStepByStepId($item->stepid);
                $canEditOnStatus = true;
                if ($firstStep['ordering'] != 1){
                    $canEditOnStatus = false;
                }

            ?>
            <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_citybranding.poi.'.$item->id)): ?>
                <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
            <?php endif; ?>
                
            <div class="grid-item">
                <div id="citybranding-panel-<?php echo $item->id;?>" class="citybranding-panel">
                    <?php if ($item->id == 4) : //testing ?>
                      <div class="ribbon-wrapper-corner"><div class="ribbon-corner">360<sup>o</sup></div></div>
                    <?php endif; ?>
                    
                    <?php //show photo if any
                        $i = 0;
                        if(isset($attachments->files)){
                            foreach ($attachments->files as $file) {
                                if (isset($file->thumbnailUrl)){
                                    echo '<div class="citybranding-panel-thumbnail">'. "\n";
                                    echo '<a href="'. JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id).'" class="image fit">';
                                    echo '<img src="'.$attachments->imagedir .'/'. $attachments->id . '/medium/' . ($attachments->files[$i]->name) .'" alt="poi photo" />' . "\n";
                                    echo '</a>';
                                    echo '</div>'. "\n";
                                    break; //on first photo break
                                }  
                                $i++;  
                            }
                        }
                    ?>

                    <div class="<?php echo ($item->moderation == 1 ? 'poi-unmoderated ' : ''); ?>citybranding-panel-body">
                        <p class="lead">
                            <?php if($item->category_image != '') : ?>
                            <img src="<?php echo $item->category_image; ?>" alt="category image" />
                            <?php endif; ?>
                            <?php if ($canEdit && $canEditOnStatus) : ?>
                              <a href="<?php echo JRoute::_('index.php?option=com_citybranding&task=poi.edit&id='.(int) $item->id); ?>">
                              <i class="icon-edit"></i> <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                              <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                        </p>

                        <?php //echo CitybrandingFrontendHelper::cutString($item->description, 200); ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>

</div> <!-- grid -->

<?php echo $this->pagination->getListFooter(); ?>