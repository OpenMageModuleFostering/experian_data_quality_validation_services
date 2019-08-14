<?php
class EDQ_AddressValidation_Model_Resource_Customer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('addressvalidation/customer');
    }    
}