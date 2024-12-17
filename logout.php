<?php
require_once 'config/init.php';

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit;
?> 