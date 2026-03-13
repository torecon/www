<?php
session_start();
if (empty($_SESSION['torecon_analytics_auth'])) {
    header('Location: https://analytics.torecon.de/login.php');
    exit;
}
