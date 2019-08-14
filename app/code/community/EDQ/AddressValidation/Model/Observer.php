<?php 
class EDQ_AddressValidation_Model_Observer
{
    public function adminhtmlBlockSystemConfigInitTabSectionsBefore(Varien_Event_Observer $observer)
    {     
        //get init sections and tabs
        $config = $observer->getConfig();
        
        //get tab 'experiandataquality'
        $experianDataQualityTab = $config->getNode('tabs/experiandataquality');
        
        if(!$experianDataQualityTab) 
        {
            $new_tab_xml = new Mage_Core_Model_Config_Element(' 
                <experiandataquality translate="label">
                    <label>Experian Data Quality</label>
                    <sort_order>9999</sort_order>
                </experiandataquality>
            ');

            $tabs = $config->getNode('tabs');
            $tabs->appendChild($new_tab_xml);
        }
        
        return $this;
    }
    
    public function customerAddressSaveAfter(Varien_Event_Observer $observer)
    {       
        if(!session_id())
        {
            return;
        }
        
        $isModuleEnable = Mage::helper('core')->isModuleOutputEnabled('EDQ_AddressValidation');
        if(!$isModuleEnable) { return; }
                        
        $countryId = $observer->getData('customer_address')->getData('country_id');
        
        if( ($countryId === 'US' && $this->_getSettings()->areUSDataSetsEnabled()) 
         || ($countryId === 'CA' && $this->_getSettings()->isCANEnabled()))
        {                
            $customerId = $observer->getData('customer_address')->getData('customer_id');
            
            $getEdqInfoMatrixFor = 'getEdqInfoMatrixFor' . $customerId;            
            $edqInfoArray = Mage::getSingleton('adminhtml/session')->$getEdqInfoMatrixFor();
                        
            $edqInfo = null;            
            $entityId = $observer->getData('customer_address')->getData('entity_id');
            $postIndex = filter_var($observer->getData('customer_address')->getData('post_index'), FILTER_SANITIZE_NUMBER_INT);
            if(is_array($edqInfoArray) && array_key_exists($postIndex, $edqInfoArray))
            {
                $edqInfo = $edqInfoArray[$postIndex];
            }
            $this->_getService()->insertIntoDataBase($entityId, $edqInfo);
            
            if($edqInfo !== null)
            {       
                $unsEdqInfoMatrixFor = 'unsEdqInfoMatrixFor' . $customerId;
                Mage::getSingleton('adminhtml/session')->$unsEdqInfoMatrixFor();
            }
        }
    } 
    
    private function _getService()
    {
        return Mage::getModel('addressvalidation/Service');
    }

    private function _getSettings() 
    {
        return Mage::helper('addressvalidation/Settings');
    }
}