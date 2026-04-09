<?php

session_start();

// Hapus session
session_destroy();

// Hapus cookie
// setcookie(session_name(), "", time() - 3600);

// Arahkan ke halaman login
header("Location: login.php");
exit;
