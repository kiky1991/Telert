
<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if (!class_exists('Telert_Table_Alert_List')) {
    class Telert_Table_Alert_List extends WP_List_Table
    {
        public $status;

        public function __construct()
        {
            parent::__construct(
                array(
                    'singular' => 'telert_alert_list',
                    'plural'   => 'telert_alert_lists',
                    'ajax'     => true
                )
            );
        }

        public function prepare_items($search = "")
        {
            global $wpdb;

            // set table name
            $table_name = $wpdb->prefix . 'telert_cron';

            // set offset perpage
            $per_page = 50;

            // get columns of table
            $columns = $this->get_columns();

            // get hidden column if needed 
            $hidden = $this->get_hidden_columns();

            // get sortable columns if needed
            $sortable = $this->get_sortable_columns();

            // set header
            $this->_column_headers = array($columns, $hidden, $sortable);

            // setup offset with order by
            $paged = $this->get_pagenum();
            $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($sortable))) ? $_REQUEST['orderby'] : "updated_at";
            $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';
            $offset = ($per_page * $paged) - $per_page;

            // init for where
            $where = !empty($search) ? 'WHERE title LIKE "%' . sanitize_text_field(wp_unslash($search)) . '%"' : '';
            
            // count id from table to get max page
            $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where");

            // get the items data from table DB and passed to array
            $this->items = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name
                $where
                ORDER BY $orderby $order LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                ),
                ARRAY_A
            );

            // set pagination
            $this->set_pagination_args(array(
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page)
            ));
        }

        /**
         * Display tablenav.
         *
         * Adds an export button.
         *
         * @param  string $which Whether we're generating the "top" or "bottom" tablenav.
         */
        protected function display_tablenav($which)
        {
            if ('top' === $which) {
                include_once TELERT_PLUGIN_PATH . '/classes/includes/tablenav-alert-list.php';
            }
        }

        public function get_columns()
        {
            return [
                'cb'            => "<input type='checkbox' />",
                'title'         => "Title",
                'cron'          => "Cron",
                'start_cron'    => "Start Alert",
                'next_cron'     => "Next Alert",
                'is_enable'     => "Enable",
                'updated_at'    => "Updated At"
            ];
        }

        public function column_cb($item)
        {
            return sprintf("<input type='checkbox' name='ids[]' value='%s' />", $item['id']);
        }

        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'title':
                    $actions = array(
                        'edit' => sprintf('<a href="?page=%1$s&tab=%2$s&action=edit&id=%3$s">%4$s</a>', $_REQUEST['page'], $_REQUEST['tab'], $item['id'], __('Edit', 'telert')),
                        'delete' => sprintf('<a href="?page=%1$s&tab=%2$s&action=delete&id=%3$s" onclick="return confirm(`Are you sure?`)">%4$s</a>', $_REQUEST['page'], $_REQUEST['tab'], $item['id'], __('Delete', 'rockmp')),
                    );

                    return sprintf('%1$s %2$s', $item[$column_name], $this->row_actions($actions));
                    break;
                case 'cron':
                    return ucfirst($item[$column_name]);
                case 'is_enable':
                    return $item[$column_name] ? 'Yes' : 'No';
                    break;
                case 'start_cron':
                case 'next_cron':
                    return $item[$column_name];
                    break;
                case 'updated_at':
                    $date_format = get_option('date_format');
                    $time_format = get_option('time_format');
                    return date("{$date_format} {$time_format}", strtotime($item['updated_at']));
                    break;
                default:
                    return "No Value";
                    break;
            }
        }

        public function get_bulk_actions()
        {
            return array(
                'bulk_delete'    => 'Bulk Delete',
            );
        }

        public function get_sortable_columns()
        {
            return array(
                'updated_at'    => ['update_at', false],
            );
        }

        public function get_hidden_columns()
        {
            return array(
                'created_at'
            );
        }
    }
}
