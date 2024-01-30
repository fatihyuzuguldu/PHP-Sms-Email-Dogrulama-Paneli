<?php
session_start();

// Tüm oturum değişkenlerini sıfırla veya sil
$_SESSION = array();
session_unset();
session_destroy();

// Tüm çerezleri sil
$cookies = $_COOKIE;
foreach ($cookies as $cookie_name => $cookie_value) {
    setcookie($cookie_name, '', time() - 3600, '/');
}
setcookie("oturum", "", time() - 3600, "/"); // Geçmiş bir zaman damgası
setcookie("cerez", "", time() - 3600, "/"); // Geçmiş bir zaman damgası
setcookie("verify", "", time() - 3600, "/"); // Geçmiş bir zaman damgası
header("location: login.php");
?>