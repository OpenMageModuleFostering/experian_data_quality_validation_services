<?php 
    class EDQ_EmailAndPhoneValidation_Model_Validate extends Mage_Core_Model_Config_Data
    {
        public function _afterSave()
        {         
            $moduleName = 'EDQ_EmailAndPhoneValidation';

            if(!Mage::helper('core')->isModuleOutputEnabled($moduleName)){
                return parent::_afterSave();
            } 
            
            $data = array(
                "enable_email_validate" => $this->getFieldsetDataValue('enable_email_validate'),
                "email_validation_token"  => $this->getFieldsetDataValue('email_validation_token'),
                "email_service_url"     => $this->getFieldsetDataValue('email_service_url'),
                "enable_phone_validate" => $this->getFieldsetDataValue('enable_phone_validate'),
                "phone_service_url"     => $this->getFieldsetDataValue('phone_service_url'),
                "phone_validation_token"  => $this->getFieldsetDataValue('phone_validation_token'),
                "enable_session_token_security" => $this->getGroupSectionFiedSetDataValue('security', 'enable_session_token_security'),
                "session_token_life_time_in_minutes" => $this->getGroupSectionFiedSetDataValue('security', 'session_token_life_time_in_minutes')
            );
                        
            if($data['enable_email_validate'] == 0 && $data['enable_phone_validate'] == 0 && $data['enable_session_token_security'] == 0) {
                return parent::_afterSave();
            }

            if($this->_areChangesMade($data)) {
                $errors = array();
                $validationResult = $this->_validateEDQFields($data, $errors);

                if($validationResult == false)
                {                   
                    for($i=0; $i<count($errors); $i++)
                    {
                        Mage::getSingleton('core/session')->addError($errors[$i]);
                    }
                    Mage::throwException('The configuration has not been saved.');
                }
            } 
            return parent::_afterSave();
        }
        
        private function getGroupSectionFiedSetDataValue($section, $key) {
            if(!$this->hasData('groups')) {
                return '';
            }
            
            $groupData = $this->getData('groups');
            
            if(array_key_exists($section, $groupData)) {
                if(array_key_exists('fields', (array)$groupData[$section])) {
                    
                    $fields = (array)$groupData[$section]['fields'];
                    if(array_key_exists($key, $fields)) {
                        return $fields[$key]['value'];                        
                    }       
                }
            }     
            
            return '';
        }

        private function _areChangesMade($data) {
            $helper = Mage::helper('edq_emailandphonevalidation');
            $areAnyChangesMade = true;

            if((bool) $data['enable_email_validate']        == $helper->isEmailValidateEnabled()
            && (bool) $data['enable_phone_validate']        == $helper->isPhoneValidateEnabled()            
            && (bool) $data['enable_session_token_secuiry'] == $helper->isSessionSecurityEnabled()
            && strcmp($data['email_validation_token'],              $helper->getEmailValidateKey())  == 0
            && strcmp($data['email_service_url'],                   $helper->getEmailServiceUrl())   == 0
            && strcmp($data['phone_service_url'],                   $helper->getPhoneServiceUrl())   == 0
            && strcmp($data['phone_validation_token'],              $helper->getPhoneValidateKey())  == 0 
            && strcmp($data['session_token_life_time_in_minutes'],  $helper->getSessionSecurityTokenLifeTime())  == 0)     
            { $areAnyChangesMade = false; }

            return $areAnyChangesMade;
        }   

        private function _validateEDQFields($data, &$errors) {
            $isEmailValidationSet = true;
            $isPhoneValidationSet = true;
            $isSecuritySet = true;

            if($data['enable_email_validate'] == 1) {
                if($this->_isNullOrEmptyString($data['email_service_url'])) {
                    array_push($errors, 'Email service url cannot be empty.');
                    $isEmailValidationSet = false;
                }

                if($this->_isNullOrEmptyString($data['email_validation_token'])) {
                    array_push($errors, 'Email validation token cannot be empty.');
                    $isEmailValidationSet = false;
                }

                if($isEmailValidationSet) {
                    $emailInput = json_encode(array('Email' => 'test_email@test.com' ));
                    $emailRequest = $this->_createRequest($data['email_service_url'], 'post', $data['email_validation_token'], $emailInput); 
                    $emailResponse = curl_exec($emailRequest);
                    $getEmalHttpStatus = curl_getinfo($emailRequest, CURLINFO_HTTP_CODE);
                    curl_close($emailRequest); 
                    $isEmailValidationSet = $this->_handleHttpStatus($getEmalHttpStatus, $errors, 'Email');
                }
            }

            if($data['enable_phone_validate'] == 1) {
                if($this->_isNullOrEmptyString($data['phone_service_url'])) {
                    array_push($errors, 'Phone service url cannot be empty.');
                    $isPhoneValidationSet = false;
                }

                if($this->_isNullOrEmptyString($data['phone_validation_token'])) {
                    array_push($errors, 'Phone validation token cannot be empty.');
                    $isPhoneValidationSet = false;
                }

                if($isPhoneValidationSet) {
                    $phoneInput = json_encode(array('DefaultCountryCode' => 'USA' , 'Number' => '+123456789'));   
                    $phoneRequest = $this->_createRequest($data['phone_service_url'], 'post', $data['phone_validation_token'], $phoneInput); 
                    $phoneResponse = curl_exec($phoneRequest);
                    $getPhoneHttpStatus = curl_getinfo($phoneRequest, CURLINFO_HTTP_CODE);
                    curl_close($phoneRequest);  
                    $isPhoneValidationSet = $this->_handleHttpStatus($getPhoneHttpStatus, $errors, 'Phone');
                }
            }

            if($data['enable_session_token_security'] == 1) {
                if($this->_isNullOrEmptyString($data['session_token_life_time_in_minutes'])) {
                    array_push($errors, 'Session Token life time cannot be empty');
                    $isSecuritySet = false;
                }

                if($isSecuritySet) {
                    if(!is_numeric($data['session_token_life_time_in_minutes'])) {
                        array_push($errors, 'Session Token lifetime needs to be numeric.');
                        $isSecuritySet = false;
                    } else if($data['session_token_life_time_in_minutes'] < 0) {
                        array_push($errors, 'Session Token lifetime cannot be less then zero.');
                        $isSecuritySet = false;
                    }
                }
            }
            return ($isPhoneValidationSet && $isEmailValidationSet && $isSecuritySet);
        } 

        private function _createRequest($url, $method, $token, $data = ''){
            $request = curl_init($url);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($method));         
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);    
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($request, CURLOPT_VERBOSE, 1);
            curl_setopt($request, CURLOPT_HEADER, 1);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json',
                'Auth-Token: '.$token
            ));

            return $request;
        }

        private function _handleHttpStatus($http_status, &$errors, $prefix){
            switch ($http_status){
                case 200:
                case 204:
                    return true;
                case 409:
                    array_push($errors, $prefix.' Service timeout.'); 
                    return false;
                case 401:
                    array_push($errors, "Invalid ".$prefix." Service Url or Validation Token");  
                    return false; 
                case 404:
                    array_push($errors, 'The requested '.$prefix.' resource could not be found but may be available again in the future. Subsequent requests by the client are permissible.');   
                    return false; 
                case 500: 
                    array_push($errors, $prefix.' Service exception.');    
                    return false;  
                default: 
                    array_push($errors, "Unexpected HTTP status {$http_status} from ".$prefix." service.");   
            }
        }

        private function _isNullOrEmptyString($fieldValue){
            return (!isset($fieldValue) || trim($fieldValue)==='');
        }
    }
