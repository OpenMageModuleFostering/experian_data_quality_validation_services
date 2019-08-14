<?php
class EDQ_AddressValidation_Model_Resource_Quote extends Mage_Core_Model_Resource_Db_Abstract
{    
    public function _construct()
    {
        $this->_init('addressvalidation/quote', 'quote_id');
    }
}