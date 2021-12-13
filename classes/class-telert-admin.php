<?php
/**
 * Validator for PHP 7.0+
 * Documentation https://github.com/rakit/validation
 */
use Rakit\Validation\Validator;

if (!class_exists('Telert_Admin')) {

    /**
     * Admin Class
     */
    class Telert_Admin
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->core         = new Telert_Core();
            $this->validator    = new Validator();

            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

            // handle actions
            add_action('admin_post_telert_alert_list_add', array($this, 'alert_list_add'));
            add_action('admin_post_telert_alert_list_edit', array($this, 'alert_list_edit'));
            add_action('admin_notices', array($this, 'display_flash_notices'));
        }

        /**
         * Validate current admin screen
         *
         * @param   string  $page   page to validate 
         * @return  boolean         Screen is Brandplus or not.
         */
        protected function validate_screen($page = '')
        {
            $screen = get_current_screen();
            if (is_null($screen)) {
                return false;
            }

            if (!empty($page) && $screen->id === $page) {
                return true;
            }

            $allowed_screens = apply_filters('telert_validate_screen', array());
            if (empty($page) && in_array($screen->id, $allowed_screens, true)) {
                return true;
            }
            return false;
        }

        public function admin_menu()
        {
            add_menu_page('Telert', 'Telert', 'level_7', 'telert', null, TELERT_PLUGIN_URI . '/assets/img/icon.png', 58);
            add_submenu_page('telert', __('Telert', 'telert'), __('Telert', 'telert'), 'level_7', 'telert', array($this, 'render_page_setting'), 70);
            remove_submenu_page('telert', 'telert');
        }

        public function enqueue_scripts()
        {
            // if ($this->validate_screen()) {
                wp_register_script('telert-sweatalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11', true);
                wp_register_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
                wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'), '4.0.3', true);
                wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'), '4.0.3', true);
                wp_enqueue_style('telert-admin', TELERT_PLUGIN_URI . '/assets/css/admin.css', array('select2'), TELERT_VERSION);
                wp_enqueue_script('telert-admin', TELERT_PLUGIN_URI . '/assets/js/admin.js', array('jquery', 'select2'), TELERT_VERSION, true);
                wp_localize_script(
                    'telert-admin',
                    'nonce',
                    array(
                        'search_user'   => wp_create_nonce('telert-search-user-nonce'),
                    )
                );
                // }
        }

        public function render_page_setting()
        {
            $tabs = apply_filters(
                'telert_setting_tabs',
                array(
                    'dashboard'   => array(
                        'label'     => __('Dashboard', 'telert'),
                        'callback'  => array($this, 'render_subpage_dashboard'),
                    ),
                    'alert_list'   => array(
                        'label'     => __('Alert List', 'telert'),
                        'callback'  => array($this, 'render_subpage_alert_list'),
                    ),
                )
            );

            if (isset($_GET['tab']) && in_array(sanitize_text_field(wp_unslash($_GET['tab'])), array_keys($tabs), true)) { // WPCS: Input var okay, CSRF ok.
                $tab = sanitize_text_field(wp_unslash($_GET['tab'])); // WPCS: Input var okay, CSRF ok.
            } else {
                $tab = current(array_keys($tabs));
            }
            include_once TELERT_PLUGIN_PATH . 'views/setting.php';
        }

        public function render_subpage_dashboard()
        {   
            echo "Undermaintenance";
        }

        public function render_subpage_alert_list()
        {
            if (!current_user_can('level_7')) {
                return;
            }

            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

            switch ($action) {
                case 'bulk_delete':
                case 'delete':
                    if ($action === 'delete' && $id > 0) {
                        $deleted = $this->core->db_cron(array(
                            'db_type'   => 'delete_by_id',
                            'data'      => array(
                                'id'   => $id
                            )
                        ));
                    }

                    if ($action == 'bulk_delete' && !empty($_POST['ids'])) {
                        $deleted = $this->core->db_cron(array(
                            'db_type'   => 'delete_bulk_action',
                            'data'      => array(
                                'ids'   => $_POST['ids']
                            )
                        ));
                    }
                    break;
                case 'add':
                    wp_enqueue_style('select2');
                    wp_enqueue_script('select2');

                    include_once TELERT_PLUGIN_PATH . '/views/setting-alert-list-form.php';
                    return;
                    break;
                case 'edit':
                    if ($id > 0) {
                        $alert = $this->core->db_cron(array(
                            'db_type'   => 'get_single_by_id',
                            'data'      => array(
                                'id' => $id
                            )
                        ));

                        if (empty($alert)) {
                            esc_html_e('Cannot found alert!', 'telert');
                            die;
                        } else {
                            include_once TELERT_PLUGIN_PATH . '/views/setting-alert-list-form.php';
                        }
                    } else {
                        esc_html_e('Page not valid!', 'telert');
                    }
                    return;
                    break;
                default:
                    break;
            }

            $search = '';
            if (isset($_POST['s']) && !empty($_POST['s'])) {
                $search = sanitize_text_field(trim($_POST['s']));
            }

            include_once TELERT_PLUGIN_PATH . '/classes/includes/table-alert-list.php';
            $alerts = new Telert_Table_Alert_List($search);
            include_once TELERT_PLUGIN_PATH . '/views/setting-alert-list.php';
        }

        public function alert_list_add()
        {
            if (
                !isset($_POST['telert_alert_list_add_nonce'])
                || !wp_verify_nonce($_POST['telert_alert_list_add_nonce'], 'telert_alert_list_add')
                || !isset($_POST['add'])
            ) {
                return;
            }

            if (!current_user_can('level_7')) {
                return;
            }

            $validation = $this->validator->validate($_POST, [
                'cron'      => 'required',
                'title'     => 'required',
                'message'   => 'required',
                'enable'    => 'required',
            ]);

            if ($validation->fails()) {
                $errors = $validation->errors();
                
                $this->add_flash_notice('<ul>' . implode(' ', $errors->all('<li>:message</li>')) . '</ul>', 'error', false);
                wp_safe_redirect(add_query_arg(array('page'=>'telert', 'tab'=>'alert_list', 'action'=>'add'), admin_url("admin.php")));
                exit;
            }

            $enable     = sanitize_text_field(wp_unslash($_POST['enable']));
            $title      = sanitize_text_field(wp_unslash($_POST['title']));
            $message    = sanitize_text_field(wp_unslash($_POST['message']));
            $cron       = sanitize_text_field(wp_unslash($_POST['cron']));
            
            $inserted = $this->core->db_cron(array(
                'db_type'   => 'insert',
                'data'      => array(
                    'title'             => $title,
                    'message'           => $message,
                    'cron'              => $cron,
                    'type'              => null,
                    'user_responsible'  => 0,
                    'is_enable'         => $enable === 'yes' ? true : false,
                    'is_handled'        => false,
                    'is_sended_telegram'=> false,
                    'start_cron'        => current_time('mysql'),
                    'next_cron'         => current_time('mysql'),
                )
            ));

            if ($inserted && $inserted > 0) {
                $this->add_flash_notice('success insert cron!', 'success');
                wp_safe_redirect(admin_url("admin.php?page=telert&tab=alert_list"));
                die;
            } else {
                $this->add_flash_notice('Cron cannot save, try again or call developer if repeatable!', 'error');
                wp_safe_redirect(admin_url("admin.php?page=telert&tab=alert_list&action=add"));
                die;
            }
        }

        public function alert_list_edit()
        {
            if (
                !isset($_POST['telert_alert_list_edit_nonce'])
                || !wp_verify_nonce($_POST['telert_alert_list_edit_nonce'], 'telert_alert_list_edit')
                || !isset($_POST['edit'])
            ) {
                return;
            }

            if (!current_user_can('level_7')) {
                return;
            }

            $validation = $this->validator->validate($_POST, [
                'cron'      => 'required',
                'title'     => 'required',
                'message'   => 'required',
                'enable'    => 'required',
            ]);

            if ($validation->fails()) {
                $errors = $validation->errors();
                
                $this->add_flash_notice('<ul>' . implode(' ', $errors->all('<li>:message</li>')) . '</ul>', 'error', false);
                wp_safe_redirect(add_query_arg(array('page'=>'telert', 'tab'=>'alert_list', 'action'=>'add'), admin_url("admin.php")));
                exit;
            }

            $id         = sanitize_text_field(intval($_POST['id']));
            $enable     = sanitize_text_field(wp_unslash($_POST['enable']));
            $title      = sanitize_text_field(wp_unslash($_POST['title']));
            $message    = sanitize_text_field(wp_unslash($_POST['message']));
            $cron       = sanitize_text_field(wp_unslash($_POST['cron']));

            $alert = $this->core->db_cron(array(
                'db_type'  => 'get_single_by_id',
                'data'  => array(
                    'id' => $id
                )
            ));

            if (!$alert) {
                $this->add_flash_notice('Cannot save, id not found!', 'error');
                wp_safe_redirect(admin_url("admin.php?page=telert&tab=alert_list&action=edit"));
                die;
            }
            
            $edited = $this->core->db_cron(array(
                'db_type'   => 'update',
                'data'      => array(
                    'title'             => $title,
                    'message'           => $message,
                    'cron'              => $cron,
                    'type'              => $alert['type'],
                    'user_responsible'  => $alert['user_responsible'],
                    'is_enable'         => $enable === 'yes' ? true : false,
                    'is_handled'        => $alert['is_handled'],
                    'is_sended_telegram'=> $alert['is_sended_telegram'],
                    'start_cron'        => $alert['start_cron'],
                    'next_cron'         => $alert['next_cron'],
                    'id'                => $alert['id']
                )
            ));

            if ($edited && $edited > 0) {
                $this->add_flash_notice('success edit cron!', 'success');
                wp_safe_redirect(admin_url("admin.php?page=telert&tab=alert_list"));
                die;
            } else {
                $this->add_flash_notice('Cron cannot save, try again or call developer if repeatable!', 'error');
                wp_safe_redirect(admin_url("admin.php?page=telert&tab=alert_list&action=edit&id=$id"));
                die;
            }
        }

        public function add_flash_notice($message = '', $type = 'success', $p = true)
        {
            $old_notice = get_option('my_flash_notices', array());
            $old_notice[] = array(
                'type'      => !empty($type) ? $type : 'success',
                'message'   => $p ? '<p>' . $message . '</p>' : $message,
            );
            update_option('my_flash_notices', $old_notice, false);
        }

        public function display_flash_notices()
        {
            $notices = get_option('my_flash_notices', array());
            foreach ($notices as $notice) {
                printf(
                    '<div class="notice is-dismissible notice-%1$s">%2$s</div>',
                    esc_attr($notice['type']),
                    wp_kses_post($notice['message'])
                );
            }

            if (!empty($notices)) {
                delete_option("my_flash_notices", array());
            }
        }
    }
}