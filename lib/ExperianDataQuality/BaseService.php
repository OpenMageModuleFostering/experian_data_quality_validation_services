<?php 
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetLayoutsRequest.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/CanSearchRequest.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/RefineRequest.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetAddressRequest.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/GlobalSettings.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/HelperMethods.php';
    include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/BaseSearchRequest.php';
    include_once 'AddressValidationParser.php';
    include_once 'QAGetLayouts.php';
    include_once 'QALayouts.php';
    include_once 'QACanSearch.php';
    include_once 'QASearchOk.php';
    include_once 'QASearch.php';
    include_once 'QASearchResult.php';
    include_once 'QARefine.php';
    include_once 'Picklist.php';
    include_once 'QAGetAddress.php';
    include_once 'Address.php';
    include_once 'EngineType.php';
    include_once 'EngineEnumType.php';
    
    abstract class ExperianDataQuality_BaseService
    {
        private static $parser = \NULL;        
        protected $client = \NULL;        
        
        public function __construct() 
        {             
            self::$parser = new ExperianDataQuality_AddressValidationParser();
        }
        
        public function doGetLayouts(GetLayoutsRequest $doGetLayoutsRequest)
        {
            $this->_checkForErrors($doGetLayoutsRequest);              
            $this->_setLocation();  
            
            $getLayoutsRequest = new QAGetLayouts($doGetLayoutsRequest->Country); 
            $qALayoutsResponse = new QALayouts($this->client->doGetLayouts($getLayoutsRequest)); 
            
            return self::$parser->parseDoGetLayouts($qALayoutsResponse);
        }

        public function doCanSearch(CanSearchRequest $canSearchRequest) 
        {
            $this->_checkForErrors($canSearchRequest);

            $layout = $this->_getLayout($canSearchRequest->DataSet, $canSearchRequest->Layout);             
            $engine = $this->_getEngineType($canSearchRequest->DataSet, $canSearchRequest->promptSetType());
            
            $this->_setLocation($canSearchRequest->Location);  
            
            $qACanSearch = new QACanSearch($canSearchRequest->DataSet, $engine, $layout);                            
            $qASearchOkResponse = new QASearchOk($this->client->doCanSearch($qACanSearch)); 

            return self::$parser->parseDoCanSearchResponse($qASearchOkResponse);
        }

        public function doSearch(BaseSearchRequest $baseRequest,  $searchAddress = "")
        {                         
            $this->_checkForErrors($baseRequest);
            
            $layout = $this->_getLayout($baseRequest->DataSet, $baseRequest->Layout);    
            $engine = $this->_getEngineType($baseRequest->DataSet, $baseRequest->promptSetType());            
                
            $this->_setLocation();  
            
            $iso3 = Mage::getModel('directory/country')->load($baseRequest->DataSet)->getIso3Code();            
            if($iso3 === 'USA' && GlobalSettings::instance()->getIsATIEnabled())
            {
                $iso3 = 'USE';
            }
            
            $qASearchRequest = new QASearch($iso3, $engine, $layout, $searchAddress);                
            $doSearchResponse = $this->client->doSearch($qASearchRequest);
            
            if($engine['_'] === EngineEnumType::VERIFICATION && ($doSearchResponse->VerifyLevel === 'Verified' || $doSearchResponse->VerifyLevel === 'InteractionRequired'))
            {
                $address = new Address($doSearchResponse);                
                return self::$parser->parseDoGetAddressResponse($address, $baseRequest->DataSet);  
            } 
            else 
            {            
                $qASearchResult = new QASearchResult($doSearchResponse); 
                return self::$parser->parseResponse($qASearchResult);
            }
        }

        public function doRefine(RefineRequest $refineRequest) 
        {
            $this->_checkForErrors($refineRequest);
            
            $layout = $this->_getLayout($refineRequest->DataSet, $refineRequest->Layout);    
            $threshold = GlobalSettings::instance()->proOnDemandThreshold();
            $timeout = GlobalSettings::instance()->proOnDemandTimeout();
            
            $this->_setLocation();  

            $doRefineRequest = new QARefine($refineRequest->Moniker, $refineRequest->Refinement, $layout, $threshold, $timeout);
            $PicklistResponse = new Picklist($this->client->doRefine($doRefineRequest));

            return self::$parser->ParseDoRefineResponse($PicklistResponse); 
        }

        public function doGetAddress(GetAddressRequest $getAddressRequest) 
        {
            $this->_checkForErrors($getAddressRequest);

            $layout = $this->_getLayout($getAddressRequest->DataSet, $getAddressRequest->Layout); 
            
            $this->_setLocation();            
            
            $addressRequest = new QAGetAddress($layout, $getAddressRequest->Moniker);
            $getAddressResponse = new Address($this->client->doGetAddress($addressRequest));

            return self::$parser->parseDoGetAddressResponse($getAddressResponse, $getAddressRequest->DataSet);  
        }

        private static function _getLayout($dataSet, $layout)
        {
            if(HelperMethods::isNullOrWhiteSpace($layout) === \FALSE)
            {
                 return $layout;
            } 

            return GlobalSettings::instance()->defaultLayout($dataSet);
        }

        //EngineType
        private static function _getEngineType($dataSet, $promptSetType = \NULL)
        {
            $flatten = GlobalSettings::instance()->defaultFlatten();
            $promptSetSpecified =  GlobalSettings::instance()->defaultPromptSetSpecified();
            $threshold =  GlobalSettings::instance()->proOnDemandThreshold();
            $timeout = GlobalSettings::instance()->proOnDemandTimeout();
            $value = GlobalSettings::instance()->defaultEngineEnumType($dataSet);             

            $engineType = new EngineType($flatten, $promptSetSpecified, $threshold, $timeout, $value);

            if(isset($promptSetType))
            {
                $engineType->EngineType['PromptSet'] = $promptSetType;
            }

            return $engineType->EngineType; 
        } 
                
        private function _checkForErrors($request)
        {
            if(!isset($request))
            {
                throw new \Exception("The $request cannot be null.");
            }
            
            $errors = array();
            if($request->isValid($errors) === \FALSE)
            {
                $message = \implode(" ", $errors);
                throw new \Exception($message);
            }
        } 
        
        protected abstract function _setLocation($location = NULL);
    }