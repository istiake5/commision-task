<?php
include_once 'vendor/autoload.php';

if (!isset($argv[1]) || !is_file($file = $argv[1])) {
    printf("php %s <file>\n", __FILE__);
    exit(1);
}

//run application
(new app($file))->outputOnConsole();

