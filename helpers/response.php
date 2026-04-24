<?php


//This makes the response well structured
function jsonResponse($success,$message,$data =null){
    header("Content-Type: application/json");

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}