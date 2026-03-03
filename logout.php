<?php
require_once 'koneksi.php';

// Destroy session
session_destroy();
redirect('index.php?success=Anda%20telah%20logout');
?>

