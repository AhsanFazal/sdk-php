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

class ScholicaSessionTest extends PHPUnit_Framework_TestCase {

	private static function getInstance(){
		static $scholica;
		if(!isset($scholica)){
			$scholica = new \Scholica\ScholicaSession(ScholicaTestCredentials::$consumer_key, ScholicaTestCredentials::$consumer_secret);
		}
		return $scholica;
	}

	function testSetAccessToken(){
		$scholica = $this->getInstance();
		$request_token = $scholica->setAccessToken(ScholicaTestCredentials::$access_token);

		$this->assertInternalType('string', $request_token);
	}

	function testGetProfile(){
		$scholica = $this->getInstance();
		$r = $scholica->me;

		$this->assertInternalType('object', $r);
		$this->assertInternalType('int', $r->id);
		$this->assertInternalType('string', $r->name);

		$this->assertEquals($scholica->me, $scholica->user);
	}

	function testGetCommunities(){
		$scholica = $this->getInstance();
		$r = $scholica->request('communities');
		
		$this->assertInternalType('array', $r);
	}

	function testGetCommunity(){
		$scholica = $this->getInstance();
		$r1 = $scholica->request('communities/:community');
		$r2 = $scholica->request('communities/:c');

		$this->assertEquals($r1, $r2);

		$this->assertInternalType('object', $r1);
		$this->assertInternalType('int', $r1->id);

		$this->assertEquals($r1->id, $scholica->me->community);
	}

	function testGetUser(){
		$scholica = $this->getInstance();
		$r1 = $scholica->request('users/:user');
		$r2 = $scholica->request('users/:u');

		$this->assertEquals($r1, $r2);

		$this->assertInternalType('object', $r1);
		$this->assertInternalType('int', $r1->id);

		$this->assertEquals($r1->id, $scholica->me->id);
	}

}