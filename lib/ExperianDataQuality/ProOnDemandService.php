<?php
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/GlobalSettings.php';
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';

    /**
    * ProOnDemand service. 
    */
    class ExperianDataQuality_ProOnDemandService extends ExperianDataQuality_BaseService implements ExperianDataQuality_IService
    { 
        public function __construct($token = \NULL) 
        {
            $wsdl = Mage::getBaseDir('lib') . '/ExperianDataQuality/ProOnDemandService.wsdl';
            
            if(HelperMethods::isNullOrWhiteSpace($token)) 
            {
                $token = GlobalSettings::instance()->getOnDemandToken();
            }
            
            $context = array(
                'http' =>  array( 'header' => 'Auth-Token: ' . $token)
            );
            
            if(GlobalSettings::instance()->getIsUsingProxy())
            {
                $this->client = new \SoapClient($wsdl,
                                            array(
                                                'soap_version' => SOAP_1_2,
                                                'trace' => \FALSE,  
                                                'exceptions' => \TRUE,
                                                'stream_context' => stream_context_create($context),                                               
                                                    'proxy_host'     => GlobalSettings::instance()->getProxyName(),
                                                    'proxy_port'     => GlobalSettings::instance()->getProxyPort(),
                                                    'proxy_login'    => GlobalSettings::instance()->getProxyUser(),
                                                    'proxy_password' => GlobalSettings::instance()->getProxyPassword()));
            }
            else 
            {
                $this->client = new \SoapClient($wsdl, 
                                            array(
                                                'soap_version' => SOAP_1_2,
                                                'trace' => \FALSE,  
                                                'exceptions' => \TRUE,
                                                'stream_context' => stream_context_create($context)));
            }
            
            if (\is_soap_fault($this->client))
            {
                $this->client = \NULL;
            }
            
            parent::__construct();
        } 
                
        protected function _setLocation($location = NULL)
        {
            if(HelperMethods::isNullOrWhiteSpace($location)) 
            {
                $location = GlobalSettings::instance()->getOnDemandEndpoint();
            }
            
            $this->client->__setLocation($location);
        }        
    }