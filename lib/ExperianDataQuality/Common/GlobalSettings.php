<?php 
    /**
     * Application settings.
     */
    class GlobalSettings 
    {
        private $settingsProvider;

        function __construct(Mage_Core_Helper_Abstract $settingsProvider)
        {
            $this->settingsProvider = $settingsProvider;        
        }
        
        public static function instance()
        {
            static $instance = \NULL;
            if($instance === \NULL)
            { 
                $instance = new GlobalSettings(Mage::helper('addressvalidation/Settings'));
            }

            return $instance;
        }
        
        public function getServiceProvide()
        {
            return $this->settingsProvider->getServiceType();
        }
        
        public function getOnDemandToken()
        {
            return $this->settingsProvider->getOnDemandToken();
        }
        
        public function getOnDemandEndpoint()
        {
            return $this->settingsProvider->getOnDemandEndpoint();
        }
        public function proOnDemandThreshold()
        {
            return '25';
        }

        public function proOnDemandTimeout()
        {
            return '1000';   
        }

        public function proOnDemandRequestTag()
        {
            return '';      
        }

        public function defaultLayout($dataSet)
        {
            switch ($dataSet)
            {
                case 'US':
                case 'USA':
                    if($this->getIsATIEnabled()) 
                    {
                        return $this->settingsProvider->getUSELayout();
                    }                    
                    return $this->settingsProvider->getUSALayout();
                case 'CA':
                case 'CAN':
                    return $this->settingsProvider->getCANLayout();
                case 'GB':
                case 'GBR':
                    return $this->settingsProvider->getGBRLayout();
                case 'DE':
                case 'DEU':
                    return $this->settingsProvider->getDEULayout();
                case 'IE':
                case 'IRL':
                    return $this->settingsProvider->getIRLLayout();
                default:                    
                    return '';
            }
        }
        
        public function defaultEngineEnumType($dataSet)
        {
            switch ($dataSet)
            {
                case 'US':
                case 'USA':
                case 'USE':    
                case 'CA':
                case 'CAN':
                    return EngineEnumType::VERIFICATION;
                case 'GB':
                case 'GBR':
                case 'DE':
                case 'DEU':
                case 'IE':
                case 'IRL':
                    return EngineEnumType::SINGLELINE;
                default:                    
                    return '';
            }
        }

        public function defaultPromptSetType()
        {
            return 'Default';
        }

        public function defaultFlatten()
        {
            return \TRUE;
        }

        public function defaultPromptSetSpecified()
        {
           return \TRUE;
        } 
        
        public function getIsATIEnabled()
        {
            return $this->settingsProvider->isATIEnabled(); 
        }
        
        public function getIsUsingProxy()
        {
            return $this->settingsProvider->isUsingProxy();
        }
        
        public function getProxyName()
        {
            return $this->settingsProvider->getProxyName();
        }
        
        public function getProxyPort()
        {
            return $this->settingsProvider->getProxyPort();
        }
        
        public function getProxyUser()
        {
            return $this->settingsProvider->getProxyUser();
        }
        
        public function getProxyPassword()
        {
            return $this->settingsProvider->getProxyPassword();
        }
    }