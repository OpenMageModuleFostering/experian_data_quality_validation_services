<?php
    class EngineType
    {
        public $EngineType = array();
        public $PromptSet;
        private $Flatten;
        private $FlattenSpecified;
        private $Intensity;
        private $IntensitySpecified;
        private $PromptSetSpecified;
        private $Threshold;
        private $Timeout;
        private $Value;

        public function __construct($flattenField, $promptSetFieldSpecified, $thresholdField, $timeoutField, $valueField, $flattenSpecified = \NULL, $intensity = \NULL, $intensitySpecified= \NULL) 
        {
            $this->Value = $valueField;     
            $this->Flatten = $flattenField;
            $this->PromptSetSpecified = $promptSetFieldSpecified;
            $this->Threshold = $thresholdField;
            $this->Timeout = $timeoutField; 

            if(isset($flattenSpecified)) 
            {
                $this->FlattenSpecified = $flattenSpecified;
            }

            if(isset($intensity))
            {
                $this->Intensity = $intensity;
            }

            if(isset($intensitySpecified))
            {
                $this->IntensitySpecified = $intensitySpecified;
            }
            
            $this->EngineType = array(
                '_' => $this->Value,
                'Flatten' => $this->Flatten,
                'PromptSet' => $this->PromptSet
            );
        }
    }