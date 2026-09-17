<?php 
require_once dirname(__DIR__, 2) . '/env.php';

define("SERVER_LDAP", env('LDAP_SERVER', "10.10.10.71"));
define("BASE_PATH", "file_upload");
?>