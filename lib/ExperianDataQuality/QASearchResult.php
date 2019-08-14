<?php
include_once 'QAPicklist.php';
include_once 'VerifyLevelType.php';

    class QASearchResult
    {
        public $QAPicklist;
        public $QAAddress;
        public $VerificationFlags;
        public $VerificationFlagsType;

        public function __construct($response) 
        { 
            $this->QAPicklist            = isset($response->QAPicklist)        ? new QAPicklist($response->QAPicklist)        : FALSE;
            $this->QAAddress             = isset($response->QAAddress)         ? $response->QAAddress                         : FALSE;
            $this->VerificationFlags     = isset($response->VerificationFlags) ? $response->VerificationFlags                 : FALSE;
            $this->VerificationFlagsType = isset($response->VerifyLevel)       ? $response->VerifyLevel                       : VerifyLevelType::NONE;
        }
    }