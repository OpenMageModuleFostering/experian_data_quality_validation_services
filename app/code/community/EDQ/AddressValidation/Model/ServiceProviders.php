<?php
class EDQ_AddressValidation_Model_ServiceProviders
{        
    /**
    * Options getter
    *
    * @return array
    */
   public function toOptionArray()
   {
       return array(
           array('value' => 'proondemand', 'label'=>Mage::helper('adminhtml')->__('Pro On Demand'))
       );
   }

   /**
    * Get options in "key-value" format
    *
    * @return array
    */
   public function toArray()
   {
       return array(
           'proondemand' => Mage::helper('adminhtml')->__('Pro On Demand')
       );
   }
}