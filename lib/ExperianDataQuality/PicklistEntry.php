<?php
    class PicklistEntry
    {
        public $Moniker;
        public $PartialAddress;
        public $PickList;
        public $Postcode;
        public $Score;
        public $FullAddress;
        public $Multiples;
        public $CanStep = \FALSE;
        public $AliasMatch = \FALSE;
        public $PostcodeRecoded = \FALSE;
        public $CrossBorderMatch = \FALSE;
        public $DummyPOBox = \FALSE;
        public $Name = \FALSE;
        public $Information = \FALSE;
        public $WarnInformation = \FALSE;
        public $IncompleteAddr = \FALSE;
        public $UnresolvableRange;
        public $PhantomPrimaryPoint = \FALSE;
        public $SubsidiaryData = \FALSE;
        public $ExtendedData = \FALSE;
        public $EnhancedData = \FALSE;

        public function __construct($response) 
        {
            $this->Moniker           = isset($response->Moniker)           ? $response->Moniker           : \FALSE;
            $this->PartialAddress    = isset($response->PartialAddress)    ? $response->PartialAddress    : \FALSE;
            $this->PickList          = isset($response->Picklist)          ? $response->Picklist          : \FALSE;
            $this->Postcode          = isset($response->Postcode)          ? $response->Postcode          : \FALSE;
            $this->Score             = isset($response->Score)             ? $response->Score             : \FALSE;
            $this->FullAddress       = isset($response->FullAddress)       ? $response->FullAddress       : \FALSE;
            $this->UnresolvableRange = isset($response->UnresolvableRange) ? $response->UnresolvableRange : \FALSE; 
        }
    }