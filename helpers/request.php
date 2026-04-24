<?php

function getJsonInput() {
    $data = json_decode(file_get_contents("php://input"), true);
    return $data ? $data : [];
}




