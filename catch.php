<?php
session_start();
$_SESSION['user_id'] = 177;
$_SESSION['user_type'] = 'researcher';

// Capture warnings/errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
$_POST['labselect'] = '3';
$_POST['purpose'] = 'R';
$_POST['head'] = '1';

try {
    require 'request.php';
} catch (Throwable $e) {
    echo "CAUGHT CAUGHT CAUGHT\n";
    echo $e->getMessage();
}
$output = ob_get_clean();
file_put_contents('catch_log.txt', $output);
