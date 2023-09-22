<?php
require_once(dirname(__FILE__) . '/../function.php');

try {
    session_start();

    $_SESSION = array();

    session_destroy();

    redirect('/admin/login.php');
} catch (Exception $e) {
    redirect('/error.php');
}
?>