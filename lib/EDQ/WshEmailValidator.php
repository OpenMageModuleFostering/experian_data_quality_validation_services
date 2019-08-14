<?php
    require_once('WshValidator.php');

    class WshEmailValidator extends WshValidator{
        function __construct($email_service_url, $email_validation_key, $sleep_seconds_between_requests, $retries){
            parent::__construct($email_service_url, $email_validation_key, $sleep_seconds_between_requests, $retries);
        }
    
        protected function _get_post_data($validation_request){ 
            return json_encode(array('Email' => $validation_request[parent::EMAIL]));
        }
    }
