<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<fieldset>
    <div class="form-group">
        <?php echo $form->label('title', t('Title ')); ?>
        <div class="input-group">
            <?php echo $form->text('title', $title ?? null); ?>
            <?php echo $form->select('titleFormat', \Concrete\Core\Block\BlockController::$btTitleFormats, $titleFormat ?? null, ['style' => 'width:105px;flex-grow:0;', 'class' => 'form-select']); ?>
        </div>
    </div>

    <div class="form-group">
        <?php
            echo $form->label('content', t('Content'));
$editor = Core::make('editor');
echo $editor->outputBlockEditModeEditor('content', $content ?? null);
?>
    </div>
</fieldset>