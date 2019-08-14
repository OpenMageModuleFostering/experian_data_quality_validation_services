<?php
    abstract class WshValidator{
        private $_validation_key;
        private $_service_url;
        private $_sleep_seconds_between_requests;
        private $_retries;
        private $_has_error;
        private $_error_message;

        const AREA_CODE = 'area_code';
        const PHONE = 'phone';
        const EMAIL = 'email';
    
        function __construct($url, $_validation_key, $_sleep_seconds_between_requests, $_retries){
            $this->_service_url = $url;
            $this->_validation_key = $_validation_key;
            $this->_sleep_seconds_between_requests = $_sleep_seconds_between_requests;
            $this->_retries = $_retries;
            $this->_has_error = false;
            $this->_error_message = '';
        } 

        public function has_error() {
            return $this->_has_error;
        }

        public function get_error_message() {
            return $this->_error_message;
        }
    
        public function validate($validation_request){
            if (!is_array($validation_request)){
                throw new Exception('The validation request must be a key value object.');
            }

            $data = $this->_get_post_data($validation_request); 
            return $this->_execute_validation($data);
        }
    
        abstract protected function _get_post_data($validation_request);
    
        private function _execute_validation($data) {  
            $post_request = $this->_create_request($this->_service_url, 'post', $data);             
            $post_response = curl_exec($post_request); 
            $get_http_status = curl_getinfo($post_request, CURLINFO_HTTP_CODE); 

            curl_close($post_request);  

            if(!$this->_handle_http_status($get_http_status)) {
                return;            
            }
            
            list($post_response_headers, $post_content) = explode("\r\n\r\n", $post_response);
    
            $get_request_url = '';
            foreach (explode("\r\n", $post_response_headers) as $hdr) {
                if (strpos($hdr, 'Content-Location: ') !== FALSE){
                    $get_request_url = substr($hdr, strlen('Content-Location: '));
                    break;
                }
            }
    
            $get_request = $this->_create_request($get_request_url, 'get');
    
            $get_response = '';
            $http_status = '';
            for ($i = 0; $i < $this->_retries; $i++){
                sleep($this->_sleep_seconds_between_requests);
                $get_response = curl_exec($get_request);
                $http_status = curl_getinfo($get_request, CURLINFO_HTTP_CODE);
                if ($http_status !== 409){
                    break;
                }
            }

            if(!$this->_handle_http_status($http_status)) {
                curl_close($get_request);
                return;    
            }
            list($get_response_headers, $get_content) = explode("\r\n\r\n", $get_response);
    
            curl_close($get_request); 
    
            return $get_content;
        }
    
        private function _create_request($url, $method, $data = ''){
            $request = curl_init($url);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($method)); 
            if (strcasecmp($method, 'post') === 0){
                if ((!isset($data) || trim($data)==='')) {
                    throw new Exception('Expected post data.');
                }
    
                curl_setopt($request, CURLOPT_POSTFIELDS, $data);    
            }
    
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($request, CURLOPT_VERBOSE, 1);
            curl_setopt($request, CURLOPT_HEADER, 1);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'Content-type: application/json',
                'Auth-Token: '.$this->_validation_key
            ));
    
            return $request;
        }

        private function _set_error_message($error_message) {            
            $this->_has_error = true;
            $this->_error_message = $error_message;
        }

        private function _handle_http_status($http_status){
            switch ($http_status){
                case 200:
                case 204:
                    return true;
                case 409:
                    $this->_set_error_message('Service timeout.'); 
                    return false;
                case 401:
                    $this->_set_error_message("Invalid validation key {$this->_validation_key}"); 
                    return false; 
                case 404:
                    $this->_set_error_message('The requested resource could not be found but may be available again in the future. Subsequent requests by the client are permissible.'); 
                    return false; 
                case 500:
                    $this->_set_error_message('Service exception.'); 
                    return false;  
                default: 
                    throw new Exception("Unexpected HTTP status {$http_status}");
            }
        }
    }
