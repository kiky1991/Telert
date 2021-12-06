<?php

if (!class_exists('Telegram')) {

    /**
     * Class Telegram
     * 
     * Documentation https://core.telegram.org/bots/api
     * This class crete by Hengky, this is for send message bot telgram using bot father.
     */
    class Telegram
    {
        /**
         * Telegram::$baseUri
         *
         * Telegram Base Uri.
         *
         * @access  protected
         * @type    string
         */
        protected $baseUri = 'https://api.telegram.org/';

        /**
         * Telegram::__construct
         */
        public function __construct()
        {
        }

        /**
         * Telegram::request
         *
         * Curl request API caller.
         *
         * @param   string  $path       Path url
         * @param   array   $params     Params
         * @param   string  $type       POST or GET
         *
         * @access  protected
         * @return  bool    retun false if failed.
         */
        protected function request($path, $params = '', $type = 'GET')
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->baseUri . $path,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $type,
                CURLOPT_HTTPHEADER => array(),
                CURLOPT_POST => ($type == 'GET') ? 0 : 1,
                CURLOPT_POSTFIELDS => $params,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return false;
            }

            return $response;
        }

        /**
         * Telegram::send_message
         * 
         * @param   $message    Message text or html
         * @return bool
         */
        public function send_message($id, $token, $message = '')
        {
            if (empty($message)) {
                return false;
            }

            $params = array(
                'chat_id'       => $id,
                'text'          => $message,
                'parse_mode'    => 'HTML'
            );
            return $this->request("bot{$token}/sendMessage", $params, 'POST');
        }

        /**
         * Telegram::send_message
         * 
         * @param   $message    Message text or html
         * @return bool
         */
        public function send_photo($id, $token, $url = '', $caption = '')
        {
            if (empty($url)) {
                return false;
            }

            $params = array(
                'chat_id'       => $id,
                'photo'         => $url,
                'caption'       => $caption,
                'parse_mode'    => 'HTML'
            );
            return $this->request("bot{$token}/sendPhoto", $params, 'POST');
        }
    }
}
