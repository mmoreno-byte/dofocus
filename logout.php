<?php
session_start();        
session_destroy(); // "Destruir sesión", la elimina del servidor
header("Location: login.php"); // Volver al login
exit();
?>
