<?php
include_once 'Layout.php';

    class QALayouts
    {
        public $Layout = array();
        
        public function __construct($response)
        {
            if(isset($response->Layout) === \FALSE)
            {
                $this->Layout = array();
            }
            else 
            {
                if(is_array($response->Layout))
                {
                    foreach ($response->Layout as $layout) 
                    {
                        \array_push($this->Layout, new Layout($layout->Name, $layout->Comment));
                    }
                }
                else 
                {
                    \array_push($this->Layout, new Layout($layout->Name, $layout->Comment));
                }
            }     
        }
    }