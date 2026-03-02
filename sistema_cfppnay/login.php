<?php
session_start();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="css/cssLogin.css">
</head>
<body>

<div style="position: fixed; top: 20px; left: 20px; z-index: 100;">
  <img src="imagenes/logoPng.png" alt="Logo" style="height: 150px;">
</div>

<div class="scroll-down" id="scrollDown">DESPLAZA HACIA ABAJO
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
    <path d="M16 3C8.832031 3 3 8.832031 3 16s5.832031 13 13 13 13-5.832031 13-13S23.167969 3 16 3zm0 2c6.085938 0 11 4.914063 11 11 0 6.085938-4.914062 11-11 11-6.085937 0-11-4.914062-11-11C5 9.914063 9.914063 5 16 5zm-1 4v10.28125l-4-4-1.40625 1.4375L16 23.125l6.40625-6.40625L21 15.28125l-4 4V9z"/> 
  </svg>
</div>

<div class="container"></div>

<div class="modal">
  <div class="modal-container">
    <div class="modal-left">
      
      <form method="POST" action="funciones/login_process.php">
        
        <h1 class="modal-title">Bienvenido!</h1>
        <i><p class="modal-desc">Sistema para el Registro de Actividades mensuales y semanales Federales y Estatales</p></i>
        <div class="input-block">
          <label for="email" class="input-label">Email</label>
          <input type="email" name="email" id="email" placeholder="Email">
        </div>
        <div class="input-block">
          <label for="password" class="input-label">Contraseña</label>
          <input type="password" name="password" id="password" placeholder="Password">
        </div>
        <?php
          if ($error) {
              echo '<div style="color:red; text-align:center; margin-bottom:10px;">'.htmlspecialchars($error).'</div>';
          }
        ?>
        <div class="modal-buttons">
          <a href="funciones/recuperarContra.php">Clic aquí si se te olvidó la contraseña</a>
          <button class="input-button">ENTRAR</button>
        </div>
      </form>
        <p class="sign-up">No tienes cuenta? <a href="funciones/recuperarContra.php?accion=solicitar">Solicitar cuenta</a></p>
      </div>
      <div class="modal-right">
        <img src="imagenes/fondoVacas.jpg" alt="FondoVacas">
      </div>
      <button class="icon-button close-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
          <path d="M 25 3 C 12.86158 3 3 12.86158 3 25 C 3 37.13842 12.86158 47 25 47 C 37.13842 47 47 37.13842 47 25 C 47 12.86158 37.13842 3 25 3 z M 25 5 C 36.05754 5 45 13.94246 45 25 C 45 36.05754 36.05754 45 25 45 C 13.94246 45 5 36.05754 5 25 C 5 13.94246 13.94246 5 25 5 z M 16.990234 15.990234 A 1.0001 1.0001 0 0 0 16.292969 17.707031 L 23.585938 25 L 16.292969 32.292969 A 1.0001 1.0001 0 1 0 17.707031 33.707031 L 25 26.414062 L 32.292969 33.707031 A 1.0001 1.0001 0 1 0 33.707031 32.292969 L 26.414062 25 L 33.707031 17.707031 A 1.0001 1.0001 0 0 0 32.980469 15.990234 A 1.0001 1.0001 0 0 0 32.292969 16.292969 L 25 23.585938 L 17.707031 16.292969 A 1.0001 1.0001 0 0 0 16.990234 15.990234 z"></path>
        </svg>
      </button>
  </div>
  <button class="modal-button">INICIA SESIÓN</button>
</div>

<script src="js/login.js"></script> 

<?php if ($error): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var scrollDown = document.getElementById('scrollDown');
    if (scrollDown) scrollDown.style.display = 'none';

    var modalButton = document.querySelector('.modal-button');
    if (modalButton && modalButton.offsetParent !== null) {
        modalButton.click();
    }
    document.body.style.overflow = "auto";
    var modalLeft = document.querySelector('.modal-left');
    if (modalLeft) modalLeft.scrollIntoView({behavior: "auto", block: "center"});
});
</script>
<?php endif; ?>
<?php unset($_SESSION['error']); ?>

</body>
</html>