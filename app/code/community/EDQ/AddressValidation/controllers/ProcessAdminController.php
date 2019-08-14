<?php
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/RefineRequest.php');
    require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetAddressRequest.php');
	
    /**
     * ProcessAdminController
     * 
     * EDQ_AddressValidation controller for backend touchpoints
     */
    class EDQ_AddressValidation_ProcessAdminController extends Mage_Adminhtml_Controller_Action
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
                $searchRequest->DataSet = $this->_getMethods()->getValueOrEmptyString('DataSet', $data);
                
                if($searchRequest->DataSet === 'US' || $searchRequest->DataSet === 'CA') 
                {
                    $searchRequest->Street1  = $this->_getMethods()->getValueOrEmptyString('Line0', $data);
                    $searchRequest->Street2  = $this->_getMethods()->getValueOrEmptyString('Line1', $data);
                    $searchRequest->City     = $this->_getMethods()->getValueOrEmptyString('Line2', $data);
                    $searchRequest->State    = $this->_getService()->getRegion($this->_getMethods()->getValueOrEmptyString('Line3', $data)); 
                    $searchRequest->Postcode = $this->_getMethods()->getValueOrEmptyString('Line4', $data);
                }
                else 
                {
                    $searchRequest->Street1              = $this->_getMethods()->getValueOrEmptyString('Street1', $data);
                    $searchRequest->BuildingNumberOrName = $this->_getMethods()->getValueOrEmptyString('BuildingNumberOrName', $data);
                    $searchRequest->TownOrLacality       = $this->_getMethods()->getValueOrEmptyString('Town', $data);
                    $searchRequest->Postcode             = $this->_getMethods()->getValueOrEmptyString('Postcode', $data);
                }

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
                
                if(is_array($formatResult) && array_key_exists('matchType', $formatResult) && $formatResult['matchType'] === 'error')
                {
                    throw new Exception($formatResult['error']);
                }
                                
                if($getAddressRequest->DataSet == 'US' || $getAddressRequest->DataSet == 'CA')
                {
                    $this->_collectAdditionalDataAndInserToDb($formatResult, $data);
                }
                
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($formatResult));
            } 
            catch (Exception $ex) 
            {
                $searchResult = array('error' => $ex->getMessage());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($searchResult));
            } 
        }
        
        private function _collectAdditionalDataAndInserToDb($searchResult, $data)
        {       
            $edqInfo = array();
            $edqInfo['matchType'] = $searchResult->MatchType;

            if(isset($searchResult->AddressLineDictionary) && count($searchResult->AddressLineDictionary) < 5)
            {
                $edqInfo['error'] = $this->_getTranslation(
                                    'Invalid Layout. Make sure your layout conforms
                                     to having as many street address lines as
                                     your magento setup followed by three lines
                                     containing City, State, Zip.');
                throw new Exception($edqInfo['error']);
            }
            else
            {
                $key = 'Line';                 
                $edqInfo['additionalData'] = '';
                $size = count($searchResult->AddressLineDictionary);
                for($j = 5; $j < $size; $j++) 
                {
                    $edqInfo['additionalData'] .= $searchResult->AddressLineDictionary[$key . $j] . '|';
                }                        
            }
            
            $getEdqInfoMatrixFor = 'getEdqInfoMatrixFor' . $data['id'];            
            $edqInfoArray = $this->_getSession()->$getEdqInfoMatrixFor();
                        
            if(is_array($edqInfoArray) && array_key_exists($data['address_id'], $edqInfoArray))
            {
                $edqInfoArray[$data['address_id']] = $edqInfo;
            }
            else 
            {
                $edqInfoArray = array();
                $edqInfoArray[$data['address_id']] = $edqInfo;
            }
                        
            $setEdqInfoMatrixFor = 'setEdqInfoMatrixFor' . $data['id'];
            $this->_getSession()->$setEdqInfoMatrixFor($edqInfoArray);
        }
        
        private function _getService()
        {
            return Mage::getModel('addressvalidation/Service');
        }
        
        private function _getMethods()
        {
            return Mage::helper('addressvalidation/Methods');
        }
        
        private function _getTranslation($text)
        {
            return Mage::getSingleton('core/translate')->translate(array($text));
        }

        protected function _isAllowed() { return true; } 
    }