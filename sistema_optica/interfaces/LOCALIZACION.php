<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener Localización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        input {
            width: 80%;
            padding: 10px;
            margin: 10px;
            font-size: 16px;
            text-align: center;
        }
        button {
            padding: 10px 20px;
            font-size: 18px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h2>Obtener Ubicación</h2>
    <button onclick="obtenerUbicacion()">Obtener Localización</button>
    <br><br>
    <label>Latitud:</label>
    <input type="text" id="latitud" readonly>
    <br>
    <label>Longitud:</label>
    <input type="text" id="longitud" readonly>

    <script>
        function obtenerUbicacion() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        document.getElementById("latitud").value = position.coords.latitude;
                        document.getElementById("longitud").value = position.coords.longitude;
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
