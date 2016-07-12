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

$canEdit = $user->authorise('core.edit', 'com_citybranding');
$canDelete = $user->authorise('core.delete', 'com_citybranding');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.min.js"></script>
<script src="<?php echo  JURI::root(true) . '/components/com_citybranding/assets/js/imagesloaded.pkgd.min.js'; ?>"></script>

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
                    <?php /* if (JFactory::getUser()->id == $item->created_by) : ?>
                      <div class="ribbon-wrapper-corner"><div class="ribbon-corner"><?php echo JText::_('COM_CITYBRANDING_POIS_MY_POI');?></div></div>
                    <?php endif; */
                    ?>

                    <?php //get photo if any

                        $img = null;
                        $i = 0;
                        if(isset($attachments->files)){
                            foreach ($attachments->files as $file) {
                                if (isset($file->thumbnailUrl)){
                                    $img['src']  = $attachments->imagedir .'/'. $attachments->id . '/medium/' . ($attachments->files[$i]->name);
                                    $img['link'] = JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id);
                                    break; //on first photo break
                                }
                                $i++;
                            }
                        }

                        if (empty($attachments->files))
                        {
                            $img['src']  = JURI::root(true) . '/components/com_citybranding/assets/images/image-placeholder-1.png';
                            $img['link'] = JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id);
                        }
                    ?>
                    <?php if (!is_null($img)) : ?>
                    <div class="crop-height">
                        <a href="<?php echo $img['link'];?>">
                            <img class="scale" src="<?php echo $img['src'];?>" alt="POI photo" />
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="cb-category-icon">
                        <?php if($item->category_image != '') : ?>
                            <img
                                title="<?php echo JText::_(CitybrandingFrontendHelper::getCategoryNameByCategoryId($item->catid));?>"
                                src="<?php echo $item->category_image; ?>" alt="category symbol"
                            />
                        <?php endif; ?>
                    </div>

                    <div class="<?php echo ($item->moderation == 1 ? 'poi-unmoderated ' : ''); ?>citybranding-panel-body">
                        <span class="lead">
                            <div class="cb-classification-icon2">
                                <?php $item->classifications = explode(',',$item->classifications);?>
                                <?php foreach ($item->classifications as $classification) : ?>
                                    <i
                                        title="<?php echo JText::_(CitybrandingFrontendHelper::getClassificationTitleById($classification)); ?>"
                                        class="icon <?php echo CitybrandingFrontendHelper::getClassificationById($classification); ?>"
                                    ></i>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($canEdit && $canEditOnStatus) : ?>
                              <a href="<?php echo JRoute::_('index.php?option=com_citybranding&task=poi.edit&id='.(int) $item->id); ?>">
                              <i class="icon-edit"></i> <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id); ?>">
                                <?php echo $this->escape($item->title); ?>
                                </a>
                            <?php endif; ?>
                        </span>

                        <?php //echo CitybrandingFrontendHelper::cutString($item->description, 200); ?>

                        <?php /*
                        <p><a href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id); ?>"><?php echo JText::_('COM_CITYBRANDING_POIS_MORE');?></a></p>
                        <?php if($item->moderation == 1) : ?>
                            <hr />
                            <p class="imc-warning"><i class="icon-info-sign"></i> <?php echo JText::_('COM_CITYBRANDING_POIS_NOT_YET_PUBLISHED');?></p>
                        <?php endif; ?>
                        <?php if (!$canEditOnStatus && JFactory::getUser()->id == $item->created_by) : ?>
                            <p class="imc-info"><i class="icon-info-sign"></i> <?php echo JText::_('COM_CITYBRANDING_POI_CANNOT_EDIT_ANYMORE'); ?></p>
                        <?php endif; ?>
                        */ ?>

                    </div>

                </div>
            </div>
        <?php endforeach; ?>

</div> <!-- grid -->

<?php echo $this->pagination->getListFooter(); ?>