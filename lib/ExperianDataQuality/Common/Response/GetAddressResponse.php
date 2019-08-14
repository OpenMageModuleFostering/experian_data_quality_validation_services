<?php 
    include_once 'BaseSearchResponse.php';

    /**
     * Holds the address response data from ProOnDemand WS.
     */
    class GetAddressResponse extends BaseSearchResponse
    {
        /* @var $AddressLineDictionary Dictinary */
        public $AddressLineDictionary = array(); 
        
        public $MissingSubPremise;
    }