<?php  
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';
    /**
     * Holds the base address request data for ProOnDemand WS.
     */
    class BaseAddressRequest
    {
        /* @var $Moniker String */
        public $Moniker;

        /* @var $DataSet String */
        public $DataSet;
         
        /* @var $Layout String */
        public $Layout;
        
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

            if(HelperMethods::isNullOrWhiteSpace($this->DataSet))
            {
                \array_push($errors, 'The dateset is not specified.');
            }

            if(HelperMethods::isNullOrWhiteSpace($this->Moniker))
            {
                \array_push($errors, 'The moniker is not specified.');
            } 

            return \count($errors) === 0;
        }
    }