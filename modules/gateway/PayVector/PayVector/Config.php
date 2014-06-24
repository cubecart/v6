<?php

$DeveloperEmailAddress = "";



/////////////////////////////////////////////////////////////////////////////////////////
//
//
//                      NOTHING SHOULD BE EDITED BELOW THIS NOTICE
//
//
/////////////////////////////////////////////////////////////////////////////////////////


$PaymentProcessorDomain = "payvector.net";
$PaymentProcessorPort   = 443;

if ($PaymentProcessorPort == 443) {
    $PaymentProcessorFullDomain = $PaymentProcessorDomain . "/";
} else {
    $PaymentProcessorFullDomain = $PaymentProcessorDomain . ":" . $PaymentProcessorPort . "/";
}
?>