<?php
include_once 'QAAuthentication.php';

    class QAQueryHeader
    {
        private $QAAuthentication;
        private $Security;

        public function __construct($username,$password) 
        {
          $this->QAAuthentication = new QAAuthentication($username, $password);
          $this->Security = \NULL;
        }
    }