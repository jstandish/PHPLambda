<?
function send_json_response($data){
    header('Content-type: application/json');
    echo json_encode($data);
}
?>