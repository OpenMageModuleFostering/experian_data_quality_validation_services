<?php
    class QACanSearch
    {
        private $Country;
        private $Engine;
        private $Layout;
        private $Localisation;

        public function __construct($countryField, $engineField, $layoutField, $localisationField = \NULL) 
        {
          $this->Country = $countryField;
          $this->Engine = $engineField;
          $this->Layout = $layoutField;
          $this->Localisation = $localisationField;
        }        
    }