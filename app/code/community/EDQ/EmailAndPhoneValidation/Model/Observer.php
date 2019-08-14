<?php 
class EDQ_EmailAndPhoneValidation_Model_Observer
{
    public function addExperianModuleTab(Varien_Event_Observer $observer)
    {                
        //get init sections and tabs
        $config = $observer->getConfig();
        
        //get tab 'experiandataquality'
        $experianDataQualityTab = $config->getNode('tabs/experiandataquality');
        
        if(!$experianDataQualityTab) 
        {
            $new_tab_xml = new Mage_Core_Model_Config_Element(' 
                <experiandataquality>
                    <label>Experian Data Quality</label>
                    <sort_order>10</sort_order>
                </experiandataquality>
            ');

            $tabs = $config->getNode('tabs');
            $tabs->appendChild($new_tab_xml);
        }
        
        return $this;
    }
} 
