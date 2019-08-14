<?php
class EDQ_AddressValidation_Model_DataCenters
{        
    /**
    * Options getter
    *
    * @return array
    */
   public function toOptionArray()
   {
       return array(
           array('value' => 'uk', 'label'=>Mage::helper('adminhtml')->__('United Kingdom')),
           array('value' => 'us', 'label'=>Mage::helper('adminhtml')->__('United States')),
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
           'uk' => Mage::helper('adminhtml')->__('United Kingdom'),
           'us' => Mage::helper('adminhtml')->__('United States'),
       );
   }
}