<?php

// First, include Requests
include '../library/Requests.php';

// Next, make sure Requests can load internal classes
Requests::register_autoloader();

// Setup what we want to request
$requests = [
	[
		'url' => 'http://httpbin.org/get',
		'headers' => ['Accept' => 'application/javascript'],
	],
	'post' => [
		'url' => 'http://httpbin.org/post',
		'data' => ['mydata' => 'something'],
	],
	'delayed' => [
		'url' => 'http://httpbin.org/delay/10',
		'options' => [
			'timeout' => 20,
		],
	],
];

// Setup a callback
function my_callback(&$request, $id)
{
	var_dump($id, $request);
}

// Tell Requests to use the callback
$options = [
	'complete' => 'my_callback',
];

// Send the request!
$responses = Requests::request_multiple($requests, $options);

// Note: the response from the above call will be an associative array matching
// $requests with the response data, however we've already handled it in
// my_callback() anyway!
//
// If you don't believe me, uncomment this:
// var_dump($responses);