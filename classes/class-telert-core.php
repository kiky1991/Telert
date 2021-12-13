<?php

if (!class_exists('Telert_Core')) {

    /**
     * Core Class
     */
    class Telert_Core
    {
        public function __construct()
        {
            global $wpdb;
            
            $this->wpdb = $wpdb;
        }

        public function db_cron($db = array())
        {
            if (!isset($db['db_type']) && !isset($db['data'])) {
                return false;
            }

            $table = $this->wpdb->prefix . 'telert_cron';
            switch ($db['db_type']) {
                case 'insert':
                    $result = $this->wpdb->insert(
                        $table,
                        array(
                            'type'              => $db['data']['type'],
                            'cron'              => $db['data']['cron'],
                            'title'             => $db['data']['title'],
                            'message'           => $db['data']['message'],
                            'user_responsible'  => $db['data']['user_responsible'],
                            'is_enable'         => $db['data']['is_enable'],
                            'is_handled'        => $db['data']['is_handled'],
                            'is_sended_telegram'=> $db['data']['is_sended_telegram'],
                            'start_cron'        => $db['data']['start_cron'],
                            'next_cron'         => $db['data']['next_cron'],
                        )
                    );

                    return ($result !== false) ? $this->wpdb->insert_id : false;
                    break;
                case 'update':
                    return $this->wpdb->update(
                        $table,
                        array(
                            'type'              => $db['data']['type'],
                            'cron'              => $db['data']['cron'],
                            'title'             => $db['data']['title'],
                            'message'           => $db['data']['message'],
                            'user_responsible'  => $db['data']['user_responsible'],
                            'is_enable'         => $db['data']['is_enable'],
                            'is_handled'        => $db['data']['is_handled'],
                            'is_sended_telegram'=> $db['data']['is_sended_telegram'],
                            'start_cron'        => $db['data']['start_cron'],
                            'next_cron'         => $db['data']['next_cron'],
                            'updated_at'        => current_time('mysql'),
                        ),
                        array(
                            'id' => $db['data']['id'],
                        )
                    );
                    break;
                case 'get_custom':
                    $where = isset($db['data']['where']) ? $db['data']['where'] : '';

                    return $this->wpdb->get_results(
                        "SELECT * FROM {$table}
                            {$where}",
                        ARRAY_A
                    );
                    break;
                case 'get_all':
                    return $this->wpdb->get_results(
                        "SELECT * FROM {$table}",
                        ARRAY_A
                    );
                    break;
                case 'delete_bulk_action':
                    $id = implode(',', $db['data']['ids']);
                    return $this->wpdb->query("DELETE FROM $table WHERE id IN($id)");
                    break;
                case 'delete_by_id':
                    return $this->wpdb->delete(
                        $table,
                        array(
                            'id' => $db['data']['id']
                        ),
                        array(
                            '%d'
                        )
                    );
                    break;
                case 'get_single_by_id':
                    return $this->wpdb->get_row(
                        $this->wpdb->prepare(
                            "SELECT * FROM {$table}
                                WHERE id = %d",
                            $db['data']['id']
                        ),
                        ARRAY_A
                    );
                    break;
            }
        }

        protected function empty_table($table)
        {
            return $this->wpdb->query("TRUNCATE {$table}");
        }

        protected function get_where($table, $data, $single = false)
        {
            if (empty($table) || empty($data)) return __return_false();

            $value = is_numeric($data['value']) ? '%d' : '%s';
            $query = $this->wpdb->prepare(
                "SELECT * FROM {$table} WHERE {$data['title']} = {$value}",
                $data['value']
            );

            if ($single)
                return $this->wpdb->get_row($query, ARRAY_A);
            else
                return $this->wpdb->get_results($query, ARRAY_A);
        }

        protected function bulk_insert($table, $rows)
        {
            $columns = array_keys($rows[0]);
            asort($columns);
            $columnList = '`' . implode('`, `', $columns) . '`';

            // Start building SQL, initialise data and placeholder arrays
            $sql = "INSERT INTO `$table` ($columnList) VALUES\n";
            $placeholders = array();
            $data = array();

            // Build placeholders for each row, and add values to data array
            foreach ($rows as $row) {
                ksort($row);
                $rowPlaceholders = array();

                foreach ($row as $key => $value) {
                    $data[] = $value;
                    if ($key == 'weight') $rowPlaceholders[] = '%s';
                    else $rowPlaceholders[] = is_numeric($value) ? '%d' : '%s';
                }

                $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
            }

            // Stitch all rows together
            $sql .= implode(",\n", $placeholders);

            // Run the query.  Returns number of affected rows.
            return $this->wpdb->query($this->wpdb->prepare($sql, $data));
        }

        protected function bulk_update($table, $data, $where, $format = NULL, $where_format = NULL)
        {
            $table = esc_sql($table);

            $i          = 0;
            $q          = "UPDATE " . $table . " SET ";
            $format     = array_values((array) $format);
            $escaped    = array();

            foreach ((array) $data as $key => $value) {
                $f         = isset($format[$i]) && in_array($format[$i], array('%s', '%d'), TRUE) ? $format[$i] : '%s';
                $escaped[] = esc_sql($key) . " = " . $this->wpdb->prepare($f, $value);
                $i++;
            }

            $q         .= implode(', ', $escaped);
            $where      = (array) $where;
            $where_keys = array_keys($where);
            $where_val  = (array) array_shift($where);
            $q         .= " WHERE " . esc_sql(array_shift($where_keys)) . ' IN (';

            if (!in_array($where_format, array('%s', '%d'), TRUE)) {
                $where_format = '%s';
            }

            $escaped = array();

            foreach ($where_val as $val) {
                $escaped[] = $this->wpdb->prepare($where_format, $val);
            }

            $q .= implode(', ', $escaped) . ')';
            return $this->wpdb->query($q);
        }
    }
}