<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesi¨®n y si es laboratorista
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Laboratorista' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
</head>
<body>


    <section class="contract-table-wrapper">
    <div class="table-container">
        <table class="contract-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Alias</th>
                    <th>Estado Contrato</th>
                    
                    <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                        <th>Cobrador Asignado</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para obtener todos los contratos, junto con la direcci¨®n de cobranza
                $sql = "
                    SELECT 
                        c.id_cliente, 
                        c.id_folio, 
                        c.nombre_cliente, 
                        c.alias_cliente, 
                        c.estado_contrato, 
                        f.folios, 
                        f.total, 
                        f.estado_liquidacion,
                        f.fecha_creacion
                    FROM 
                        clientecontrato c
                    JOIN 
                        folios f 
                    ON 
                        c.id_folio = f.id_folio
                    WHERE 
                        c.estado_contrato = 'No Liberado'
                    ORDER BY 
                        f.id_folio DESC";


                // Prepara la consulta
                /*
                                        f.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        AND f.fecha_creacion < DATE_SUB(NOW(), INTERVAL 2 DAY)
                */
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error en la preparaci¨®n de la consulta: " . $conn->error);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                // Mostrar los contratos
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td data-label="Cliente">
                            <a href="informacion_de_contrato_lab.php?id_cliente=<?php echo $row['id_cliente']; ?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>
                        <td data-label="Alias">
                            <?php echo htmlspecialchars($row['alias_cliente']); ?>
                        </td>
                        <td data-label="Estado Contrato">
                            <?php echo htmlspecialchars($row['estado_contrato']); ?>
                        </td>



                        <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                            <td data-label="Cobrador Asignado">
                                <?php echo htmlspecialchars($row['cobrador_nombre']); ?>
                            </td>
                        <?php endif; ?>

                    </tr>
                    <?php
                }

                // Cierra la consulta
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
    </section>
</body>
</html>
