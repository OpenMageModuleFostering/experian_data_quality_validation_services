<?php
class EDQ_AddressValidation_Model_Customer extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix     = 'addressvalidation_customer';
    protected $_evenetObject    = 'customer';
    
    public function _construct()
    {
        $this->_init('addressvalidation/customer');
    }
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if($this->isObjectNew())
        {
            $this->setData('timestamp', Varien_Date::now());
        }
        return $this;
    }
}