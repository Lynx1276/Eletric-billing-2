<?php
session_start();

function user() {
    return isset($_SESSION['user_id']);
}

function admin() {
    return isset($_SESSION['admin_id']);
}

function require_login() {
    // Check if the user is not logged in
    if (!user()) {
        header("Location: ./index.php");
        exit;
    }
    // Check if the user is not an admin
    if (!admin()) {
        header("Location: ./index.php");
        exit;
    }
}
?>
