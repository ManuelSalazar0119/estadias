<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$accion = isset($_GET['accion']) && $_GET['accion'] === 'solicitar' ? 'solicitar' : 'recuperar';
// Cargar autoload de Composer (intento en `funciones/vendor` y ruta alternativa)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    $alt = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($alt)) {
        require_once $alt;
    }
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    if ($email) {
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $msg = "PHPMailer no está instalado. Ejecuta en la carpeta 'funciones': composer require phpmailer/phpmailer";
        } else {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jholausfp@gmail.com';
                $mail->Password = 'xdau ctkd cior tvzw';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                // ...configuración SMTP...
                $mail->setFrom('jholausfp@gmail.com', 'Sistema');
                $mail->addAddress('jholausfp@gmail.com'); // Tu correo admin

                if ($accion === 'solicitar') {
                    $mail->Subject = 'Solicitud de nueva cuenta';
                    $mail->Body    = "El usuario con email $email ha solicitado una nueva cuenta.";
                } else {
                    $mail->Subject = 'Solicitud de restablecimiento de contraseña';
                    $mail->Body    = "El usuario con email $email ha solicitado restablecer su contraseña.";
                }

                $mail->send();
                $msg = $accion === 'solicitar'
                    ? "Tu solicitud de cuenta ha sido enviada al administrador. Pronto te contactarán."
                    : "Tu solicitud ha sido enviada al administrador. Pronto te contactarán.";
            } catch (Exception $e) {
                $msg = "No se pudo enviar el correo. Error: " . ($mail->ErrorInfo ?? $e->getMessage());
            }
        }
    } else {
        $msg = "Por favor, ingresa un email válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
    <style>
body {
    background: linear-gradient(135deg, #e8f5e9 0%, #fffbe7 100%);
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #4e342e;
    margin: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

h2 {
    color: #388e3c;
    margin-bottom: 10px;
    letter-spacing: 1px;
    font-weight: 700;
}

form {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(60,40,20,0.10), 0 1.5px 0 #a1887f;
    padding: 32px 28px 24px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 320px;
    max-width: 90vw;
    margin-bottom: 18px;
    position: relative;
}

label {
    font-weight: 600;
    color: #6d4c41;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

input[type="email"] {
    padding: 10px 12px;
    border: 1.5px solid #a1887f;
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 18px;
    width: 220px;
    background: #f9fbe7;
    transition: border 0.2s;
}

input[type="email"]:focus {
    border: 2px solid #388e3c;
    outline: none;
    background: #fffde7;
}

button[type="submit"] {
    background: linear-gradient(90deg, #8d6e63 0%, #388e3c 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 28px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(60,40,20,0.08);
    transition: background 0.2s, transform 0.1s;
    margin-top: 6px;
}

button[type="submit"]:hover {
    background: linear-gradient(90deg, #388e3c 0%, #8d6e63 100%);
    transform: translateY(-2px) scale(1.03);
}

a {
    color: #388e3c;
    text-decoration: none;
    font-weight: 500;
    margin-top: 10px;
    display: inline-block;
    transition: color 0.2s;
}

a:hover {
    color: #6d4c41;
    text-decoration: underline;
}

p {
    margin: 10px 0 0 0;
    font-size: 1rem;
    font-weight: 500;
}

@media (max-width: 500px) {
    form {
        min-width: 90vw;
        padding: 18px 8vw 18px 8vw;
    }
    input[type="email"] {
        width: 100%;
    }
}

/* Detalle decorativo: manchitas de vaca */
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    pointer-events: none;
    z-index: 0;
    background: url('../imagenes/vaca.jpg') repeat;
    opacity: 0.07;
}
</style>
</head>
<body>
<h2>
  <?php echo $accion === 'solicitar' ? 'Solicitar cuenta' : 'Recuperar contraseña'; ?>
</h2>
<?php if (!empty($msg)): ?>
  <p style="color:<?php echo (strpos($msg, 'No se pudo') === false) ? 'blue' : 'red'; ?>;text-align:center;">
    <?php echo htmlspecialchars($msg); ?>
  </p>
<?php endif; ?>
<form method="POST">
    <label for="email">
      <?php echo $accion === 'solicitar' ? 'Tu email para solicitar cuenta:' : 'Tu email:'; ?>
    </label>
    <input type="email" name="email" id="email" required>
    <button type="submit">
      <?php echo $accion === 'solicitar' ? 'Solicitar cuenta' : 'Solicitar'; ?>
    </button>
</form>
    <a href="/login.php">Volver al login</a>
</body>
</html>