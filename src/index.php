<?
include 'json-response.php';

// get the JSON request and serialize it
$data = json_decode(file_get_contents('php://input'), true);

// create response object
$response = [ 'context' => 'custom-runtime-example', 'age' => $data ];

// return response
send_json_response($response);
?>