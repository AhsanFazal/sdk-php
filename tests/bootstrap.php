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

require_once __DIR__ . '/../vendor/autoload.php';

use Scholica\ScholicaException;

if (!file_exists(__DIR__ . '/ScholicaTestCredentials.php')) {
    throw new ScholicaException(
        'You must create a ScholicaTestCredentials.php file from ScholicaTestCredentials.php.dist'
    );
}elseif(getenv('ACCESSTOKEN')){
	require_once __DIR__ . '/ScholicaTestCredentials.php.dist';
	ScholicaTestCredentials::$consumer_key = getenv('CONSUMERKEY');
	ScholicaTestCredentials::$consumer_secret = getenv('CONSUMERSECRET');
	ScholicaTestCredentials::$access_token = getenv('ACCESSTOKEN');
}else{
	require_once __DIR__ . '/ScholicaTestCredentials.php';
}