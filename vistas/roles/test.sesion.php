<?php
session_start();
echo "<h2>Los secretos de tu Sesión Actual:</h2>";
echo "<pre style='background: #1e1e1e; color: #00ff00; padding: 20px; font-size: 18px;'>";
var_dump($_SESSION);
echo "</pre>";
?>