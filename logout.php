<?php
require_once 'includes/db.php';
session_destroy();
header('Location: ' . BASE_URL . 'index.php');
exit;
