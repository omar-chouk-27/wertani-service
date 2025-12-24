<?php
/**
 * Index - Entry point
 * Redirects to dashboard if logged in, otherwise to login
 */

session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
