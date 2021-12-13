<?php

if (!class_exists('Telert_Cron')) {

    /**
     * Cron Class
     */
    class Telert_Cron
    {
        /**
         * Telert_Cron::$api_key
         *
         * Api Key.
         *
         * @access  private
         * @type    string
         */
        private $api_key = '5049490253:AAHMbApXekH3Clf63y-YNUtcuT5G2CROLsM';
        
        /**
         * Telert_Cron::$chat_id
         *
         * Api Key.
         *
         * @access  private
         * @type    string
         */
        private $chat_id = '-798712547';

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->telegram = new Telegram();
            $this->core     = new Telert_Core();

            if (!wp_next_scheduled('do_cron_daily')) {
                wp_schedule_event(time(), 'daily', 'do_cron_daily');
            }

            if (!wp_next_scheduled('do_cron_weekly')) {
                wp_schedule_event(time(), 'weekly', 'do_cron_weekly');
            }

            if (!wp_next_scheduled('do_cron_twoweeks')) {
                wp_schedule_event(time(), 'twoweeks', 'do_cron_twoweeks');
            }

            if (!wp_next_scheduled('do_cron_monthly')) {
                wp_schedule_event(time(), 'monthly', 'do_cron_monthly');
            }

            if (!wp_next_scheduled('do_cron_yearly')) {
                wp_schedule_event(time(), 'yearly', 'do_cron_yearly');
            }

            add_filter('cron_schedules', array($this, 'cron_custom'));
            add_action('do_cron_daily', array($this, 'daily'));
            add_action('do_cron_weekly', array($this, 'weekly'));
            add_action('do_cron_twoweeks', array($this, 'twoweeks'));
            add_action('do_cron_monthly', array($this, 'monthly'));
            add_action('do_cron_yearly', array($this, 'yearly'));
        }

        public function daily()
        {
            $this->do_telegram('daily');
        }
        
        public function weekly()
        {
            $this->do_telegram('weekly');
        }

        public function twoweeks()
        {
            $this->do_telegram('twoweeks');
        }

        public function monthly()
        {
            $this->do_telegram('monthly');
        }
        
        public function yearly()
        {
            $this->do_telegram('yearly');
        }

        public function do_telegram($type)
        {
            $alerts = $this->core->db_cron(array(
                'db_type'  => 'get_custom',
                'data'  => array(
                    'where' => sprintf('where cron="%s" AND is_enable=1', $type)
                )
            ));

            if (empty($alerts)) return __return_false();
            foreach($alerts as $alert) {
                $result = $this->telegram->send_message($this->chat_id, $this->api_key, $alert['message']);
                $return = isset($result) ? json_decode($result, true) : false;
                $return = (isset($return['ok']) && $return['ok'] == true && isset($return['result']['message_id'])) ? true : false;
                
                if (!$return) continue;

                $this->core->db_cron(array(
                    'db_type'  => 'update',
                    'data'  => array(
                        'title'             => $alert['title'],
                        'message'           => $alert['message'],
                        'cron'              => $alert['cron'],
                        'type'              => $alert['type'],
                        'user_responsible'  => $alert['user_responsible'],
                        'is_enable'         => $alert['is_enable'],
                        'is_handled'        => $alert['is_handled'],
                        'is_sended_telegram'=> $alert['is_sended_telegram'],
                        'start_cron'        => $alert['start_cron'],
                        'next_cron'         => $this->get_date($alert['cron'], $alert['next_cron']),
                        'id'                => $alert['id']
                    )
                ));
            }
        }

        /**
         * Rockor_Cron::cron_custom
         * 
         * Create custom cron timer
         * @param   array   $schedules   list of schedules
         * 
         * @return  array   $schedules  list of new schedules
         */
        public function cron_custom($schedules)
        {
            $schedules['yearly'] = array(
                'interval'  => 60 * 60 * 24 * 30 * 12,
                'display'   => __('Every Year', 'telert')
            );

            $schedules['monthly'] = array(
                'interval'  => 60 * 60 * 24 * 30,
                'display'   => __('Every Monthly', 'telert')
            );

            $schedules['weekly'] = array(
                'interval'  => 60 * 60 * 24 * 7,
                'display'   => __('Every 1 Weeks', 'telert')
            );

            $schedules['twoweeks'] = array(
                'interval'  => 60 * 60 * 24 * 14,
                'display'   => __('Every 2 Weeks', 'telert')
            );

            $schedules['sixhour'] = array(
                'interval'  => 60 * 60 * 6,
                'display'   => __('Every 6 hour', 'telert')
            );

            $schedules['thirtyminutes'] = array(
                'interval'  => 60 * 30,
                'display'   => __('Every Thirty Minutes', 'telert')
            );

            $schedules['fiveteenminutes'] = array(
                'interval'  => 60 * 15,
                'display'   => __('Every Fiveteen Minutes', 'telert')
            );
            
            $schedules['fiveminutes'] = array(
                'interval'  => 60 * 5,
                'display'   => __('Every Five Minutes', 'telert')
            );

            $schedules['everyminutes'] = array(
                'interval'  => 60 * 1,
                'display'   => __('Every Minutes', 'telert')
            );

            return $schedules;
        }

        private function get_date($type, $datetime)
        {
            switch ($type) {
                case 'hourly':
                    return date("Y-m-d H:i:s", strtotime("+1 hours", strtotime($datetime)));
                    break;
                case 'sixhour':
                    return date("Y-m-d H:i:s", strtotime("+6 hours", strtotime($datetime)));
                    break;
                case 'daily':
                    return date("Y-m-d H:i:s", strtotime("+1 day", strtotime($datetime)));
                    break;
                case 'weekly':
                    return date("Y-m-d H:i:s", strtotime("+1 week", strtotime($datetime)));
                    break;
                case 'twoweeks':
                    return date("Y-m-d H:i:s", strtotime("+2 week", strtotime($datetime)));
                    break;
                case 'monthly':
                    return date("Y-m-d H:i:s", strtotime("+1 month", strtotime($datetime)));
                    break;
                case 'yearly':
                    return date("Y-m-d H:i:s", strtotime("+1 years", strtotime($datetime)));
                    break;
                
                default:
                    return $datetime;
                    break;
            }
        }
    }
}