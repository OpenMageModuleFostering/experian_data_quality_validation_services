<?php
class EDQ_Addressvalidation_Model_Quote extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix     = 'addressvalidation_quote';
    protected $_evenetObject    = 'customer';
    
    public function _construct()
    {
        $this->_init('addressvalidation/quote');
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