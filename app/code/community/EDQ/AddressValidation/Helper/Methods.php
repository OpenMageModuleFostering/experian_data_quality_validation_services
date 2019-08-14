<?php
require_once(Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/SearchRequest.php');

class EDQ_AddressValidation_Helper_Methods extends Mage_Core_Helper_Abstract
{          
    public function getValueOrEmptyString($key, $array)
    {
        return array_key_exists($key, $array) ? $array[$key] : '';
    }
    
    public function getRegisteredAddress($customerAddressId, $sizeOfStreet)
    {
        $customerAddress = Mage::getModel('customer/address')->load($customerAddressId); 
        $returnAddress = array();

        if ($customerAddress->getId()) 
        {                
            $returnAddress['street'] = $customerAddress->getStreet();

            while(count($returnAddress['street']) < $sizeOfStreet) 
            {	
                $returnAddress['street'][]='';
            }
            $returnAddress['city'] = $customerAddress->getCity();
            $returnAddress['region_id'] = $customerAddress->getRegion_id();
            $returnAddress['postcode'] = $customerAddress->getPostcode();
            $returnAddress['country_id'] = $customerAddress->getCountry_id();
        }

        return $returnAddress;
    }
    
    public function createSearchRequest($data)
    {
        $searchRequest = new SearchRequest(); 
        $searchRequest->Street1  = $data['address']['street'][0];
        $searchRequest->Street2  = $data['address']['street'][1];
        $searchRequest->City     = $data['address']['city'];
        $searchRequest->State    = $this->_getService()->getRegion($data['address']['region_id']); 
        $searchRequest->Postcode = $data['address']['postcode'];
        $searchRequest->DataSet  = $data['address']['country_id'];

        return $searchRequest;
    }
    
    public function createEdqInfo($searchResult, &$data)
    {
        $edqInfo = array();
        
        if($searchResult != null && array_key_exists('matchType', $searchResult) && $searchResult['matchType'] !== 'error') 
        {
            $edqInfo['matchType'] = $searchResult['matchType'];

            if(isset($searchResult['cleanAddress']) && count($searchResult['cleanAddress']) < count($data['address']['street']) + 3)
            {
                $edqInfo['matchType'] = 'error';
                $edqInfo['error'] = $this->_getTranslation(
                                    'Invalid Layout. Make sure your layout conforms
                                     to having as many street address lines as
                                     your magento setup followed by three lines
                                     containing City, State, Zip.');
            }
            else
            {
                $key = 'Line';
                for($i = 0; $i < count($data['address']['street']); $i++) 
                {                        
                    $data['address']['street'][$i] = $searchResult['cleanAddress'][$key . $i];
                }
                $data['address']['city'] = $searchResult['cleanAddress'][$key . $i];
                $data['address']['region_id'] = $searchResult['cleanAddress'][$key . ($i + 1)];
                $data['address']['postcode'] = $searchResult['cleanAddress'][$key . ($i + 2)];                    
                $edqInfo['additionalData'] = '';
                for($j = $i + 3; $j < count($searchResult['cleanAddress']); $j++) 
                {
                    $edqInfo['additionalData'] .= $searchResult['cleanAddress'][$key . $j] . '|';
                }
            }
        } 
        else if($searchResult['matchType']) 
        {
            $edqInfo['matchType'] = 'error';
            $edqInfo['error'] = $searchResult['error'];
        } 
        else 
        {
            $edqInfo['matchType'] = 'bypass';
        }
        
        return $edqInfo;
    }
    
    private function _getService()
    {
        return Mage::getModel('addressvalidation/Service');
    }
    
    private function _getTranslation($text)
    {
        return Mage::getSingleton('core/translate')->translate(array($text));
    } 
}
