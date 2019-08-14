<?php
include_once 'QAAddress.php';

    class Address
    {
        public $QAAddress;
        public $VerifyLevel;

        public function __construct($response)
        {
            $this->QAAddress = isset($response->QAAddress) ? new QAAddress($response->QAAddress) : \FALSE;
            $this->VerifyLevel = isset($response->VerifyLevel) ? $response->VerifyLevel : '';
        }
    }