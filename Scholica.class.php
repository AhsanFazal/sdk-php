<?php
/**
 * Scholica API wrapper for PHP
 *
 * Dependencies:
 *   - PHP 5+
 *   - cURL
 *
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   E-learning
 * @package    Scholica_API
 * @author     Tom Schoffelen <tom@scholica.com>
 * @copyright  Scholica
 * @license    MIT License
 * @version    0.1
 */
 
class Scholica {
	
	// Version
	public $version = '0.1';
	
	// Tokens
	private $consumer_key;
	private $consumer_secret;
	private $access_token;
	private $request_token;
	
	// API endpoint
	private $endpoint = 'http://api.scholica.com/';
	
	// Secure API URL
	private $auth_api = 'https://secure.scholica.com/';
	
	// cURL resource
	private $http;
	
	// Constructor
	public function __construct($consumer_key, $consumer_secret){
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}
	
	// Private helper
	private function HTTPPostRequest($url, $fields=''){
		if(!is_callable('curl_init')){ throw new ScholicaException('CURL library not found.'); }
		if(is_array($fields)){ $fields = http_build_query($fields); }
		
		$this->http = curl_init();
		curl_setopt($this->http, CURLOPT_URL,$url);
		curl_setopt($this->http, CURLOPT_FAILONERROR, 0);
		curl_setopt($this->http, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->http, CURLOPT_POST, 1);
		curl_setopt($this->http, CURLOPT_POSTFIELDS, $fields);
		return curl_exec($this->http);
	}
	
	// Tokens
	public function setAccessToken($access_token){
		$this->access_token = $access_token;
		$this->getRequestToken();
	}
	private function calculateConsumerHash(){
		return @substr(sha1($this->consumer_secret),-10);
	}
	private function getRequestToken(){
		$result = $this->HTTPPostRequest($this->auth_api.'token', array('access_token'=>$this->access_token,'consumer_secret'=>$this->consumer_secret));
		if(empty($result)){ throw new ScholicaException('Empty response from token service'); }
		if(!$json = @json_decode($result)){ throw new ScholicaException('Invalid response from token service'); }
		if(isset($json->error)){ throw new ScholicaException($json->error);}
		if(!isset($json->status) || $json->status != 'ok'){ throw new ScholicaException('Incomplete response from token service'); }
		if(!isset($json->request_token)){ throw new ScholicaException('Incomplete response from token service'); }
		if(!isset($json->consumer_hash)){ throw new ScholicaException('Incomplete response from token service'); }
		if($json->consumer_hash != $this->calculateConsumerHash()){ throw new ScholicaException('Invalid hash returned by token service'); }
		$this->request_token = $json->request_token;
		return $json->request_token;
	}
	
	// Authorize method
	public function authorize($redirect_uri){
		header('Location: '.$this->auth_api.'auth?consumer_key='.$this->consumer_key.'&redirect_uri='.$redirect_uri); 
		exit;
	}
	
	// API methods
	public function request($method, $fields=array()){
		$method = ltrim($method, '/');
		
		if(!isset($fields['token'])){ $fields['token'] = $this->request_token; }
		
		$result = $this->HTTPPostRequest($this->endpoint . $method, $fields);
		if(empty($result)){ throw new ScholicaException('Empty response from token service'); }
		if(!$json = @json_decode($result)){ throw new ScholicaException('Invalid response from token service'); }
		if(isset($json->error)){ throw new ScholicaException($json->error->description, $json->error->code); }
		return $json;
	}
}

class ScholicaException extends Exception {}