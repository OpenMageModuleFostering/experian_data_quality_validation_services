<?php
    class QARefine
    {
        private $Moniker;
        private $Refinement;
        private $Layout;
        private $Threshold;
        private $Timeout;
        private $Localisation = \FALSE;
        private $FormattedAddressInPicklist = \FALSE;

        public function __construct($monikerField, $refinementField, $layoutField, $thresholdField, $timeoutField) 
        {
           $this->Moniker = $monikerField;
           $this->Refinement = $refinementField;
           $this->Layout = $layoutField;
           $this->Threshold = $thresholdField;
           $this->Timeout = $timeoutField;
        }
    }