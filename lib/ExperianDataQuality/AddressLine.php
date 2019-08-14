<?php
include_once 'LineContentType.php';

    class AddressLine
    {
        public $Label;
        public $Line;
        public $LineContent;
        public $DataplusGroup;
        public $Overflow = \FALSE; 
        public $Truncated = \FALSE;

        public function __construct($response) 
        {
            $this->Label = isset($response->Label) ? $response->Label : \FALSE;
            $this->Line = isset($response->Line) ? $response->Line : \FALSE;            
            $this->LineContent = isset($response->LineContent) ?  $response->LineContent : LineContentType::ADDRESS;
            $this->DataplusGroup = isset($response->DataplusGroup) ? $response->DataplusGroup : \FALSE;  
        }
    }