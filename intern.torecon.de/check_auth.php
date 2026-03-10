<?php
session_start();
if (empty($_SESSION['torecon_auth'])) {
    header('Location: https://intern.torecon.de/login.php');
    exit;
}
