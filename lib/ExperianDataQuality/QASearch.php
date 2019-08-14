<?php
    class QASearch
    {
        private $Country;
        private $Engine;
        private $Layout;
        private $Search;
        private $FormattedAddressInPicklist;
        private $Localisation;

        public function __construct($countryField, $engineField, $layoutField, $searchField, $localisationField = \NULL) 
        {
            $this->Country = $countryField;
            $this->Engine = $engineField;
            $this->Layout = $layoutField;
            $this->Search = $searchField;

            if(isset($localisationField))
            {
                $this->Localisation = $localisationField;
            }

            $this->FormattedAddressInPicklist = \FALSE;
        }
    }