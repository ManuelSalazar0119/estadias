<?php
session_start();

// 1. Limpiar el array de sesión por completo
$_SESSION = [];

// 2. Destruir la cookie de sesión en el navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir los datos de sesión en el servidor
session_destroy();

// 4. Redirección relativa (sin el '/' inicial)
// Si login.php está un nivel arriba de este archivo, usa ../
header("Location: ../login.php");
exit;
?>