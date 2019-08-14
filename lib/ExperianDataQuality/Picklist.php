<?php
include_once 'QAPicklist.php';

    class Picklist
    {
        public $QAPicklist;

        public function __construct($response) 
        {
            $this->QAPicklist = isset($response->QAPicklist) ? new QAPicklist($response->QAPicklist) : \FALSE;
        }
    }