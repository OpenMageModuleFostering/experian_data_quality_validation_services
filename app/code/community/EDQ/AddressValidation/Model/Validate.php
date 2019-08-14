<?php
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/CanSearchRequest.php';
include_once Mage::getBaseDir('lib') . '/ExperianDataQuality/Common/Request/GetLayoutsRequest.php';
include_once 'DoCanSearchParameters.php';

class EDQ_AddressValidation_Model_Validate extends Mage_Core_Model_Config_Data
{
    public function _beforeSave()
    {
        $moduleName = 'EDQ_AddressValidation';

        if(!Mage::helper('core')->isModuleOutputEnabled($moduleName)){
            return parent::_beforeSave();
        } 

        $data = array(
            "ServiceType" => $this->getFieldsetDataValue('service_type'),
            
            "Token" => $this->getFieldsetDataValue('token'),
            "Endpoint" => $this->getFieldsetDataValue('endpoint'), 

            "IsProxyEnabled" => $this->getFieldsetDataValue('use_proxy'),
             
            "IsUSAEnabled" => $this->getFieldsetDataValue('usa_clean'),
            "USALayout" => $this->getFieldsetDataValue('usa_layout'),
            
            "IsATIEnabled" => $this->getFieldsetDataValue('enable_ati'),
            "USELayout" => $this->getFieldsetDataValue('use_layout'),

            "IsCANEnabled" => $this->getFieldsetDataValue('can_clean'),
            "CANLayout" => $this->getFieldsetDataValue('can_layout'),

            "IsGRBEnabled" => $this->getFieldsetDataValue('gbr_clean'),
            "GRBLayout" => $this->getFieldsetDataValue('gbr_layout'),

            "IsDEUEnabled" => $this->getFieldsetDataValue('deu_clean'),
            "DEULayout" => $this->getFieldsetDataValue('deu_layout'),
                                
            "IsIRLEnabled" => $this->getFieldsetDataValue('irl_clean'),
            "IRLLayout" => $this->getFieldsetDataValue('irl_layout'),
                
            "ProxyName" => $this->getFieldsetDataValue('proxyname'),
            "ProxyPort" => $this->getFieldsetDataValue('proxyport'),
                                
            "PorxyUser" => $this->getFieldsetDataValue('proxyuser'),
            "ProxyPassword" => $this->getFieldsetDataValue('proxypassword')
        );

        $errors = array();
        $validationResult = $this->ValidateEDQFields($data, $errors);

        if($validationResult == false)
        {    
            array_push($errors, $this->_getTranslation('The configuration has not been saved.'));
            $errorMessage = implode("\n", $errors);
            Mage::throwException($errorMessage);    
            return false;
        } 
        
        return parent::_beforeSave();
    }
    
    public function ValidateEDQFields($data, &$errors)
    {
        if($data['IsProxyEnabled'] == 1) 
        { 
            $this->_ifEmptyAddError($data['ProxyName'], $errors, $this->_getTranslation('Proxy URL')  . $this->_getTranslation(' cannot be empty.'));     
            $this->_ifEmptyAddError($data['ProxyPort'], $errors, $this->_getTranslation('Proxy Port') . $this->_getTranslation(' cannot be empty.'));
            $this->_ifEmptyAddError($data['PorxyUser'], $errors, $this->_getTranslation('Proxy Username') . $this->_getTranslation(' cannot be empty.'));
            $this->_ifEmptyAddError($data['PorxyUser'], $errors, $this->_getTranslation('Proxy Password') . $this->_getTranslation(' cannot be empty.'));
            
            if(count($errors) > 0) { return false; }
        }

        $doCanSearchParametersArray = array();
        
        $this->_insertParameterIfEnabledFor('USA', $data['IsUSAEnabled'],  $data['USALayout'], $doCanSearchParametersArray);
        $this->_insertParameterIfEnabledFor('USE', $data['IsATIEnabled'],  $data['USELayout'], $doCanSearchParametersArray);
        $this->_insertParameterIfEnabledFor('CAN', $data['IsCANEnabled'],  $data['CANLayout'], $doCanSearchParametersArray);
        $this->_insertParameterIfEnabledFor('GBR', $data['IsGRBEnabled'],  $data['GRBLayout'], $doCanSearchParametersArray);
        $this->_insertParameterIfEnabledFor('DEU', $data['IsDEUEnabled'],  $data['DEULayout'], $doCanSearchParametersArray);
        $this->_insertParameterIfEnabledFor('IRL', $data['IsIRLEnabled'],  $data['IRLLayout'], $doCanSearchParametersArray);
        
        if(count($doCanSearchParametersArray) == 0) { return true; }        
        
        if($data["ServiceType"] === 'proondemand')
        {          
            $this->_ifEmptyAddError($data['Token'], $errors, $this->_getTranslation('On Demand Token') .  $this->_getTranslation(' cannot be empty.'));
            $this->_ifEmptyAddError($data['Endpoint'], $errors, $this->_getTranslation('On Demand Endpoint') .  $this->_getTranslation(' cannot be empty.'));            
        } 
        
        if($data['IsUSAEnabled'] == 1 && $data['IsATIEnabled'] == 1) 
        {
            array_push($errors, $this->_getTranslation('Both USA Clean and Enabled ATI settings cannot be enabled at the same time.'));
        }
        
        if(count($errors) > 0) { return false; }
                        
        foreach ($doCanSearchParametersArray as $doCanSearchParameters)
        {           
            $soapFault = '';
            $soapString = '';

            $canSearchRequest = new CanSearchRequest();
            $canSearchRequest->DataSet = $doCanSearchParameters->Country;
            $canSearchRequest->Layout = $doCanSearchParameters->Layout;
            $canSearchRequest->Service = $data["ServiceType"];
            $canSearchRequest->Location = $data["Endpoint"];
                    
            try
            {
                $doCanSearchResult = $this->_getService()->canSearch($canSearchRequest, $data["ServiceType"], $data['Token']);  
            }
            catch (SoapFault $e)
            {
                $soapString = $e->faultstring;
                $soapFault = $e->faultcode;       
            }

            if(!$this->_isNullOrEmptyString($soapFault))
            {
                if(strcmp(strtoupper($soapFault), strtoupper('AuthorizationFailed')) == 0)
                {
                    array_push($errors,$this->_getTranslation('Your account is not authorized to use this Datacenter.'));
                    break;
                }
                else if(strcmp(strtoupper($soapFault), strtoupper('AuthenticationFailed')) == 0)
                {
                    if(HelperMethods::startsWith('User has reached maximum number of failed log on attempts', $soapString))
                    {
                        array_push($errors, $this->_getTranslation('User has reached maximum number of failed log on attempts.'));
                        break;
                    }
                    array_push($errors, $this->_getTranslation('Your On Demand Token is incorrect.'));
                    break;
                }
                else 
                {
                    array_push($errors, $this->_getTranslation('There was an error reaching the Experian Address Verification service. See Magento logs for details.'));
                    break;                        
                }   
            }
            else if(!$this->_isNullOrEmptyString($doCanSearchResult->ErrorCode) || !$this->_isNullOrEmptyString($doCanSearchResult->ErrorDetail))
            {
                array_push($errors, $doCanSearchParameters->Layout . $this->_getTranslation(' is an incorrect layout.'));
            }
        }
        
        return count($errors) === 0;
    }
        
    private function _insertParameterIfEnabledFor($country, $isEnabled, $layout, &$parameterArray)
    {
        if($isEnabled == 1) 
        {
            if($this->_isNullOrEmptyString($layout))
            {
                $action = 'get' . $country . 'Layout';
                if(is_callable(array($this->_getSettings(), $action)))
                {
                    $layout = $this->_getSettings()->$action();
                }               
            }
            array_push($parameterArray, new DoCanSearchParameters($country, $layout));
        }
    }
    
    private function _ifEmptyAddError($value, &$errors, $message)
    {
        if($this->_isNullOrEmptyString($value))
        {
            array_push($errors, $this->_getTranslation($message));
        }
    }

    private function _isNullOrEmptyString($fieldValue)
    {
        return (!isset($fieldValue) || trim($fieldValue)==='');
    }
    
    private function _getTranslation($message) 
    {
        return Mage::getSingleton('core/translate')->translate(array($message));
    }
    
    private function _getService()
    {
        return Mage::getModel('addressvalidation/Service');
    }

    private function _getSettings() 
    {
        return Mage::helper('addressvalidation/Settings');
    }
}