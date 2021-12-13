<div class="wrap">
    <a href="<?php echo esc_url(add_query_arg(['page' => 'telert', 'tab' => 'alert_list', 'action' => 'add'], admin_url("admin.php"))); ?>" class="button button-primary"><?php esc_html_e('Add Alert', 'telert'); ?></a>
    <br>
    <form action="<?php echo esc_url(admin_url('admin.php?page=telert&tab=alert_list')); ?>" method="POST">
        <?php $alerts->prepare_items($search); ?>
        <div class="telert-setting">
            <div class="setting-section">
                <?php $alerts->search_box('Search alert (s)', 'search'); ?>
                <?php $alerts->display(); ?>
            </div>
        </div>
    </form>
</div>
<style type="text/css">
    .wp-list-table .column-updated_at {
        width: 100px;
    }

    .widefat tbody tr:hover {
        background-color: #ececec;
    }
</style>