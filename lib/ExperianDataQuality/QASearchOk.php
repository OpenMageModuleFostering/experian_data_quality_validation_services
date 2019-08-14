<?php
    class QASearchOk
    {
        public $IsOk;
        public $ErrorCode;
        public $ErrorMessage;
        public $ErrorDetail;

        public function __construct($response)
        {
            $this->IsOk         = isset($response->IsOk)         ? $response->IsOk         : \FALSE;
            $this->ErrorCode    = isset($response->ErrorCode)    ? $response->ErrorCode    : \FALSE;
            $this->ErrorMessage = isset($response->ErrorMessage) ? $response->ErrorMessage : \FALSE;
            $this->ErrorDetail  = isset($response->ErrorDetail)  ? $response->ErrorDetail  : \FALSE;
        }       
    }