<?php    
    include_once 'BaseSearchRequest.php';
    /*
     * Search request data for ProOnDemand WS.and open the template in the editor.
     */
    class SearchRequest extends BaseSearchRequest {
        /* @var $Street1 String */
        public $Street1;

        /* @var $Street2 String */
        public $Street2;

        /* @var $BuildingNumberOrName String */
        public $BuildingNumberOrName;

        /* @var $City String */
        public $City;

        /* @var $State String */
        public $State;

        /* @var $Postcode String */
        public $Postcode;

        /* @var $TownOrLacality String */            
        public $TownOrLacality;

        /**
         * Validates the request information
         *
         * @param string $errors Request errors.
         * @return True of False whether the data is valid.
         */
        public function isValid(&$errors) 
        {
            if (!isset($errors)) 
            {
                $errors = array();
            }

            if (HelperMethods::isNullOrWhiteSpace($this->Street1)
            &&  HelperMethods::isNullOrWhiteSpace($this->Street2)
            &&  HelperMethods::isNullOrWhiteSpace($this->BuildingNumberOrName)
            &&  HelperMethods::isNullOrWhiteSpace($this->City)
            &&  HelperMethods::isNullOrWhiteSpace($this->State)
            &&  HelperMethods::isNullOrWhiteSpace($this->Postcode)
            &&  HelperMethods::isNullOrWhiteSpace($this->TownOrLacality))                 
            {
                \array_push($errors, 'Please enter exact details.');

                return \FALSE;
            }             

            return \count($errors) === 0;
        }
    }