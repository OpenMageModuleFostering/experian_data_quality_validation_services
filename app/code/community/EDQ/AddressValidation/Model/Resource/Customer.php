<?php
class EDQ_AddressValidation_Model_Resource_Customer extends Mage_Core_Model_Resource_Db_Abstract
{    
    public function _construct()
    {
        $this->_init('addressvalidation/customer', 'customer_id');
    }
}