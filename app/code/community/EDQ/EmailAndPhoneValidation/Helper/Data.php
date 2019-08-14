<?php
class EDQ_EmailAndPhoneValidation_Helper_Data extends Mage_Core_Helper_Abstract
{ 
    /**
     *  Config path to enable email validation
     */
    const XML_PATH_EMAIL_VALIDATION_ENABLED = 'experiandataquality_emailandphonevalidation/settings/enable_email_validate';
    /**
     *  Config path to email service url
     */
    const XML_PATH_EMAIL_SERVICE_URL = 'experiandataquality_emailandphonevalidation/settings/email_service_url';
    /**
     *  Config path for email validation key
     */
    const XML_PATH_EMAIL_VALIDATION_TOKEN = 'experiandataquality_emailandphonevalidation/settings/email_validation_token';
	/**
     *  Config path to enable phone validation
     */
    const XML_PATH_PHONE_VALIDATION_ENABLED = 'experiandataquality_emailandphonevalidation/settings/enable_phone_validate';
	/**
     *  Config path to phone service url
     */
    const XML_PATH_PHONE_SERVICE_URL = 'experiandataquality_emailandphonevalidation/settings/phone_service_url';
    /**
     *  Config path for phone validation key
     */
    const XML_PATH_PHONE_VALIDATION_TOKEN = 'experiandataquality_emailandphonevalidation/settings/phone_validation_token';
    /**
     *  Config path to phone default country
     */
    const XML_PATH_PHONE_DEFAULT_COUNTRY = 'experiandataquality_emailandphonevalidation/settings/phone_default_country';
    /**
     *  Config path to enable select country options
     */
    const XML_PATH_PHONE_COUNTRY_SELECTION_ENABLED = 'experiandataquality_emailandphonevalidation/settings/use_phone_country_selection';
    /**
    *  Config path for session token live time in minutes
     */
    const XML_PATH_ENABLE_SESSION_TOKEN_SECURITY = 'experiandataquality_emailandphonevalidation/security/enable_session_token_security';
    /**
     *  Config path for session token live time in minutes
     */
    const XML_PATH_SESSION_TOKEN_LIVE_TIME = 'experiandataquality_emailandphonevalidation/security/session_token_life_time_in_minutes';    

    /**
     * Check if Email Validate is enabled
     *
     * @return boolean
     */
    public function isEmailValidateEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATION_ENABLED);
    }

	/**
     * Check if Phone Validate is enabled
     *
     * @return boolean
     */
    public function isPhoneValidateEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_PHONE_VALIDATION_ENABLED);
    }

	/**
     * Retrieve Email Validation key
     *
     * @return string
     */
    public function getEmailValidateKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_VALIDATION_TOKEN);
    }

    /**
     * Retrieve Phone Validation key
     *
     * @return string
     */
    public function getPhoneValidateKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHONE_VALIDATION_TOKEN);
    }

    /**
     * Retrieve Email Service Url
     *
     * @return string
     */
    public function getEmailServiceUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_EMAIL_SERVICE_URL);
    }

    /**
     * Retrieve Phone Service Url
     *
     * @return string
     */
    public function getPhoneServiceUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHONE_SERVICE_URL);
    }

    /**
     * Check if Session Security is enabled
     *
     * @return boolean
     */
    public function isSessionSecurityEnabled() 
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ENABLE_SESSION_TOKEN_SECURITY);
    }

    /**
     * Check if Phone Country Selection is enabled
     *
     * @return boolean
     */
    public function isPhoneCountrySelectionEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_PHONE_COUNTRY_SELECTION_ENABLED);
    }

    /**
     * Retrieve Phone Default Country
     *
     * @return string
     */
    public function getPhoneDefaultCountry()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHONE_DEFAULT_COUNTRY);
    }

    /**
     * Retrieve Token Life Time
     *
     * @return string
     */
    public function getSessionSecurityTokenLifeTime() 
    {
        return Mage::getStoreConfig(self::XML_PATH_SESSION_TOKEN_LIVE_TIME);
    }
    /**
     * Returns current date and time based on Magento time zone
     *
     *@return date
     */
    public function getCurrentDateTime() 
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $date = date('Y-m-d H:i:s', $currentTimestamp);
        return $date;
    }

    /**
     * Check if string is null or empty
     *
     *@return bool
     */
    public function isNullOrEmptyString($string) 
    {
        return (!isset($string) || trim($string)==='');
    }

    /**
     * Check if string is null or it contains only white spaces
     *
     *@return bool
     */
    public function isNullOrWhiteSpace($string) 
    {
        return ($this->isNullOrEmptyString($string) || strlen(trim($string)) == 0);
    }
    
    /**
     * Check if heystack begins with begins with needle
     *
     *@return bool
     */
    public function startsWith($needle, $haystack)
    {
        $beginingToCompare = substr($haystack, 0, strlen($needle));
        return (strcmp($beginingToCompare, $needle) == 0);
    }

    /**
     * Returns DataSet based on country code
     *
     *@return string
     */
    public function getDataSetByCountryCode($code)
    {
        switch ($code)
        {
            case "+1":
                return 'USA';
            case "+44":
                return 'GBR';
            case "+61":
                return 'AUS';
            case "+33":
                return 'FRA';
            default:
                return false;
        }
    }
}