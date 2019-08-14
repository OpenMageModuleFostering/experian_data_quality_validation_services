<?php    
    class HelperMethods 
    {
        /**
         * Indicates whether a specified string is null, empty, or consists 
         * only of white-space characters.
         *
         * @param string $value The string to test
         * @return true if the value parameter is null, 
         * or if value consists exclusively of white-space characters.
         */
        public static function isNullOrWhiteSpace($value)
        {   
            $isValid = \FALSE;

            if ((!isset($value) || trim($value) === '')) 
            {
                $isValid = \TRUE;
            }

            return $isValid;
        }     

        public static function startsWith($needle, $haystack)
        {
            $beginingToCompare = substr($haystack, 0, strlen($needle));
            return (strcmp($beginingToCompare, $needle) == 0); 
        }
    }