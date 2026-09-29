<form>
    <fieldset>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('EventbriteKitId', t('EventbriteKit ID'));
                    echo $form->text('EventbriteKitId', $v['EventbriteKitId'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('name', t('Name'));
                    echo $form->text('name', $v['name'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('venue', t('Venue'));
                    echo $form->text('venue', $v['venue'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('url', t('URL'));
                    echo $form->url('url', $v['url'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('startDate', t('Start Date'));
                    echo $form->text('startDate', $v['startDate'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('endDate', t('End Date'));
                    echo $form->text('endDate', $v['endDate'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php
                        echo $form->label('description', t('Description'));
                    echo $form->textarea('description', $v['description'], ['readonly' => 'readonly']);
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <?php echo $form->label('image', t('Image')); ?>
                    <?php if (strlen($v['image']) > 0) { ?>
                        <img src="<?php echo $v['image']; ?>" style="display:block; max-width:300px; height:auto" />
                    <?php } else {
                        echo $form->text('image', t('No image uploaded'), ['readonly' => 'readonly']);
                    } ?>
                </div>
            </div>
        </div>
    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo \URL::to('/dashboard/EventbriteKit/events'); ?>" class="btn btn-secondary float-start"><?php echo t('Back to Events'); ?></a>
            <a href="<?php echo $v['url']; ?>" target="_blank" class="btn btn-primary float-end"><?php echo t('View Event'); ?></a>
        </div>
    </div>
</form>