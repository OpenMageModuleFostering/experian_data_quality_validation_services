<?php     
    /**
     * Holds the base search response data from ProOnDemand WS.
     */
    class BaseSearchResponse
    {
        /* @var $PickListEntries  IEnumerable<PickListEntry> */
        public $PickListEntries = array();

        /* @var $Prompt String */
        public $Prompt;

        /* @var $Total String */
        public $Total;
        
        /* @var $ErrorMessage String */
        public $ErrorMessage;
                 
        /* @var $MatchType String */
        public $MatchType;
        
        /* @var $MatchType String */
        public $DPVStatus;
    }