<?php

function debug($label, $data=null){

    $logFile = __DIR__ . "/../logs/debug.log";

    $time = date("Y-m-d H:i:s");
    $output = "\n==============================\n";
    $output .= "[$time] $label\n";
    if ($data !== null) {

        if (is_array($data) || is_object($data)) {

            $output .= print_r($data, true);

        } else {

            $output .= $data;
        }
    }
    $output.="\n==============================\n";
    file_put_contents($logFile, $output, FILE_APPEND); 
}