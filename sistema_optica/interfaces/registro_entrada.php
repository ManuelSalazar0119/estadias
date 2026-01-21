<?php include('header.php'); 
date_default_timezone_set('America/Mexico_City');
?>
<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Entradas</title>
<style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 20px;
            width: 90%;
            max-width: 400px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Entrada</h2>
        <p style="color:Red"><i>Aviso: Asegura tener la localizacion en el movil Encendida.</i></p>
        <p>Usuario: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></p>
        <button onclick="registrarEntrada()">Registrar entrada</button>
        <button class="back-button" style="background-color:red" onclick="window.location.href='../interfaces/lista_contratos_campo.php'">Regresar</button>
        <p id="status"></p>
    </div>

    <script>
        function registrarEntrada() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const latitud = position.coords.latitude;
                        const longitud = position.coords.longitude;
                        const fechaHora = new Date().toISOString().slice(0, 19).replace('T', ' ');

                        fetch('../funciones/procesar_registro.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `latitud=${latitud}&longitud=${longitud}&fecha_hora=${fechaHora}`
                        })
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById("status").innerText = data;
                        })
                        .catch(error => {
                            document.getElementById("status").innerText = "Error al registrar entrada.";
                        });
                    },
                    function (error) {
                        alert("Error obteniendo ubicación: " + error.message);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                alert("Geolocalización no es compatible con este navegador.");
            }
        }
    </script>
</body>
</html>
