<form method="post" action="<?php echo $view->action('save'); ?>">
    <?php echo $token->output('submit'); ?>
    <fieldset>
        <legend><?php echo t('Configuration'); ?></legend>

        <div class="form-group">
            <?php
                echo $form->label('api_key', t('API Key'));
echo $form->text('api_key', (isset($formContent)) ? $formContent['api_key'] : (($pkg && $pkg->getFileConfig()->get('eventbrite.api_key') !== null) ? $pkg->getFileConfig()->get('eventbrite.api_key') : ''), ['placeholder' => 'API KEY']);
?>
        </div>

        <div class="form-group">
            <?php
    echo $form->label('base_url', t('Base URL'));
echo $form->url('base_url', (isset($formContent)) ? $formContent['base_url'] : (($pkg && $pkg->getFileConfig()->get('eventbrite.base_url') !== null) ? $pkg->getFileConfig()->get('eventbrite.base_url') : ''), ['placeholder' => 'https://www.eventbrite.com/v3']);
?>
        </div>
    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $form->submit('save', t('Save Settings'), ['class' => 'btn btn-primary float-end']); ?>
        </div>
    </div>
</form>