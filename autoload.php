<?php

spl_autoload_register('SocialAuthAutoload');

function SocialAuthAutoload($class)
{
    $filename = __DIR__.'/'.str_replace('SocialAuther/', '', str_replace('\\', '/', $class) . '.php');
    if (file_exists($filename)) {
        require_once $filename;
    }
}