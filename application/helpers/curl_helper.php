<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

function curl_post($url, $data_array = array())
{
	$protocol = 'http';
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		$protocol = 'https';
	}

	$fields = $data_array;
	$fields_string = '';
	//url-ify the data for the POST
	foreach ($fields as $key => $value) {
		$fields_string .= $key . '=' . $value . '&';
	}
	rtrim($fields_string, '&');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16');
	curl_setopt($ch, CURLOPT_REFERER, $protocol . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '/');


	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}


function get_curl($url)
{
	$ch = curl_init();

	// set url 
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// $output contains the output string 
	$output = curl_exec($ch);

	// close curl resource to free up system resources 
	curl_close($ch);
	return $output;
}

function curl_with_header($url, $raw_data = '', $header = array())
{

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "$raw_data",
		CURLOPT_HTTPHEADER => $header,
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return 'error';
	} else {
		return $response;
	}
}

function get_curl_with_header($url, $header = array())
{
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => $header,
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return 'error';
	} else {
		return $response;
	}
}
