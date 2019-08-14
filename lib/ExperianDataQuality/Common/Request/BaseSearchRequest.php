<?php  
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';
    /**
    * Base search request data for ProOnDemand WS.
    */
    abstract class BaseSearchRequest
    {        
        /* @var $DataSet String */
        public $DataSet;

        /* @var $PromptSet String */
        public $PromptSet;

        /* @var $Engine String */
        public $Engine;
        
         /* @var $Layout String */
        public $Layout;
        
        /* @var $DataCenter String */
        public $DataCenter;
        
        /* @var $Service String */
        public $Service;

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
                \array_push($errors, 'The DataSet is not specified');
            } 

            return \count($errors) === 0;
        }

        public function promptSetType() 
        {
            return 'Default';
        }

        public function engineEnumType()
        {  
            /*
            if(HelperMethods::isNullOrWhiteSpace($this->Engine) !== \TRUE)
            { 
                if(EngineEnumType::isValidValue($this->Engine) === \FALSE)
                {
                    $message = "Engine enum type " . $this->Engine . " is not supported.";
                    throw new \Exception($message);
                }

                return $this->Engine;
            }

            return GlobalSettings::instance()->defaultEngineEnumType($this->DataSet);
             * */            
        }
    }