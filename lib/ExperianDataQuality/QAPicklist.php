<?php
include_once 'Picklist.php';
include_once 'PicklistEntry.php';

    class QAPicklist
    { 
        public $FullPicklistMoniker;
        public $PicklistEntry = array();
        public $Prompt;
        public $Total;
        public $AutoFormatSafe = \FALSE;
        public $AutoFormatPastClose = \FALSE;
        public $AutoStepinSafe = \FALSE;
        public $AutoStepinPastClose = \FALSE;
        public $LargePotential = \FALSE;
        public $MaxMatches = \FALSE;
        public $MoreOtherMatches = \FALSE;
        public $OverThreshold = \FALSE;
        public $Timeout = \FALSE;

        public function __construct($response) 
        {                
            if(isset($response->PicklistEntry) === \FALSE)
            {
                $this->PicklistEntry = array();
            }
            else 
            {
                if(is_array($response->PicklistEntry))
                {
                    foreach ($response->PicklistEntry as $value) 
                    {
                        \array_push($this->PicklistEntry, new PicklistEntry($value));
                    }
                }
                else 
                {
                    \array_push($this->PicklistEntry, new PicklistEntry($response->PicklistEntry));
                }
            }            
            $this->FullPicklistMoniker = isset($response->FullPicklistMoniker) ? $response->FullPicklistMoniker : \FALSE;
            $this->Prompt = isset($response->Prompt) ? $response->Prompt : \FALSE;
            $this->Total  = isset($response->Total)  ? $response->Total  : \FALSE;      
        }
    }