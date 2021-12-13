<div class="tablenav top">
    <div class="alignleft actions bulkactions">
        <?php $this->bulk_actions($which); ?>
    </div>

    <div class="alignleft">
        <!-- left it blank -->
    </div>
    <div class="alignright">
        <?php $this->extra_tablenav($which); ?>
        <?php $this->pagination($which); ?>
    </div>
</div>