<?php
    require_once('WshPhoneValidator.php');
    require_once('WshEmailValidator.php');

    class WshValidatorFactory{
        const EMAIL = 'email';
        const PHONE = 'phone';

        public static function create($validator_type, $serice_url, $validation_key, $sleep_seconds_between_requests, $retries){
            switch ($validator_type) {
                case self::EMAIL:
                    return new WshEmailValidator($serice_url, $validation_key, $sleep_seconds_between_requests, $retries);
                case self::PHONE:
                    return new WshPhoneValidator($serice_url, $validation_key, $sleep_seconds_between_requests, $retries);
                default:
                    throw new Exception("Unsupported WSH validator: {$validator_type}.");
            }
        }
    }
