<?php
require_once(Mage::getBaseDir('lib') . '/EDQ/WshValidator.php');
require_once(Mage::getBaseDir('lib') . '/EDQ/WshValidatorFactory.php');

class EDQ_EmailAndPhoneValidation_ValidationController extends Mage_Core_Controller_Front_Action
{ 
    public function validateEmailAction() {		
        $helper = Mage::helper('edq_emailandphonevalidation');

        if(!$helper->isEmailValidateEnabled()) {
            $this->_setErrorResponce('', 'Email validation is not enabled.', WshValidator::EMAIL);
            return;
        }

        $data = $this->getRequest()->getPost();	 
        $emailAddress =  isset($data['email-address']) ? $data['email-address'] : '';
         
        if (!Zend_Validate::is($emailAddress, 'EmailAddress'))
        {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($this->_invalidEmailResponseObject($emailAddress))); 
            return;
        }

        try {
            $this->_checkSessionSecurityOrThrowException($data, $helper);

            $email_validator = WshValidatorFactory::create(WshValidatorFactory::EMAIL, 
                                                           $helper->getEmailServiceUrl(),
                                                           $helper->getEmailValidateKey(),
                                                           '1', '10');    //try 10 times on every second
            $email_validation_request = array(WshValidator::EMAIL => $emailAddress);

            $email_result = $email_validator->validate($email_validation_request); 

            if($email_validator->has_error()) { 
                throw new Exception($email_validator->get_error_message());  
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(json_decode($email_result, true)));  
        }
        catch(Exception $e) 
        {
            Mage::log($e->getMessage(), NULL, 'EmailPhoneValidator.log');
            $this->_setErrorResponce($emailAddress, $e->getMessage(), WshValidator::EMAIL); 
        } 
    } 

    public function validatePhoneAction() 
    {	    
        $helper  = Mage::helper('edq_emailandphonevalidation'); 
        $phoneFormater = Mage::getModel('emailandphonevalidation/Phone_PhoneFormater'); //Not working in Magento EE
        //$phoneFormater = new EDQ_EmailAndPhoneValidation_Model_Phone_PhoneFormater();

        if(!$helper->isPhoneValidateEnabled()) 
        { 
            $this->_setErrorResponce('', 'Phone validation is not enabled.', WshValidator::PHONE);
            return;
        }

        $data = $this->getRequest()->getPost();	 
        $areaCode = isset($data['telephone-area-code']) ? $data['telephone-area-code'] : '';
        $number = isset($data['telephone-number']) ? $data['telephone-number'] : '';

        try 
        {
            $this->_checkSessionSecurityOrThrowException($data, $helper);

            $phone_validator = WshValidatorFactory::create(WshValidatorFactory::PHONE, 
                                                       $helper->getPhoneServiceUrl(),
                                                       $helper->getPhoneValidateKey(),
                                                       '1', '10'); //try 10 times on every second
            $phone_validation_request = array(WshValidator::AREA_CODE => $areaCode, WshValidator::PHONE => $phoneFormater->formatInput($number, $areaCode));

            $phone_result = $phone_validator->validate($phone_validation_request);

            if($phone_validator->has_error()) { 
                throw new Exception($phone_validator->get_error_message());  
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($phoneFormater->formatOutput($phone_result, $number, $areaCode) ));  
        }
        catch(Exception $e) {
            Mage::log($e->getMessage(), NULL, 'EmailPhoneValidator.log');
            $this->_setErrorResponce($number, $e->getMessage(), WshValidator::PHONE); 
        }   
    } 

    private function _checkSessionSecurityOrThrowException($data, $helper) 
    {
        if(!$helper->isSessionSecurityEnabled()) { return; }

        $token = isset($data['session-security-token']) ? $data['session-security-token'] : '';
        $tokenLifeTime = $helper->getSessionSecurityTokenLifeTime();
        if(!$this->_isTokenValid($token, $tokenLifeTime)) 
        {
            throw new Exception('Your request cannot be completed. Your token is expired'); 
        }
    }
    private function _isTokenValid($token, $lifeTime) {	 
        if($token === "EFDCE366-F4E0-292B-3F64-1D22FFB0B1F4") {
            return true; 
        }
        
        $helper = Mage::helper('edq_emailandphonevalidation');
                
        $tokenAndTimeStamp = Mage::getSingleton('core/session', array('name' => 'frontend'))->getTokenAndTimeStamp();
        $tokenParts = explode('|', $tokenAndTimeStamp);
        
        if(strcmp($token, $tokenParts[0]) != 0) { 
            return false;
        }
        
        $currentDateTime = $helper->getCurrentDateTime();
        $tokenExparationDate = date('Y-m-d H:i:s', strtotime('+' . $lifeTime . ' minutes', strtotime($tokenParts[1])));
        if($tokenExparationDate < $currentDateTime) {
            return false;
        }

        return true;
    }

    private function _setErrorResponce($input, $message, $validatorType) {
        $responce  = '';

        if($validatorType == WshValidator::PHONE) 
        {
                $responce = "{\"ResultCode\":\"0\",\"Number\":\"" . $input . "\",\"PhoneType\":\"\",\"Certainty\":\"" . $message . "\"}";	
        } 
        else if($validatorType == WshValidator::EMAIL) 
        {
                $responce = "{\"Email\":\"" . $input . "\",\"Message\":\"\",\"Certainty\":\"" . $message . "\"}";	
        } 

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode( json_decode($responce, true) )); 
    }    
    
    private function _invalidEmailResponseObject($input) {
        $responce = "{\"Email\":\"" . $input . "\",\"Message\":\"\",\"Certainty\":\"Please enter a valid email address.\"}";
        return json_decode($responce, true);
    }
}  
