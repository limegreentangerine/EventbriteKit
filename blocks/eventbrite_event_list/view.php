<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<section id="<?php echo $bID; ?>" class="block__eventbrite-event-list">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php if ($title) { ?>
                    <<?php echo $titleFormat; ?>><?php echo $title; ?></<?php echo $titleFormat; ?>>
                <?php } ?>
                <?php if ($content) { ?>
                    <p><?php echo $content; ?></p>
                <?php } ?>
            </div>
        </div>
    </div> 
    <?php if (count($events) > 0) {
        \View::element('slideshow', [
            'identifier' => $bID,
            'spacing' => 0,
            'mobileSpacing' => 10,
            'tabletSpacing' => 12.5,
            'desktopSpacing' => 12.5,
            'offset' => 150,
            'autoplay' => false,
            'autoplaySpeed' => 0,
            'mobile' => 1,
            'tablet' => 1,
            'desktop' => 3,
            'buttonType' => 'hidden',
            'dots' => false,
            'viewTemplate' => 'view',
            'items' => $events,
            'containerBleed' => '.container>.row>.col-12',
        ]);
    } ?>
</section>
