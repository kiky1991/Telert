<?php

if (!class_exists('Telert_Ajax')) {

    /**
     * Ajax Class
     */
    class Telert_Ajax
    {

        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('wp_ajax_telert_search_user', array($this, 'search_user'));
        }

        public function search_user()
        {
            ob_start();

            check_ajax_referer( 'telert-search-user-nonce', 'telert_search_user_nonce' );

            if (!current_user_can('administrator')) {
                wp_die(-1);
            }

            $term  = isset($_GET['search']) ? (string) sanitize_text_field(wp_unslash($_GET['search'])) : '';
            $limit = 0;

            if (empty($term)) {
                wp_die();
            }

            $ids = array();
            // Search by ID.
            if (is_numeric($term)) {
                $user = new WP_User(intval($term));

                // Customer does not exists.
                if (0 !== $user->ID) {
                    $ids = array($user->ID);
                }
            }

            // Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
            if (empty($ids)) {
                $users = new WP_User_Query( array(
                    'search'         => '*'.esc_attr( $term ).'*',
                    'search_columns' => array(
                        'user_login',
                        'user_nicename',
                        'user_email',
                    ),
                ) );
                $data_store = $users->get_results();
                $ids = array_column($data_store, 'ID');
            }

            $found_users = array();
            foreach ($ids as $id) {
                $user = new WP_User($id);

                /* translators: 1: user display name 2: user ID 3: user email */
                $found_users[] = array(
                    $user->ID,
                    sprintf(
                        /* translators: $1: customer name, $2 customer id, $3: customer email */
                        esc_html__('%1$s (#%2$s - %3$s)', 'wpes'),
                        $user->user_nicename,
                        $user->ID,
                        $user->user_email
                    )
                );
            }

            wp_send_json(apply_filters('json_search_found_customers', $found_users));
        }
    }
}