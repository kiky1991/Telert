<?php
if (!class_exists('Telert_Install')) {

    class Telert_Install
    {
        public function __construct()
        {
            $this->install_db();
        }

        public function install_db()
        {
            // install the table
            $this->create_table_telert_cron();
        }

        private function create_table_telert_cron()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'telert_cron';
            if ($wpdb->has_cap('collation')) {
                $charset_collate = $wpdb->get_charset_collate();
            }

            $sql = "DROP TABLE IF EXISTS $table_name;
                CREATE TABLE $table_name (
                id BIGINT NOT NULL AUTO_INCREMENT,
                type VARCHAR(200) NULL,
                cron VARCHAR(200) NULL,
                title VARCHAR(500) NOT NULL,
                message TEXT NULL,
                user_responsible BIGINT NULL,
                is_enable BOOLEAN NOT NULL DEFAULT FALSE,
                is_handled BOOLEAN NOT NULL DEFAULT FALSE,
                is_sended_telegram BOOLEAN NOT NULL DEFAULT FALSE,
                start_cron timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                next_cron timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
			) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
