<?php
    class EDQ_EmailAndPhoneValidation_Model_System_Config_Source_SupportedCountry
    {
        /**
         * Options getter
         *
         * @return array
         */
        public function toOptionArray()
        {   
            return array(
                array('value'=>''   , 'label'=> Mage::helper('adminhtml')->__('NONE')),
                array('value'=>'+1' , 'label'=> Mage::helper('adminhtml')->__('USA')),
                array('value'=>'+44', 'label'=> Mage::helper('adminhtml')->__('GBR')),
                array('value'=>'+61', 'label'=> Mage::helper('adminhtml')->__('AUS')),
                array('value'=>'+33', 'label'=> Mage::helper('adminhtml')->__('FRA'))
            );
        }
    }
  