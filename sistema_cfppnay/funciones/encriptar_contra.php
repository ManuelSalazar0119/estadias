<?php
// Simple herramienta para encriptar y verificar contraseñas

$hash = '';
$verificado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $hash_input = $_POST['hash'] ?? '';

    if (!empty($password) && empty($hash_input)) {
        // Generar hash
        $hash = password_hash($password, PASSWORD_DEFAULT);
    } elseif (!empty($password) && !empty($hash_input)) {
        // Verificar hash
        $verificado = password_verify($password, $hash_input);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Encriptar y Verificar Contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9fbe7; color: #4e342e; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;}
        form { background: #fff; padding: 24px 32px; border-radius: 12px; box-shadow: 0 2px 12px #a1887f33; margin-bottom: 18px;}
        label { font-weight: 600; }
        input[type="password"], input[type="text"] { padding: 8px; border-radius: 6px; border: 1px solid #a1887f; margin-bottom: 12px; width: 100%; }
        button { background: #388e3c; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-weight: 600; cursor: pointer; }
        button:hover { background: #6d4c41; }
        .result { margin-top: 12px; padding: 10px; border-radius: 6px; }
        .ok { background: #e8f5e9; color: #388e3c; }
        .fail { background: #ffebee; color: #c62828; }
        .hash { font-family: monospace; font-size: 0.95em; background: #f5f5f5; padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>Encriptar contraseña</h2>
    <form method="POST">
        <label for="password">Contraseña:</label><br>
        <input type="password" name="password" id="password" required><br>
        <button type="submit">Generar hash</button>
    </form>
    <?php if ($hash): ?>
        <div class="result ok">
            <b>Hash generado:</b><br>
            <span class="hash"><?php echo htmlspecialchars($hash); ?></span>
        </div>
    <?php endif; ?>

    <h2>Verificar contraseña</h2>
    <form method="POST">
        <label for="password2">Contraseña:</label><br>
        <input type="password" name="password" id="password2" required><br>
        <label for="hash">Hash:</label><br>
        <input type="text" name="hash" id="hash" required><br>
        <button type="submit">Verificar</button>
    </form>
    <?php if ($verificado !== null): ?>
        <div class="result <?php echo $verificado ? 'ok' : 'fail'; ?>">
            <?php echo $verificado ? '¡La contraseña coincide con el hash!' : 'La contraseña NO coincide con el hash.'; ?>
        </div>
    <?php endif; ?>
</body>
</html>