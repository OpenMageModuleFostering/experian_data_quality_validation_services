<?php

class DoCanSearchParameters
{
    public $Country;
    public $Layout;
     
    public function __construct($country, $layout)
    {
        $this->Country = $country;
        $this->Layout = $layout;
    }
}