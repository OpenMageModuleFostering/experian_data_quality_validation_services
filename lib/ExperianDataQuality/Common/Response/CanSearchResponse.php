<?php
    include_once 'BaseSearchResponse.php';

    /**
     *  Holds the can search response data from ProOnDemand WS.
     */
    class CanSearchResponse extends BaseSearchResponse 
    {
        /* @var $CanSearch Boolean */
        public $CanSearch;   
        
        /* @var $ErrorCode String */
        public $ErrorCode;
        
        /* @var $ErrorDetail String */
        public $ErrorDetail; 
    }