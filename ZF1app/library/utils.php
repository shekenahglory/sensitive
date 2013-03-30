<?php

function showme($somedata) 
{
    $argList = func_get_args();
    
    echo "<pre>";
    foreach ($argList as $arg) print_r($arg);
    echo "</pre>";      
}
    
function showmedie($somedata) 
{
    $argList = func_get_args();
    
    echo "<pre>";
    foreach ($argList as $arg) print_r($arg);
    echo "</pre>";      
    die;
}