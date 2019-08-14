<?php
    class QAAuthentication
    {
        private $Username;
        private $Password;

        public function __construct($username,$password) 
        {
          $this->Username = $username;
          $this->Password = $password;
        }
    }