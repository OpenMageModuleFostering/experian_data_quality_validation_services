<?php
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/ServiceFactory.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/CanSearchRequest.php');
    include_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetLayoutsRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/RefineRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetAddressRequest.php');
    
    class EDQ_AddressValidation_Model_Service
    {     
        public function getLayouts(GetLayoutsRequest $getLayoutsRequest)
        {  
            $service = ExperianDataQuality_ServiceFactory::createService();
            return $service->doGetLayouts($getLayoutsRequest);                   
        }
        
        public function canSearch(CanSearchRequest $canSearchRequest, $service = NULL, $token = NULL) 
        {
            $service = ExperianDataQuality_ServiceFactory::createService($service, $token);
            return $service->doCanSearch($canSearchRequest);
        }
        
        public function searchAction(SearchRequest $searchRequest, $secondRound = false, $searchAddress = "", $isSingleLine = FALSE)
        {        
            try 
            {  
                $searchString = '';
                if($searchAddress === "") 
                {
                     $searchString = $this->_parseDoSearchRequest($searchRequest);
                } 
                else 
                {
                    $asSearch = explode(",", $searchAddress);
                    $bFirst = TRUE;
                    
                    if (isset($asSearch))
                    {
                        if (is_array($asSearch))
                        {
                            foreach ($asSearch AS $sSearch)
                            {
                                if (!$bFirst)
                                {
                                    $searchString = $searchString . '|';
                                }

                                $searchString = $searchString . $sSearch;
                                $bFirst = FALSE;
                            }
                        }
                        else
                        {
                            $searchString = $asSearch;
                        }
                    } 
                }
                
                $service = ExperianDataQuality_ServiceFactory::createService();
                $doSearchResult = $service->doSearch($searchRequest, $searchString);
                
                if($isSingleLine) 
                {
                    return $doSearchResult;
                }
                
                $cleanResult = array();
                $cleanResult['matchType'] = $doSearchResult->MatchType;
                
                if (($cleanResult['matchType'] === "Verified") || ($cleanResult['matchType'] === "InteractionRequired")) 
                {
                    $cleanResult['dpvStatus'] = $doSearchResult->DPVStatus;
                    $cleanResult['isMissingSubPrem'] = $doSearchResult->MissingSubPremise;
                    $cleanResult['cleanAddress'] = $doSearchResult->AddressLineDictionary;                    
                }
                else
                {
                    foreach($doSearchResult->PickListEntries as $item) {
                        $cleanResult['picklist'][] = array(
                            'partialtext' => $item->PartialAddress,
                            'addresstext' => $item->PickList,
                            'postcode' => $item->Postcode,
                            'moniker' => $item->Moniker,
                            'fulladdress' => ($item->FullAddress ? True : False)
                        );
                    }
                }
                
                if($cleanResult['matchType'] === 'Verified' || $cleanResult['matchType'] === 'InteractionRequired') 
                {
                    if($searchRequest->DataSet === 'CAN' && !$this->_aptCheck($cleanResult['cleanAddress'])) 
                    {
                        $cleanResult['matchType'] = 'AptAppend';
                    } 
                    else if($cleanResult['matchType'] === 'InteractionRequired' && $secondRound) 
                    {
                        $cleanResult['matchType'] = 'Verified';
                    }
                }            
            } 
            catch (Exception $ex) 
            {
                
                $cleanResult['matchType'] = 'error';
                $cleanResult['error'] = Mage::getSingleton('core/translate')
                                        ->translate(array('There was an error reaching the Experian Address Verification service. See Magento logs for details.'));
                $this->handleException($ex);
            }
            
            return $cleanResult;
        }

        public function refine(RefineRequest $refineRequest, $isSingleLine = FALSE)
        {        
            try
            {        
                $service = ExperianDataQuality_ServiceFactory::createService();
                $refineResult = $service->doRefine($refineRequest);
                
                if($isSingleLine)
                {
                    return $refineResult;
                }
                
                if($refineResult->Total === 1 && $refineResult->PickListEntries[0]->FullAddress === TRUE) 
                {
                    $getAddressRequest = new GetAddressRequest();
                    $getAddressRequest->DataSet = $refineRequest->DataSet; 
                    $getAddressRequest->Moniker = $refineRequest->Moniker;
                    
                    return $this->getAddress($getAddressRequest);
                }
                else 
                {
                    $returnResult['matchType'] = 'PremisesPartial';
                    foreach($refineResult->PickListEntries as $item) {
                        $returnResult['picklist'][] = array(
                            'partialtext' => $item->PartialAddress,
                            'addresstext' => $item->Picklist,
                            'postcode' => $item->Postcode,
                            'moniker' => $item->Moniker,
                            'fulladdress' => ($item->FullAddress ? True : False)
                        );
                    } 
                }
            }
            catch (Exception $ex) 
            {
                $returnResult['matchType'] = 'error';
                $returnResult['error'] = Mage::getSingleton('core/translate')
                                        ->translate(array('There was an error reaching the Experian Address Verification service. See Magento logs for details.'));
                $this->handleException($ex);
            }
            
            return $returnResult;
            
        }

        public function getAddress(GetAddressRequest $getAddressRequest, $isSingleLine = FALSE)
        {          
            $returnResult = array();
            try
            {
                $service = ExperianDataQuality_ServiceFactory::createService();
                $doGetAddress = $service->doGetAddress($getAddressRequest);
                
                if($isSingleLine) 
                {
                    return $doGetAddress;
                }
                         
                $returnResult['matchType'] = $doGetAddress->MatchType;
                $returnResult['dpvStatus'] = $doGetAddress->DPVStatus;
                $returnResult['cleanAddress'] = $doGetAddress->AddressLineDictionary; 
            } 
            catch (Exception $ex) 
            {
                $returnResult['matchType'] = 'error';
                $returnResult['error'] = Mage::getSingleton('core/translate')
                                        ->translate(array('There was an error reaching the Experian Address Verification service. See Magento logs for details.'));
                $this->handleException($ex);
            }
            
            return $returnResult;
        }
        
        /**
         * Get the searchText for a PremisesPartial result
         * 
         * @param Array $data The search data
         * @param string $apt The desired apartment number
         * @return mixed
         */
        public function getPremisesPartialSearchText($data, $apt)
        {	
            $partialAddress = $this->_getPartialText($data);
            $strippedZipText = $this->_stripZip($partialAddress);
            
            // Replace an instance of ',' with '# $apt,' from the $strippedZipText
            // Returns the full address search text
            return preg_replace("/,/", " # " . $apt . ",", $strippedZipText);
        }
		
        /**
         * Get the searchText for a StreetPartial result
         * 
         * @param Array $data The search data
         * @param string $street The desired street number
         * @return string
         */
        public function getStreetPartialSearchText($data, $street)
        {	
            $partialAddress = $this->_getPartialText($data);
            $strippedZipText = $this->_stripZip($partialAddress);
            
            // Returns the full address search text 
            return $street . " " . $strippedZipText;
        }
        
        public function getRegion($regionId)
        {
            return Mage::getModel('directory/region')->load($regionId)->getCode();
        }
        
        public function getRegionId($regionCode, $countryCode)
        {
            return Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode)->getId();
        }  
        
        public function insertIntoDataBase($id = NULL, $data = NULL)
        {
            try
            {
                $isBilling  = $this->_getSession()->getIsBilling();
                $isCheckout = $this->_getSession()->getIsCheckout();
                
                if($data === NULL)
                {
                    $data = $this->_getSession()->getEdqInfo();    
                    
                    if($data === NULL) { return; } 
                }
                
                if($id === NULL && $isCheckout)
                {
                    $id = $isBilling ? Mage::getSingleton('checkout/type_onepage')->getQuote()->getBillingAddress()->getQuoteId()
                                     : Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->getQuoteId();
                }
                
                if(!$id)
                {
                    throw new Exception(Mage::getSingleton('core/translate')
                                        ->translate(array('There is no Entity Id, therefore cannot save any data.')));
                }
                
                if($isCheckout) 
                {
                    $table = Mage::getModel('addressvalidation/quote');
                    $table->setAddress_type($isBilling ? 'Billing' : 'Shipping');
                }
                else 
                {
                    $table = Mage::getModel('addressvalidation/customer');
                }
                
                $table->setEntity_id($id);                
                $table->setMatchtype($this->_getMethods()->getValueOrEmptyString('matchType', $data));
                $table->setAdditionaldata($this->_getMethods()->getValueOrEmptyString('additionalData', $data));
                $table->save();
                
                $this->_getSession()->unsEdqInfo();
            } 
            catch (Exception $ex) 
            {
                $this->handleException($ex);
            }
        }
        
        public function handleException(Exception $ex)
        {
            Mage::logException($ex);
            Mage::log($ex->getMessage() . PHP_EOL . $ex->getTraceAsString(), null, 'ExperianDataQualityExceptions.log', true);
        }
		
        /**
         * Returns the partialtext from given data
         * 
         * @param Array $data Contains response data for returned picklist values from the OnDemand service
         * @return string $partialText The partial address captured so far
         */
        private function _getPartialText($data)
        {
        	$partialText = "";
			
            foreach($data["picklist"] as $add) 
            {	
                if(!$add["fulladdress"]) 
                {
                	$temp = $add["partialtext"];
                	
                	// Remove the range [ex. '1 ... 100 ' or '1 ... 100-'] from the front of the address
                	$temp = preg_split("/[0-9a-zA-Z]+\s[\.\.\.]+\s[0-9a-zA-Z]+(\s|-)/", $temp);
                	
                	// temp[0] will hold the range
                	// temp[1] will hold the partial address
                	$partialText = $temp[1];
                	
                	break;
                }
                else 
                {
                	$partialText = $data["picklist"][0]["partialtext"];
                	
                	break;
                }
            } 
            
            return $partialText;
        }

        /**
         * Remove the zip+4 code from a given address
         * 
         * @param string $address A full address
         * @return string The address without the zip+4 code
         */
        private function _stripZip($address)
        {
            return preg_replace("/-\d{4}$/", "", $address);
        }
        
        private function _aptCheck($address)
        {
            $lvr = $this->_getSettings()->getLVRCheckLineNumber();
            
            $isApt = (isset($address['Line' . ($lvr - 1)]) && $address['Line' . ($lvr - 1)] !== '') ? true : false;   

            if ($isApt) 
            {
                return preg_match("/\|?\d+\s*-\s*\d+/", implode('|', $address));
            }
            
            return true;            
        }
                
        private function _parseDoSearchRequest(SearchRequest $searchRequest)
        {
            if (isset($searchRequest) === FALSE)
            {
                throw new \Exception("searchRequest");
            }

            $delimiter = "|";
            
            switch ($searchRequest->DataSet)
            {
                case 'US':
                case 'CA': 
                case 'USA':
                case 'CAN':
                    return  $searchRequest->Street1 . $delimiter . 
                            $searchRequest->Street2 . $delimiter . 
                            $searchRequest->City    . $delimiter . 
                            $searchRequest->State   . $delimiter . 
                            $searchRequest->Postcode;
                case 'GB':
                case 'GBR':
                    return  $searchRequest->Street1 . $delimiter . 
                            $searchRequest->BuildingNumberOrName . $delimiter . 
                            $delimiter . 
                            $delimiter . 
                            $searchRequest->Postcode . $searchRequest->TownOrLacality;
                case 'DE':
                case 'DEU':
                    return  $searchRequest->Street1 . $delimiter . 
                            $searchRequest->BuildingNumberOrName . $delimiter . 
                            $delimiter . 
                            $delimiter . 
                            $searchRequest->Postcode . $searchRequest->TownOrLacality;
                case 'IE':
                case 'IRL':
                    return  $searchRequest->Street1 . $delimiter . 
                            $searchRequest->BuildingNumberOrName . $delimiter . 
                            $delimiter . 
                            $delimiter . 
                            $searchRequest->TownOrLacality;
                default:                    
                    return '';
            } 
        }    
        
        private function _getMethods()
        {
            return Mage::helper('addressvalidation/Methods');
        }
        
        private function _getSettings() 
        {
            return Mage::helper('addressvalidation/Settings');
        }
        
        private function _getSession()
        {
            return Mage::getSingleton('core/session');
        }
    }