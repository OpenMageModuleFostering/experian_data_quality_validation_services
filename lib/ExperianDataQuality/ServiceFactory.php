<?php     
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/GlobalSettings.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/ProOnDemandService.php';
    
    class ExperianDataQuality_ServiceFactory 
    {       
        public static function createService($serviceType = NULL, $token = NULL)
        {
            $service = \NULL;
            
            if(HelperMethods::isNullOrWhiteSpace($serviceType)) 
            {
                $serviceType = GlobalSettings::instance()->getServiceProvide();
            }

            switch ($serviceType)
            {
                case 'proondemand':
                    $service = new ExperianDataQuality_ProOnDemandService($token);
                    break; 
                default:
                    $message = "Service type " . $serviceType . " is not supported.";
                    throw new \Exception($message);
            }

            return $service;
        }
    }
