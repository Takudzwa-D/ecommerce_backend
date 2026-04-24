<?php

function isEmptyField($value){
    return !isset($value) || trim($value) === '';

}

function isValidEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}



function isValidPhoneNumber($phone){
    return filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
}
function isValidPassword($password){
    return strlen($password) >= 6;
}