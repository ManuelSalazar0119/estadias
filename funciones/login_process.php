<?php
session_start();
if (isset($_SESSION['error'])) {
    echo '<div style="color:red; text-align:center; margin-bottom:10px;">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}

// Cambia estos datos a tu configuración real
$host = "127.0.0.1";
$db = "cefppenay";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $_SESSION['error'] = "Por favor, completa todos los campos.";
        header("Location: /login.php");
        exit;
    }

    // Consulta usuario por email
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, password_hash, rol_usuario, activo_usuario FROM usuarios WHERE email = :email AND activo_usuario = 1 LIMIT 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verifica contraseña usando password_verify (asumiendo hash Bcrypt)
        if (password_verify($password, $usuario['password_hash'])) {
            // Login exitoso: crea variables de sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['rol_usuario'] = $usuario['rol_usuario'];

            // Redirige a panel principal (cámbialo a tu dashboard)
            header("Location: /interfaces/panel_control.php");
            exit;
        } else {
            $_SESSION['error'] = "Contraseña incorrecta.";
        }
    } else {
        $_SESSION['error'] = "Usuario no encontrado o inactivo.";
    }
} else {
    $_SESSION['error'] = "Método no permitido.";
}

header("Location: /login.php");
exit;
?>
