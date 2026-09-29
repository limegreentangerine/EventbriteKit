<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Form\Service\Form;

/** @var string $headerSearchAction */
/** @var Form $form */

$form = \Core::make(Form::class);
?>

<div class="ccm-header-search-form ccm-ui" data-header="alto">
    <form method="get" class="row row-cols-auto g-0 align-items-center" action="<?php echo $headerSearchAction ?>">

        <div class="ccm-header-search-form-input input-group">
            <?php if (isset($params) && !empty($params)) { ?>
                <a href="<?php echo URL::to('/dashboard/EventbriteKit/events/clear_search'); ?>" class="btn btn-link">
                    <?php echo t('Clear Search'); ?>
                </a>
            <?php } ?>
            <?php
                echo $form->search('name', [
                    'placeholder' => t('Name'),
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                    'value' => $params['name'] ?? '',
                ]);
?>
            <button type="submit" class="input-group-icon">
                <svg width="16" height="16">
                    <use xlink:href="#icon-search"/>
                </svg>
            </button>

            <?php
    echo $form->select('num_results', $paginationSizes, $params['num_results'] ?? 10, [
        'class' => 'form-select-filter',
        'placeholder' => t('Number of Results'),
    ]);
?>

        </div>
    </form>
</div>

<script>
    (function ($) {
        $(function () {
            $(".form-select-filter").on("change", function(e){
                e.preventDefault();
                $(this).closest("form").submit();
            });

            ConcreteEvent.subscribe('SavedSearchCreated', function () {
                window.location.reload();
            });

            ConcreteEvent.subscribe('SavedPresetSubmit', function (e, url) {
                window.location.href = url;
            });
        });
    })(jQuery);
</script>