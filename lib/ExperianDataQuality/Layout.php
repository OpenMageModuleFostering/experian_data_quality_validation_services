<?php    
    class Layout
    {
        public $Name;
        public $Comment;
        
        public function __construct($name, $comment)
        {
            $this->Name = $name;
            $this->Comment = $comment;
        }
    }