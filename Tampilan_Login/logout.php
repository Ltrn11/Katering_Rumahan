<?php
session_start();      // aktifkan session dulu
session_destroy();    // hapus semua session (termasuk data login)
header("Location: index.php"); // kembali ke halaman login
exit;
?>
