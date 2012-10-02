<?php

// Image URI Generator

// Copyright 2012, Senthil Padmanabhan
// Released under the MIT License
// http://www.opensource.org/licenses/MIT

$query = array(); // query list
$response = array(); // response object

// Get query params
$params = $_GET['params'];
if(!$params) {
	$params	= $_POST['params'];
}

// Validate input
if($params) {
	$query = json_decode(stripslashes($params), true);
	$query = $query? $query['images']: 0;
}

// Validate input data
if($query && count($query)) {
	$data = array();
	foreach ($query as $url) {
		$url = "http://".preg_replace('/http:\/\//i', '',$url);
		$respObj = array('url' => $url);
		
		// Defining the default CURL options
		$defaults = array( 
		        CURLOPT_URL => $url, 
		        CURLOPT_RETURNTRANSFER => TRUE	  
		    ); 
		
		// TODO make these curl requests in parallel using pluton or other libraries		
		// Open the Curl session
		$session = curl_init();		    
		
		// Setting the options
		curl_setopt_array($session, $defaults);		    
		
		// Make the call
		$imgResp = curl_exec($session);

		// Handle response
		if($imgResp) {
			$respObj['uri'] = base64_encode($imgResp);
		} else {
			$respObj['error'] = getErrorResp(101, curl_error($session));	
		}
		$data[] = $respObj; 
		
		// Close the connetion
		curl_close($session);
	}
	$response['data'] = $data;	
} else {
	// No input - build error response and return
	$response['error'] = getErrorResp(100, 'No input URLs');
}

// Set HTTP header to JSON
// TODO also add cahce headers if necessary
header("Content-Type: application/json");  

// echo out the JSON
echo json_encode($response);

/**
 * Utility Functions 
 */
/**
 * 
 * Function to build the error response  
 * 
 * @function getErrorResp
 * @param $id {String} Error ID
 * @param $msg {String} Error Message
 * 
 */			
function getErrorResp($id, $msg) {
	return array('id' => $id, 'msg' => $msg);
}

?>
