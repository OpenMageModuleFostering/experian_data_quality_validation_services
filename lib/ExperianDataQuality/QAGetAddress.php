<?php
    class QAGetAddress
    {
        private $Layout;
        private $Moniker;
        private $Localisation = \FALSE;

        public function __construct($layoutField, $monikerField) 
        {
            $this->Layout = $layoutField;
            $this->Moniker = $monikerField;
        }
    }