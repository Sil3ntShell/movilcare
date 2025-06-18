<?php
// Archivo temporal para forzar logout completo
session_start();

// Destruir toda la sesión
$_SESSION = [];

// Eliminar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sesión
session_destroy();

// También eliminar cualquier cookie personalizada del sistema
setcookie('PHPSESSID', '', time() - 3600, '/');

echo "Sesión eliminada completamente. <br><br>";
echo "Cookies eliminadas. <br><br>";
echo "<a href='/empresa_celulares/login' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>IR AL LOGIN</a>";
?>