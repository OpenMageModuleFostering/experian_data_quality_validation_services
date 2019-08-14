<?php
include_once 'AddressLine.php';
include_once 'DPVStatusType.php';

    class QAAddress
    {
        public $AddressLine = array();
        public $Overflow;
        public $Truncated;
        public $DPVStatus;
        public $DPVStatusSpecified;
        public $MissingSubPremise;

        public function __construct($response) 
        {  
            if(isset($response->AddressLine) === \FALSE)
            {
                $this->AddressLine = array();
            }
            else 
            {
                if(is_array($response->AddressLine))
                {
                    foreach ($response->AddressLine as $value) 
                    {
                        \array_push($this->AddressLine, new AddressLine($value));
                    }
                }
                else 
                {
                    \array_push($this->AddressLine, new AddressLine($response->AddressLine));
                }
            }     

            $this->Overflow = \FALSE;
            $this->Truncated = \FALSE;            
            $this->DPVStatus = isset($response->DPVStatus) ? $response->DPVStatus : \FALSE;
            $this->DPVStatusSpecified = isset($response->DPVStatusSpecified) ? $response->DPVStatusSpecified : DPVStatusType::DPVNOTCONFIRMED;
            $this->MissingSubPremise = isset($response->MissingSubPremise) ? $response->MissingSubPremise : \FALSE;
        }
    }