<?php
require_once '../includes/security.php';
session_start();
session_destroy();
header('Location: /amazingworldmarketingcorp/index.php');
exit;
