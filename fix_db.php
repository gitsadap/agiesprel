<?php
require_once 'connect.php';

$conn->exec("UPDATE lab SET dep = '4' WHERE id = 3");
$conn->exec("UPDATE lab SET dep = '2' WHERE id = 4");
$conn->exec("UPDATE lab SET dep = '2' WHERE id = 5");
$conn->exec("UPDATE lab SET dep = '3' WHERE id = 6");

echo "UPDATE SUCCESS";
