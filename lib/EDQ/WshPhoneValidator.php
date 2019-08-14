<?php
    require_once('WshValidator.php');

    class WshPhoneValidator extends WshValidator{
        function __construct($phone_service_url, $phone_validation_key, $sleep_seconds_between_requests, $retries){
            parent::__construct($phone_service_url, $phone_validation_key, $sleep_seconds_between_requests, $retries);
        }

        protected function _get_post_data($validation_request){
            return json_encode(array('DefaultCountryCode' => $validation_request[parent::AREA_CODE], 'Number' => $validation_request[parent::PHONE]));
        }
    }
