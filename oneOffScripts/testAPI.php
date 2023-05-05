<?php

include __DIR__ . '/../commonbase.php';

$headers = [
	'Cache-Control' => 'no-cache',
	'Content-Type' => 'application/json',
	'Accept' => 'application/json',
];

$apiCredentials = new \App\Settings\TestAPI();
$parameters = $apiCredentials->getFields();
$guzzle = new \GuzzleHttp\Client(['headers' => $headers, 'verify' => false, 'http_errors' => false]);
$response = $guzzle->post($apiCredentials->url . '/V1/Authorize/password', ['form_params' => $parameters, ]);
$lastError = $response->getReasonPhrase();
$statusCode = $response->getStatusCode();
$data = \json_decode($response->getBody(), true);
echo "\nAuthorization Status: {$statusCode}\nLastError: {$lastError}\n";

$headers = [
	'Cache-Control' => 'no-cache',
	'Content-Type' => 'application/json',
	'Accept' => 'application/json',
	'Authorization' => 'Bearer ' . $data['bearer_token'],
];

$guzzle = new \GuzzleHttp\Client(['headers' => $headers, 'verify' => false, 'http_errors' => false]);

$response = $guzzle->get($apiCredentials->url . '/V1/Schema/story');
$lastError = $response->getReasonPhrase();
$statusCode = $response->getStatusCode();
$data = \json_decode($response->getBody(), true);
\print_r($data);
echo "\nLastError: {$lastError}\nStatus: {$statusCode}\n";

$response = $guzzle->get($apiCredentials->url . '/V1/Table/story?limit=2&sortField=storyId&related=*');
$lastError = $response->getReasonPhrase();
$statusCode = $response->getStatusCode();
$data = \json_decode($response->getBody(), true);
\print_r($data);
echo "\nLastError: {$lastError}\nStatus: {$statusCode}\n";
