<?php
    include_once 'BaseSearchRequest.php'; 
    
    class GetLayoutsRequest extends BaseSearchRequest
    {
        /* @var $Layout String */
        public $Country;
        
        public $qaQueryHeader = null;
                 
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

            if(HelperMethods::isNullOrWhiteSpace($this->Country))
            {
                \array_push($errors, 'The country is not specified.');
            }

            return \count($errors) === 0;
        }
    }