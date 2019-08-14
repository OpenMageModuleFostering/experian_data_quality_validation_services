<?php 
    class EDQ_EmailAndPhoneValidation_Model_Phone_PhoneFormater
    {        
        public function formatInput($phoneNumber, $countryCode) 
        {
            $helper = Mage::helper('edq_emailandphonevalidation');

            if ($helper->isNullOrWhiteSpace($phoneNumber))
            {
                throw new Exception('Phone number is empty');  
            }

            if($helper->isNullOrEmptyString($countryCode))
            {
                throw new Exception('Country code is empty');  
            }
            
            $sanitizedPhone = $this->_sanitizePhoneNumber($phoneNumber);
            
             //For Australia and France Neustar is used the country code needs to be prepended to the phone number. 
             //This is per an email from Jignesh Patel sent to Kos on 12.05.2014
            if(strcmp($countryCode, '+33') == 0 || strcmp($countryCode, '+61') == 0) //France and Australia    
            {
                if(!$helper->startsWith($countryCode, $sanitizedPhone)) 
                {
                    if($helper->startsWith('0', $sanitizedPhone)) {
                        $sanitizedPhone = substr($sanitizedPhone, 1);
                    }

                    $sanitizedPhone = $countryCode . str_replace('+', '', $sanitizedPhone);
                }
            }

            return $sanitizedPhone;
        }

        public function formatOutput($response, $originalPhoneInput, $countryCode) 
        {
            $helper = Mage::helper('edq_emailandphonevalidation');

            $phoneResponse = json_decode($response, true);
            
            if(strcmp($phoneResponse["Certainty"], 'Unverified') == 0)
            {
                $phoneResponse["Number"] = $originalPhoneInput;
            } 
            else
            {
                $additionalPhoneinfo = !array_key_exists('AdditionalPhoneInfo', $phoneResponse) ? '' : $phoneResponse["AdditionalPhoneInfo"]; 

                if(is_array($additionalPhoneinfo) && array_key_exists('ValidatedPhoneNumber', $additionalPhoneinfo)) 
                {					
                    $phoneNumber = $additionalPhoneinfo['ValidatedPhoneNumber'];
                } 
                else 
                {
                    $phoneNumber = $phoneResponse["Number"]; 				
                }
                    
                switch ($countryCode)
                {
                    case "+44":
                        $phoneNumber = $this->_formatGBRPhoneNumber($phoneNumber);
                        break;
                    case "+61":
                        $phoneNumber = $this->_formatAUSPhoneNumber($phoneNumber);
                        break;
                    case "+33":
                        $phoneNumber = $this->_formatFRAPhoneNumber($phoneNumber);
                        break;
                    case "+1": 
                        $phoneNumber = $this->_formatUSAPhoneNumber($phoneNumber);
                        break;
                }

                $phoneResponse["Number"] = $phoneNumber;		
            }

            return $phoneResponse;
        }

        private function _sanitizePhoneNumber($phoneNumber) 
        {
            $helper = Mage::helper('edq_emailandphonevalidation');
            $sanitizedPhone = preg_replace('/[^\d.]/', '', $phoneNumber); 

            if($helper->startsWith('+', $phoneNumber)) 
            {
                $sanitizedPhone = '+' . $sanitizedPhone; 
            }
            else if($helper->startsWith('00', $phoneNumber)) 
            {
                $sanitizedPhone = '+' . substr($sanitizedPhone, 2);
            }

            return $sanitizedPhone;
        }

        private function _formatGBRPhoneNumber($number) 
        {	
            $formatedNumber = '+';
            $formatedNumber .= substr($number, 0, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 2, 4);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 6, 3);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 9, strlen($number) - 9); 

            return $formatedNumber;
        }

        private function _formatAUSPhoneNumber($number) {
            $formatedNumber = '+'; 
            $formatedNumber .= substr($number, 0, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= '('; 
            $formatedNumber .= substr($number, 2, 4);
            $formatedNumber .= ')';
            $formatedNumber .= ' '; 
            $formatedNumber .= substr($number, 6, 3);
            $formatedNumber .= ' '; 
            $formatedNumber .= substr($number, 9, strlen($number) - 9);  

            return $formatedNumber;
        }

        private function _formatFRAPhoneNumber($number)
        {
            $formatedNumber = '+';
            $formatedNumber .= substr($number, 0, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 2, 1);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 3, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 5, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 7, 2);
            $formatedNumber .= ' ';
            $formatedNumber .= substr($number, 9, strlen($number) - 9); 

            return $formatedNumber;
        }

        private function _formatUSAPhoneNumber($number) {
            $helper = Mage::helper('edq_emailandphonevalidation');
            
            $formatedNumber = '';
            if(strlen($number) > 8 && $helper->startsWith('+', $number)) 
            {
                $formatedNumber .= substr($number, 0, 2);
                $formatedNumber .= '-';
                $formatedNumber .= substr($number, 2, 3);
                $formatedNumber .= '-';
                $formatedNumber .= substr($number, 5, 3);
                $formatedNumber .= '-'; 
                $formatedNumber .= substr($number, 8);
            }
            else
            {
                $formatedNumber .= '(';
                $formatedNumber .= substr($number, 0, 3);
                $formatedNumber .= ')';
                $formatedNumber .= ' '; 
                $formatedNumber .= substr($number, 3, 3);
                $formatedNumber .= '-'; 
                $formatedNumber .= substr($number, 6); 
            }

            return $formatedNumber;
        }
    }
