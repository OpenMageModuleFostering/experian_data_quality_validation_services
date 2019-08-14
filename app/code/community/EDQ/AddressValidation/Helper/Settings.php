<?php
class EDQ_AddressValidation_Helper_Settings extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TO_SETTINGS         = 'experiandataquality_addressvalidation/settings/';
    const XML_KEY_SERVICE              = 'service_type';
    const XML_KEY_ENDPOINT             = 'endpoint';    
    const XML_KEY_TOKEN                = 'token'; 
    const XML_KEY_USER_INTERACTION     = 'user_interaction';    
    const XML_KEY_USA_CLEAN            = 'usa_clean';    
    const XML_KEY_USA_LAYOUT           = 'usa_layout';    
    const XML_KEY_USE_LAYOUT           = 'use_layout';
    const XML_KEY_CAN_CLEAN            = 'can_clean';    
    const XML_KEY_CAN_LAYOUT           = 'can_layout';    
    const XML_KEY_GBR_CLEAN            = 'gbr_clean';
    const XML_KEY_GBR_LAYOUT           = 'gbr_layout';    
    const XML_KEY_DEU_CLEAN            = 'deu_clean';
    const XML_KEY_DEU_LAYOUT           = 'deu_layout';    
    const XML_KEY_IRL_CLEAN            = 'irl_clean';    
    const XML_KEY_IRL_LAYOUT           = 'irl_layout';    
    const XML_KEY_MANUAL_ADDRESS_ENTRY = 'manual_address_entry';
    const XML_KEY_ENABLE_ATI           = 'enable_ati';
    const XML_KEY_USE_PROXY            = 'use_proxy';
    const XML_KEY_PROXYNAME            = 'proxyname';
    const XML_KEY_PROXYPORT            = 'proxyport';
    const XML_KEY_PROXYUSER            = 'proxyuser';
    const XML_KEY_PROXYPASSWORD        = 'proxypassword';
    const XML_KEY_LVR                  = 'lvr';
    const XML_KEY_IR_DISP              = 'ir_disp';
    
    public function getServiceType()
    {
        return $this->getSettingAsString(self::XML_KEY_SERVICE);
    }
    
    public function getOnDemandToken()
    {
        return $this->getSettingAsString(self::XML_KEY_TOKEN);
    }
    
    public function getOnDemandEndpoint()
    {
        return $this->getSettingAsString(self::XML_KEY_ENDPOINT);
    }
    
    public function isUserInteractionEnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_USER_INTERACTION);               
    }
    
    public function isATIEnabled() 
    {
        return $this->getSettingAsBoolean(self::XML_KEY_ENABLE_ATI);  
    } 
    
    public function isUSAEnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_USA_CLEAN);  
    }
    
    public function areUSDataSetsEnabled()
    {
        return $this->isATIEnabled() || $this->isUSAEnabled();
    }
    
    public function getUSALayout()
    {
        return $this->getSettingAsString(self::XML_KEY_USA_LAYOUT);               
    }
    
    public function getUSELayout()
    {
        return $this->getSettingAsString(self::XML_KEY_USE_LAYOUT);   
    }

    public function isCANEnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_CAN_CLEAN);  
    }
       
    public function getCANLayout()
    {
        return $this->getSettingAsString(self::XML_KEY_CAN_LAYOUT);               
    }
    
    public function isGBREnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_GBR_CLEAN);  
    }
           
    public function getGBRLayout()
    {
        return $this->getSettingAsString(self::XML_KEY_GBR_LAYOUT);               
    }
    
    public function isDEUEnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_DEU_CLEAN);  
    }
       
    public function getDEULayout()
    {
        return $this->getSettingAsString(self::XML_KEY_DEU_LAYOUT);               
    }
    
    public function isIRLEnabled()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_IRL_CLEAN);  
    }
        
    public function getIRLLayout()
    {
        return $this->getSettingAsString(self::XML_KEY_IRL_LAYOUT);               
    }
    
    public function isManualEntryEnabled() 
    {
        return $this->getSettingAsBoolean(self::XML_KEY_MANUAL_ADDRESS_ENTRY);  
    } 
        
    public function isUsingProxy()
    {
        return $this->getSettingAsBoolean(self::XML_KEY_USE_PROXY);  
    }
    
    public function getProxyName()
    {
        return $this->getSettingAsString(self::XML_KEY_PROXYNAME);        
    }
    
    public function getProxyPort()
    {
        return $this->getSettingAsString(self::XML_KEY_PROXYPORT);
    }
    
    public function getProxyUser()
    {
        return $this->getSettingAsString(self::XML_KEY_PROXYUSER);
    }
    
    public function getProxyPassword()
    {
        return $this->getSettingAsString(self::XML_KEY_PROXYPASSWORD);
    } 
     
    public function getLVRCheckLineNumber()
    {
        return $this->getSettingAsString(self::XML_KEY_LVR);
    }
     
    public function getNumberOfInteraotionRequiredDisplayLines()
    {
        return $this->getSettingAsString(self::XML_KEY_IR_DISP);
    }
            
    private function getSettingAsString($key) 
    {
        return (string)Mage::getStoreConfig(self::XML_PATH_TO_SETTINGS . $key);
    }
        
    private function getSettingAsBoolean($key)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_TO_SETTINGS . $key);
    } 
}