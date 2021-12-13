<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="telert-alert-list">
    <div class="telert-setting">
        <div class="setting-section" id="<?php esc_attr_e($action, 'telert'); ?>">
            <h4 class="section-title">
                <?php esc_html_e(($action == 'add' ? 'Add' : 'Edit') . ' Alert', 'telert'); ?>
            </h4>
            <div class="setting-table">
                <div class="setting-row">
                    <div class="setting-index">
                        <label for="rockgp-enable"><?php esc_html_e('Enabled', 'telert'); ?></label>
                        <p class="helper"><?php esc_html_e('Enable or disable alert.', 'telert'); ?></p>
                    </div>
                    <div class="setting-option">
                        <div class="toggle">
                            <input type="radio" name="enable" id="telert-enable-no" <?php echo ($action == 'edit' && $alert['is_enable']) ? '' : 'checked'; ?> value="no">
                            <label for="telert-enable-no"><?php esc_html_e('No', 'telert'); ?></label>
                            <input type="radio" name="enable" id="telert-enable-yes" <?php echo ($action == 'edit' && $alert['is_enable']) ? 'checked' : ''; ?> value="yes">
                            <label for="telert-enable-yes"><?php esc_html_e('Yes', 'telert'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-index">
                        <label for="telert-title"><?php esc_html_e('Title', 'telert'); ?></label>
                        <p class="helper"><?php esc_html_e('Alert title', 'telert'); ?></p>
                    </div>
                    <div class="setting-option">
                        <input type="text" name="title" value="<?php echo ($action == 'edit') ? $alert['title'] : ''; ?>">
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-index">
                        <label for="telert-message"><?php esc_html_e('Message', 'telert'); ?></label>
                        <p class="helper"><?php esc_html_e('Yout message', 'telert'); ?></p>
                    </div>
                    <div class="setting-option">
                        <textarea name="message" cols="30" rows="10"><?php echo ($action == 'edit') ? $alert['message'] : ''; ?></textarea>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-index">
                        <label for="telert-cron-alert"><?php esc_html_e('Cron Alert', 'telert'); ?></label>
                        <p class="helper"><?php esc_html_e('Set cron for daily / weekly / monthly', 'telert'); ?></p>
                    </div>
                    <div class="setting-option">
                        <select name="cron">
                            <option value="daily" <?php echo ($action == 'edit' && $alert['cron'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo ($action == 'edit' && $alert['cron'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                            <option value="twoweeks" <?php echo ($action == 'edit' && $alert['cron'] == 'twoweeks') ? 'selected' : ''; ?>>2 Weeks</option>
                            <option value="monthly" <?php echo ($action == 'edit' && $alert['cron'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                            <option value="yearly" <?php echo ($action == 'edit' && $alert['cron'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                            <option value="once" <?php echo ($action == 'edit' && $alert['cron'] == 'once') ? 'selected' : ''; ?>>Only Once</option>
                        </select>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-index">
                        <label for="telrt-responsible"><?php esc_html_e('Responsible [Disabled]', 'telert'); ?></label>
                        <p class="helper"><?php esc_html_e('Who is the responsible user ?', 'telert'); ?></p>
                    </div>
                    <div class="setting-option">
                        <select name="user_responsible" id="search-user-select2" disabled>
                            <option value=""><?php esc_html_e('-- Search User --', 'wpes') ?></option>
                            <?php if (isset($data) && $data['user_responsible'] > 0) : ?>
                                <?php $user = new WP_User($setting['user_responsible']); ?>
                                <option value="<?php esc_attr_e($user->ID); ?>" selected>
                                    <?php echo sprintf(
                                        /* translators: $1: customer name, $2 customer id, $3: customer email */
                                        esc_html__('%1$s (#%2$s - %3$s)', 'wpes'),
                                        $user->user_nicename,
                                        $user->ID,
                                        $user->user_email
                                    ) ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <p class="helper">
                            <?php printf('Forgot your user name ? <a href="%s">%s</a>', esc_url(admin_url('users.php')), esc_html__('See Here', 'wpes')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <?php if (isset($id)) : ?>
        <input type="hidden" name="id" value="<?php esc_attr_e($id); ?>">
    <?php endif; ?>
    <input type="hidden" name="action" value="<?php esc_attr_e('telert_alert_list_' . $action, 'telert'); ?>">
    <?php wp_nonce_field('telert_alert_list_' . $action, 'telert_alert_list_' . $action . '_nonce'); ?>
    <input type="submit" name="<?php esc_attr_e($action, 'telert'); ?>" value="<?php esc_attr_e(($action == 'add' ? 'Add New' : 'Edit') . ' Alert', 'telert'); ?>" class="button button-primary">
</form>