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
        var container = document.querySelector('.masonry');
        var msnry = new Masonry( container, {
          // options
          //columnWidth: 70,
          itemSelector: '.masonry-element'
        });

        imagesLoaded( container, function() {
          msnry.layout();
        });
    });
</script>

<div id="columns">
    <div class="row masonry" id="masonry-sample">
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
                
            <div class="col-sm-6 col-md-4 col-xs-12 masonry-element">
                <div id="citybranding-panel-<?php echo $item->id;?>" class="panel panel-default">
                    <?php if (JFactory::getUser()->id == $item->created_by) : ?>  
                      <div class="ribbon-wrapper-corner"><div class="ribbon-corner"><?php echo JText::_('COM_CITYBRANDING_POIS_MY_POI');?></div></div>
                    <?php else : ?>
                        <?php if($item->votes > 0) : ?>
                        <div title="<?php echo JText::_('COM_CITYBRANDING_POIS_VOTES');?>" class="book-ribbon">
                            <div>+<?php echo $item->votes; ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php //show photo if any
                        $i = 0;
                        if(isset($attachments->files)){
                            foreach ($attachments->files as $file) {
                                if (isset($file->thumbnailUrl)){
                                    echo '<div class="panel-thumbnail">'. "\n";
                                    echo '<a href="'. JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id).'">';
                                    echo '<img src="'.$attachments->imagedir .'/'. $attachments->id . '/medium/' . ($attachments->files[$i]->name) .'" alt="poi photo" class="img-responsive" />' . "\n";
                                    echo '</a>';
                                    echo '</div>'. "\n";
                                    break;
                                }  
                                $i++;  
                            }
                        }
                    ?>

                    <div class="<?php echo ($item->moderation == 1 ? 'poi-unmoderated ' : ''); ?>panel-body">
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
                            <?php /*uncomment if you like to display a lock icon */
                              /*if (isset($item->checked_out) && $item->checked_out) : ?>
                              <i class="icon-lock"></i> <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'pois.', $canCheckin); ?>
                            <?php endif; */ ?>
                        </p>

                        <?php if($item->updated == $item->created) : ?>
                        <span class="label label-default" title="<?php echo JText::_('COM_CITYBRANDING_POIS_CREATED');?>"><?php echo CitybrandingFrontendHelper::getRelativeTime($item->created); ?></span>
                        <?php else : ?>
                        <span class="label label-info" title="<?php echo JText::_('COM_CITYBRANDING_POIS_UPDATED');?>"><?php echo CitybrandingFrontendHelper::getRelativeTime($item->updated); ?></span>
                        <?php endif; ?>
                        <span class="label label-info" style="background-color: <?php echo $item->stepid_color;?>" title="<?php echo JText::_('COM_CITYBRANDING_POIS_STEPID');?>"><?php echo $item->stepid_title; ?></span>
                        <span class="label label-default" title="<?php echo JText::_('COM_CITYBRANDING_POIS_CATID');?>"><?php echo $item->catid_title; ?></span>
                        <br /><span class="label label-default" title="<?php echo JText::_('COM_CITYBRANDING_TITLE_COMMENTS');?>"><i class="icon-comment"></i> 0</span>
                        <?php if (JFactory::getUser()->id == $item->created_by && $item->votes > 0) : ?>
                        <span class="label label-default" title="<?php echo JText::_('COM_CITYBRANDING_POIS_VOTES');?>">+<?php echo $item->votes; ?></span>
                        <?php endif; ?>

                        <p><?php echo CitybrandingFrontendHelper::cutString($item->description, 200); ?></p>

                        <p><a href="<?php echo JRoute::_('index.php?option=com_citybranding&view=poi&id='.(int) $item->id); ?>"><?php echo JText::_('COM_CITYBRANDING_POIS_MORE');?></a></p>
                        <?php if($item->moderation == 1) : ?>
                            <hr />
                            <p class="citybranding-warning"><i class="icon-info-sign"></i> <?php echo JText::_('COM_CITYBRANDING_POIS_NOT_YET_PUBLISHED');?></p>
                        <?php endif; ?>
                        <?php if (!$canEditOnStatus && JFactory::getUser()->id == $item->created_by) : ?>
                            <p class="citybranding-info"><i class="icon-info-sign"></i> <?php echo JText::_('COM_CITYBRANDING_POI_CANNOT_EDIT_ANYMORE'); ?></p>
                        <?php endif; ?>                        
                    </div>
                </div><!-- /citybranding-panel-X -->
            </div><!--/col--> 
        <?php endforeach; ?>
    </div>
</div><!-- /columns -->

<?php echo $this->pagination->getListFooter(); ?>