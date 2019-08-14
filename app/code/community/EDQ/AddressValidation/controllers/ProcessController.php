<?php 
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/RefineRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetAddressRequest.php'); 
	
    /**
     * ProcessController
     * 
     * EDQ_AddressValidation controller for frontend touchpoints
     */
    class EDQ_AddressValidation_ProcessController extends Mage_Core_Controller_Front_Action
    {            
        public function searchSingleLineAction()
        {
            try 
            { 
                $data = $this->getRequest()->getParams();

                if(!$this->getRequest()->getPost() && empty($data)) 
                {    
                    throw new Exception($this->_getTranslation('Request parameters are empty or the request is not POST'));
                }

                $searchRequest = new SearchRequest(); 
                $searchRequest->Street1              = $this->_getMethods()->getValueOrEmptyString('Street1', $data);
                $searchRequest->BuildingNumberOrName = $this->_getMethods()->getValueOrEmptyString('BuildingNumberOrName', $data);
                $searchRequest->TownOrLacality       = $this->_getMethods()->getValueOrEmptyString('Town', $data);
                $searchRequest->Postcode             = $this->_getMethods()->getValueOrEmptyString('Postcode', $data);
                $searchRequest->DataSet              = $this->_getMethods()->getValueOrEmptyString('DataSet', $data);

                $searchResult = $this->_getService()->searchAction($searchRequest, FALSE, '', TRUE); 
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($searchResult));
            } 
            catch (Exception $ex) 
            {
                $searchResult = array('error' => $ex->getMessage());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($searchResult));
            }
        }
        
        public function refineSingleLineAction()
        {
            try 
            {
                $data = $this->getRequest()->getParams();

                if(!$this->getRequest()->getPost() && empty($data)) 
                { 
                    throw new Exception($this->_getTranslation('Request parameters are empty or the request is not POST'));
                }

                $refineRequest = new RefineRequest();
                $refineRequest->Moniker    = $this->_getMethods()->getValueOrEmptyString('Moniker', $data); 
                $refineRequest->Refinement = $this->_getMethods()->getValueOrEmptyString('Refinement', $data); 
                $refineRequest->DataSet    = $this->_getMethods()->getValueOrEmptyString('DataSet', $data);

                $refineResult = $this->_getService()->refine($refineRequest, TRUE);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($refineResult));
            } 
            catch (Exception $ex) 
            {
                $searchResult = array('error' => $ex->getMessage());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($searchResult));
            }   
        }
        
        public function formatSingleLineAction()
        {
            try 
            {
                $data = $this->getRequest()->getParams();

                if(!$this->getRequest()->getPost() && empty($data)) 
                { 
                    throw new Exception($this->_getTranslation('Request parameters are empty or the request is not POST'));
                }

                $getAddressRequest = new GetAddressRequest();
                $getAddressRequest->Moniker = $this->_getMethods()->getValueOrEmptyString('Moniker', $data);
                $getAddressRequest->DataSet = $this->_getMethods()->getValueOrEmptyString('DataSet', $data);

                $formatResult = $this->_getService()->getAddress($getAddressRequest, TRUE);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($formatResult));
            } 
            catch (Exception $ex) 
            {
                $searchResult = array('error' => $ex->getMessage());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($searchResult));
            } 
        }
        
        public function verifyAddressAction()
        {       
            $isBilling = false;
            $isCheckOut = false;
            $data = $this->_getData($isBilling, $isCheckOut); 
            
            $this->_executeAddressVerification($data, $isBilling, $isCheckOut);
        }
        
        public function verifyBillingAction()
        {                    
            $isBilling = true;
            $isCheckOut = true;
            
            $data = $this->_getData($isBilling, $isCheckOut);   
            $this->_executeAddressVerification($data, $isBilling, $isCheckOut);
        }
        
        public function verifyShippingAction()
        {            
            $isBilling = false;
            $isCheckOut = true;
                                           
            $data = $this->_getData($isBilling, $isCheckOut);   
            $this->_executeAddressVerification($data, $isBilling, $isCheckOut);
        }
        
        private function _executeAddressVerification($data, $isBilling, $isCheckOut) 
        {
            $this->_getSession()->setInvalidInfo(FALSE);
            $this->_getSession()->setIsCheckout($isCheckOut);
            $searchRequest = $this->_getMethods()->createSearchRequest($data);

            if(($searchRequest->DataSet === 'US' && $this->_getSettings()->areUSDataSetsEnabled()) 
            || ($searchRequest->DataSet === 'CA' && $this->_getSettings()->isCANEnabled()))
            {
                $searchResult = $this->_getService()->searchAction($searchRequest); 

                if($searchResult['matchType'] === 'Verified' 
                    || $searchResult['matchType'] === 'error' 
                    || ($searchResult['matchType'] === 'InteractionRequired' && !$this->_getSettings()->isUserInteractionEnabled())) 
                {
                    $this->_saveAndReturn($data, $searchResult);        
                } 
                else if(!$this->_getSettings()->isUserInteractionEnabled()) 
                {
                    $this->_saveAndReturn($data);
                } 
                else 
                {
                    $this->_getSession()->setIsBilling($isBilling);
                    $this->_getSession()->setCustomerData($data);
                    $this->_getSession()->setSearchResult($searchResult);
                    
                    $this->loadLayout();
                    
                    if(!$isCheckOut)
                    {                   
                        $this->_initLayoutMessages('customer/session');
                        $layout = $this->getLayout();
                        $navigationBlock = $layout->getBlock('customer_account_navigation');
                        if ($navigationBlock) 
                        {
                            $navigationBlock->setActive('customer/address');
                        }
                    }
                    
                    $this->renderLayout();
                    return;
                }
            } 
            else 
            {
                $this->_saveAndReturn($data);
            }
        }


        public function acceptAction()
        {
            $this->_saveAndReturn(
                $this->_getSession()->getCustomerData(),
                $this->_getSession()->getSearchResult());
        }

        public function refinePremisesAction()
        {            
            $data = $this->_getSession()->getCustomerData();
            $searchData = $this->_getSession()->getSearchResult();

            $apt = $this->getRequest()->getParam('refinetext');
            
            $searchRequest = $this->_getMethods()->createSearchRequest($data);
            $searchText = $this->_getService()->getPremisesPartialSearchText($searchData, $apt);

            $searchResult = $this->_getService()->searchAction($searchRequest, true, $searchText);

            if($searchResult['matchType'] === 'Verified' || $searchResult['matchType'] === 'error') 
            {
                $this->_getSession()->setInvalidInfo(FALSE);
                $this->_saveAndReturn($data, $searchResult);
                return;
            } 
            
            if($searchResult['matchType'] === 'PremisesPartial') 
            {
                $this->_getSession()->setInvalidInfo(TRUE);
                $this->_getSession()->setCustomerData($data);
                $this->loadLayout()->renderLayout();
                return;
            } 
            else
            {
                $this->_getSession()->setInvalidInfo(TRUE);
                $this->_getSession()->setCustomerData($data);
                $this->_getSession()->setSearchResult($searchResult);
                $this->loadLayout()->renderLayout();
                return;
            }            
        }

        public function refineStreetAction()
        {
            $data = $this->_getSession()->getCustomerData();
            $searchData = $this->_getSession()->getSearchResult();
            
            $buildingNum = $this->getRequest()->getParam('refinetext');
            $searchText = $this->_getService()->getStreetPartialSearchText($searchData, $buildingNum);
            $searchRequest = $this->_getMethods()->createSearchRequest($data);
            $searchResult = $this->_getService()->searchAction($searchRequest, true, $searchText);

            if($searchResult['matchType'] === 'Verified' || $searchResult['matchType'] === 'error') 
            {
                $this->_getSession()->setInvalidInfo(FALSE);
                $this->_saveAndReturn($data, $searchResult);
                return;
            } 
            else if($searchResult['matchType'] === 'StreetPartial') 
            {
             
                $this->_getSession()->setInvalidInfo(TRUE);
                $this->_getSession()->setCustomerData($data);
                $this->loadLayout()->renderLayout();
                return;
            } 
            else 
            {
            
                $this->_getSession()->setInvalidInfo(TRUE);
                $this->_getSession()->setCustomerData($data);
                $this->_getSession()->setSearchResult($searchResult);
                $this->loadLayout()->renderLayout();
                return;
            }
        }

        public function refineAction()
        {
            $data = $this->_getSession()->getCustomerData();
            $moniker = $this->getRequest()->getParam('moniker');
            
            $refineRequest = new RefineRequest();
            $refineRequest->DataSet    = $data['address']['country_id'];
            $refineRequest->Moniker    = $moniker;
            
            $searchResult = $this->_getService()->refine($refineRequest);

            if($searchResult['matchType'] === 'Verified' || $searchResult['matchType'] === 'error')
            {
                $this->_getSession()->setInvalidInfo(FALSE);
                $this->_saveAndReturn($data, $searchResult);
            } 
            else 
            {            
                $this->_getSession()->setInvalidInfo(TRUE);
                $this->_getSession()->setCustomerData($data);
                $this->_getSession()->setSearchResult($searchResult);
                $this->loadLayout()->renderLayout();
                return;
            }
        }
              
        public function formatAction()
        {
            $data = $this->_getSession()->getCustomerData(); 
            $moniker = $this->getRequest()->getParam('moniker');
                
            $getAddressRequest = new GetAddressRequest();
            $getAddressRequest->DataSet = $data['address']['country_id'];
            $getAddressRequest->Moniker = $moniker;
            
            $searchResult = $this->_getService()->getAddress($getAddressRequest);

            $this->_saveAndReturn($data, $searchResult);             
        }
        
        public function aptAddAction()
        {
            $searchResult = $this->_getSession()->getSearchResult();
            $aptNo = $this->getRequest()->getParam('refinetext');

            $aptIndex = 0;
            $aptLine = false;
            $size = count($searchResult['cleanAddress']);
            while(!$aptLine && $aptIndex < $size)
            {
                if(preg_match("/^\d+\s/", $searchResult['cleanAddress'][$aptIndex])) 
                {
                    $aptLine = true;
                    $searchResult['cleanAddress'][$aptIndex] = $aptNo . "-" . $searchResult['cleanAddress'][$aptIndex];
                }
                $aptIndex++;
            }
            $this->_saveAndReturn(
                    $this->_getSession()->getCustomerData(),
                    $searchResult);
        }

        public function originalAction()
        {
            $this->_saveAndReturn($this->_getSession()->getCustomerData());
        } 
        
        private function _getData($isBilling, $isCheckout)
        { 
            $returnData = array();
            
            if ($this->getRequest()->isPost()) 
            {
                if($isCheckout)
                {
                    $key = $isBilling ? 'billing' : 'shipping'; 
                    $addressId = $isBilling ? 'billing_address_id' : 'shipping_address_id';

                    $returnData['address'] = $this->getRequest()->getPost($key, array());                
                    $returnData['customerAddressId'] = $this->getRequest()->getPost($addressId, false);  

                    if(!empty($returnData['customerAddressId']))
                    {
                        $returnData['address'] = $this->_getMethods()->getRegisteredAddress($returnData['customerAddressId'], count($returnData['address']['street']));
                    }

                    if($isBilling && isset($returnData['address']['email']))
                    {
                        $returnData['address']['email'] = trim($returnData['address']['email']);
                    } 

                    if($isBilling)
                    {
                        Mage::getSingleton('checkout/type_onepage')->saveBilling($returnData['address'], $returnData['customerAddressId']);
                    }
                    else 
                    {
                        Mage::getSingleton('checkout/type_onepage')->saveShipping($returnData['address'], $returnData['customerAddressId']);
                    }
                }
                else 
                {
                    $address  = Mage::getModel('customer/address');                         
                    $addressForm = Mage::getModel('customer/form');
                    $addressForm->setFormCode('customer_address_edit')->setEntity($address);
                    $returnData['address'] = $addressForm->extractData($this->getRequest());  
                }
            }
            
            return $returnData;
        }
        
        private function _saveAndReturn($data, $searchResult = null)
        {
            $isCheckOut = $this->_getSession()->getIsCheckout();
            
            $edqInfo = $this->_getMethods()->createEdqInfo($searchResult, $data);            
            $this->_getSession()->setEdqInfo($edqInfo);
            
            if($edqInfo['matchType'] === 'error')
            {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($edqInfo));
                return;
            }
            
            if($isCheckOut) 
            {                          
                if(($data['address']['country_id'] === 'US' && $this->_getSettings()->areUSDataSetsEnabled()) 
                || ($data['address']['country_id'] === 'CA' && $this->_getSettings()->isCANEnabled()))
                {
                    $this->_getService()->insertIntoDataBase();
                }
            } 
            
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
        }
        
        private function _getService()
        {
            return Mage::getModel('addressvalidation/Service');
        }
        
        private function _getSettings() 
        {
            return Mage::helper('addressvalidation/Settings');
        }
        
        private function _getMethods()
        {
            return Mage::helper('addressvalidation/Methods');
        }
        
        private function _getSession()
        {
            return Mage::getSingleton('core/session');
        }
        
        private function _getTranslation($text)
        {
            return Mage::getSingleton('core/translate')->translate(array($text));
        }        
    }