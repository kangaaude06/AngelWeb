<?php
// logout.php
require_once 'config.php';
require_once 'Auth.class.php';

$auth = new Auth();
$auth->logout();
?>

