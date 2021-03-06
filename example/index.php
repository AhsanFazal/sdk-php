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
 * This app shows how to interact with the Scholica
 * API from PHP and Javascript.
 * (PHP 5+)
 */

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Define consumer key and secret from testing credentials
if (!file_exists(__DIR__ . '/ScholicaTestCredentials.php')) {
    throw new ScholicaException(
        'You must create a ScholicaTestCredentials.php file from ScholicaTestCredentials.php.dist'
    );
}
define('CONSUMER_KEY', ScholicaTestCredentials::$consumer_key);
define('CONSUMER_SECRET', ScholicaTestCredentials::$consumer_secret);

// Define redirect URL
define('REDIRECT_URI', 'http://www.scholica.com/secure/test/app/');

// Access tokens should be stored in a database, 
// but for this example we use sessions as it is easier to set up.
session_start();

// Show errors (easier for debugging)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Allow the user to start over
if(isset($_GET['reset']) && isset($_SESSION['access_token'])){ unset($_SESSION['access_token']); }

// Create class instance
$scholica = new Scholica\ScholicaSession(CONSUMER_KEY, CONSUMER_SECRET);

$authenticated = false;

// Check for the access token
if(isset($_GET['access_token'])){
	try{
		$scholica->setAccessToken($_GET['access_token']);
		$authenticated = true;
		$_SESSION['access_token'] = $_GET['access_token'];
	}catch(Scholica\ScholicaException $e){
		echo '<span style="color: red">Oops. Something went wrong setting the access_token from $_GET: <b>'.$e->getMessage().'</b></span><br /><br />';
		echo 'You might want to try again:<br /><br />';
	}
}elseif(isset($_SESSION['access_token'])){
	try{
		$scholica->setAccessToken($_SESSION['access_token']);
		$authenticated = true;
	}catch(Scholica\ScholicaException $e){
		echo '<span style="color: red">Oops. Something went wrong setting the access_token from $_SESSION: '.$e->getMessage().'</span><br /><br />';
	}
}

// Firstly check for errors
if(isset($_GET['access_error'])){
	// Show a friendly error 
	echo '<span style="color: red">Apparently someting went wrong: '.$_GET['access_error'].'</span><br /><br />';	
}

// No access token found, so let's show an login button
if(!$authenticated){
	// There are two ways to authenticate. You can show a login button with Javascript, 
	// or immeadiatly redirect the user to the authentication server. 
	
	// Way 1: direct redirect (uncomment the following line to enable)
	// $scholica->authorize(REDIRECT_URI);
	
	// Way 2: fancy javascript button
	echo '<h3>Scholica API Login demo</h3>Start by logging in:<br /><br />';
	echo '<a class="scholica-login-button" data-size="large" data-redirect-uri="'.REDIRECT_URI.'" data-consumer-key="'.CONSUMER_KEY.'">Log in with Scholica</a><script src="//api.scholica.com/platform.js?v2"></script>';
	
	exit;
}

// The rest of the page is only shown if authenticated.
echo '<b>Authenticated!</b><br /><br />';
echo 'This is you (method: /user/me):<br /><pre>'.print_r($scholica->request('/user/me'),true).'</pre><br /><br />';
echo 'Now that you are logged in, you can go to another page in the app that doensn\'t have the <code>?access_token</code> behind the url and still have your information. <a href="index.php">Try it</a>';
echo '<br /><br /><a href="index.php?reset">Destroy session and start over</a>';