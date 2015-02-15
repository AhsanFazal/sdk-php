<?php
/**
 * Copyright 2014 Scholica VOF
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
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
 */

namespace Scholica;

/**
 * Class ScholicaSession
 * @package Scholica
 * @author Thomas Schoffelen <tom@scholica.com>
 */
class ScholicaSession {

    /**
     * @var string API version
     */
    public $version = '2.0';

    /**
     * @var string Consumer key
     */
    private $consumer_key;

    /**
     * @var string Consumer secret
     */
    private $consumer_secret;

    /**
     * @var string Access token
     */
    private $access_token;

    /**
     * @var string Request token
     */
    private $request_token;

    /**
     * @var string Main API endpoint
     */
    private $endpoint = 'https://api.scholica.com/%v';

    /**
     * @var string Login API endpoint
     */
    private $auth_api = 'https://secure.scholica.com/';

    /**
     * @var resource CURL connection resource
     */
    private $http;

    /**
     * @var object representing current user
     */
    private $me;

    /**
     * Initialize Scholica instance
     *
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($consumer_key = null, $consumer_secret = null){
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * Set consumer key
     *
     * @param string $consumer_key
     */
    public function setConsumerKey($consumer_key){
        $this->consumer_key = $consumer_key;
    }

    /**
     * Set consumer secret
     *
     * @param string $consumer_secret
     */
    public function setConsumerSecret($consumer_secret){
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * HTTP POST request helper
     *
     * @param string $url
     * @param string $fields
     * @return resource $curl
     *
     * @throws ScholicaException
     */
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

    /**
     * Set access token and get request token
     *
     * @param string $access_token Access token
     * @return string $request_token
     *
     * @throws ScholicaException
     */
    public function setAccessToken($access_token){
        $this->access_token = $access_token;
        return $this->getRequestToken();
    }

    /**
     * Calculate hash for checking API response
     *
     * @return string
     */
    private function calculateConsumerHash(){
        return substr(sha1($this->consumer_secret),-10);
    }

    /**
     * Get request token
     *
     * @return string $request_token
     *
     * @throws ScholicaException
     */
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

    /**
     * Redirect to authorization endpoint
     *
     * @param string $redirect_uri Redirect URL
     * @param string $mode Login screen design mode
     */
    public function authorize($redirect_uri = null, $mode = 'WEB'){
        if(empty($redirect_uri)){
            $redirect_uri = $this->getCurrentURL();
        }

        header('Location: ' . $this->auth_api . 'auth?consumer_key=' . $this->consumer_key . '&redirect_uri=' . $redirect_uri . '&mode=' . $mode);
        exit;
    }

    /**
     * Request an API method
     *
     * @param string $method
     * @param array $fields
     * @return mixed $result
     *
     * @throws ScholicaException
     */
    public function request($method, $fields=array()){
        $method = ltrim($method, '/');

        $method = preg_replace('#/:u(ser)?#i', '/'.$this->me->id, $method);
        $method = preg_replace('#/:c(ommunity)?#i', '/'.$this->me->community, $method);

        if(!isset($fields['token'])){ $fields['token'] = $this->request_token; }

        $result = $this->HTTPPostRequest(str_replace('%v', $this->version, $this->endpoint) . '/' . $method, $fields);
        
        if(empty($result)){ throw new ScholicaException('Empty response from token service'); }
        
        if(!$json = @json_decode($result)){ throw new ScholicaException('Invalid response from token service'); }
        
        if(isset($json->error)){ throw new ScholicaException($json->error->description, $json->error->code); }
        
        if(isset($json->result)){
            $json = $json->result;
        }

        if($method == 'me'){
            $this->me = $json;
        }
        
        return $json;
    }

    /**
     * Get current user profile ('/me' request)
     */
    public function getMe(){
        if(isset($this->me)){
            return $this->me;
        }else{
            return $this->request('me');
        }
    }

    /**
     * Magic method to allow `$scholica->user->name` usage
     */
    public function __get($name){
        if($name == 'me' || $name == 'user'){
            return $this->getMe();
        }
    }
}